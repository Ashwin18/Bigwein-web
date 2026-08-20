<?php
$root  = dirname(__DIR__);

// ── Read fresh log ────────────────────────────────────────────────────────
$logPath = $root.'/storage/logs/laravel.log';
$content = '';
if (file_exists($logPath)) {
    $size = filesize($logPath);
    $fh   = fopen($logPath,'r');
    fseek($fh, -40960, SEEK_END);
    $content = fread($fh, 40960);
    fclose($fh);
}
preg_match_all('/\[\d{4}-\d{2}-\d{2}[^\]]+\] production\.ERROR.*?(?=\[\d{4}|\z)/s', $content, $m);
$errors = array_slice($m[0], -5);
$lastError = end($errors) ?? '';

// Extract message
preg_match('/production\.ERROR:\s*([^\n{]+)/', $lastError, $errMsg);
$msg = trim($errMsg[1] ?? 'Unknown error');

// ── Diagnose + Fix ────────────────────────────────────────────────────────
$fixes = [];
$fixes[] = "Latest error: $msg";

$env = [];
foreach (file($root.'/.env') as $line) {
    $line = trim($line);
    if (!$line || $line[0]==='#' || !strpos($line,'=')) continue;
    list($k,$v) = explode('=',$line,2);
    $env[trim($k)] = trim($v," \t\n\r\0\"'");
}
try {
    $pdo = new PDO("mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4",
        $env['DB_USERNAME'],$env['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e) { die("DB: ".$e->getMessage()); }

// Check @includes in property/index.blade.php
$propView = $root.'/resources/views/property/index.blade.php';
$pv       = file_get_contents($propView);

preg_match_all('/@include\([\'"]([^\'"]+)[\'"]/', $pv, $incs);
foreach ($incs[1] as $inc) {
    $path = $root.'/resources/views/'.str_replace('.',DIRECTORY_SEPARATOR,$inc).'.blade.php';
    if (!file_exists($path)) {
        $fixes[] = "❌ Missing @include: $inc → will create stub";
        // Create empty stub
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir,0755,true);
        file_put_contents($path, '{{-- stub --}}');
        $fixes[] = "✅ Created stub: $inc";
    } else {
        $fixes[] = "✅ @include exists: $inc";
    }
}

// Check @extends
preg_match('/@extends\([\'"]([^\'"]+)[\'"]/', $pv, $ext);
$extPath = $root.'/resources/views/'.str_replace('.',DIRECTORY_SEPARATOR,$ext[1]??'').'.blade.php';
$fixes[] = file_exists($extPath) ? "✅ @extends: {$ext[1]}" : "❌ @extends MISSING: {$ext[1]}";

// Check variables passed from PropertController::index()
$ctrlPath = $root.'/app/Http/Controllers/PropertController.php';
$ctrl     = file_get_contents($ctrlPath);
preg_match('/function index\(\)[^{]*\{(.*?)(?=public function)/s', $ctrl, $im);
$indexBody = $im[1] ?? '';
preg_match('/compact\(([^)]+)\)/', $indexBody, $compactM);
$fixes[] = "compact() vars: ".($compactM[1]??'not found');

// Find all vars used in property view
preg_match_all('/@foreach\s*\(\s*\$([a-z_]+)/i', $pv, $feVars);
preg_match_all('/\$([a-z_]+)->/', $pv, $dotVars);
$viewVars = array_unique(array_merge($feVars[1]??[], $dotVars[1]??[]));
$fixes[] = "View uses vars: ".implode(', ', array_slice($viewVars,0,15));

// Check if property model has customer relation
$propModel = $root.'/app/Models/Propertys.php';
if (!file_exists($propModel)) $propModel = $root.'/app/Models/Property.php';
if (file_exists($propModel)) {
    $pm = file_get_contents($propModel);
    $hasCust = strpos($pm,'function customer')!==false;
    $fixes[] = $hasCust ? "✅ Propertys model has customer() relation" : "❌ customer() relation MISSING from model";

    if (!$hasCust) {
        // Add relation
        $pm = str_replace(
            'public function category()',
            'public function customer() { return $this->belongsTo(\App\Models\Customer::class, "added_by"); }'."\n\n    public function category()",
            $pm
        );
        file_put_contents($propModel, $pm);
        $fixes[] = "✅ Added customer() relation to Propertys model";
    }
}

// Add null-safe defaults to property/index.blade.php @php block if missing
$pvOrig = $pv;
if (strpos($pv,'Null-safe') === false) {
    $defaults = '@php
/* Null-safe defaults for property listing */
$propertiesData = $propertiesData ?? collect();
$categories     = $categories     ?? collect();
$request_status = request("request_status","");
@endphp
';
    if (strpos($pv,'@extends') !== false) {
        $pv = preg_replace('/(@extends\([^)]+\))/', $defaults."\n".'$1', $pv, 1);
        file_put_contents($propView, $pv);
        $fixes[] = "✅ Added null-safe defaults to property/index.blade.php";
    }
}

// Clear cache
$cleared = 0;
foreach(new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root.'/storage/framework/views', FilesystemIterator::SKIP_DOTS)
) as $f){ if($f->isFile()&&@unlink($f->getRealPath()))$cleared++; }
foreach(glob($root.'/bootstrap/cache/*.php') as $f){@unlink($f);}
$fixes[] = "✅ Cleared $cleared cached views + bootstrap cache";
?>
<!DOCTYPE html><html><head><title>Property List Fix</title>
<style>body{font-family:sans-serif;padding:30px;max-width:800px;margin:0 auto;font-size:13px;}
.ok{background:#F0FDF4;border:1px solid #BBF7D0;padding:8px 14px;border-radius:7px;margin:3px 0;}
.err{background:#FEE2E2;border:1px solid #FCA5A5;padding:8px 14px;border-radius:7px;margin:3px 0;font-weight:bold;}
.info{background:#F8FAFC;border:1px solid #E2E8F0;padding:8px 14px;border-radius:7px;margin:3px 0;font-family:monospace;font-size:11px;}
.warn{background:#FEF9C3;border:1px solid #FDE047;padding:12px;border-radius:8px;margin-top:16px;}
h2{color:#16A34A;}</style></head><body>
<h2>🔧 Property List Fix</h2>
<?php foreach($fixes as $f): ?>
<div class="<?= str_starts_with($f,'✅')?'ok':(str_starts_with($f,'❌')?'err':'info') ?>"><?= htmlspecialchars($f) ?></div>
<?php endforeach; ?>

<h3 style="margin-top:20px;color:#374151;">Latest Error Details:</h3>
<?php foreach(array_reverse($errors) as $err): ?>
<div class="info"><?= htmlspecialchars(substr($err,0,500)) ?></div>
<?php endforeach; ?>

<div class="warn">⚠️ Delete <code>public/bw-fix-property-list.php</code></div>
<p style="margin-top:16px;">
  <a href="/property?request_status=pending" style="color:#E5343A;font-weight:bold;">← Test /property?request_status=pending</a>
</p>
</body></html>
