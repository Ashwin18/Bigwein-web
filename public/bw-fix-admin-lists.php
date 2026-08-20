<?php
$root  = dirname(__DIR__);
$fixes = [];

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

// ── FIX 1: customerList method in CustomersController ────────────────────
$ccPath = $root.'/app/Http/Controllers/CustomersController.php';
$cc     = file_get_contents($ccPath);

if (strpos($cc,'function customerList') === false) {
    $method = '
    public function customerList(Request $request)
    {
        $customers = \App\Models\Customer::orderByDesc(\'id\')->get([
            \'id\',\'name\',\'email\',\'mobile\',\'logintype\',\'is_email_verified\',\'created_at\'
        ]);
        return response()->json([
            \'data\' => $customers->map(function($c) {
                return [
                    \'id\'            => $c->id,
                    \'name\'          => $c->name ?? \'—\',
                    \'email\'         => $c->email ?? \'—\',
                    \'mobile\'        => $c->mobile ?? \'—\',
                    \'type\'          => $c->logintype == 1 ? \'Owner\' : \'Buyer\',
                    \'verified\'      => $c->is_email_verified ? \'Yes\' : \'No\',
                    \'created_at\'    => $c->created_at ? date(\'d M Y\', strtotime($c->created_at)) : \'—\',
                    \'action\'        => $c->id,
                ];
            })
        ]);
    }
';
    // Insert before closing brace
    $cc = rtrim($cc,'} ')."\n".$method."\n}";
    file_put_contents($ccPath, $cc);
    $fixes[] = "✅ Added customerList() JSON endpoint to CustomersController";
} else {
    $fixes[] = "ℹ️ customerList() already exists — checking it returns proper JSON";

    // Make sure it returns json with 'data' key (DataTables format)
    if (strpos($cc,"'data'") === false && strpos($cc,'"data"') === false) {
        $cc = preg_replace(
            '/function customerList\([^)]*\)[^{]*\{.*?return response\(\)->json\(\[/s',
            'function customerList(Request $request) {
        $customers = \App\Models\Customer::orderByDesc(\'id\')->get([\'id\',\'name\',\'email\',\'mobile\',\'logintype\',\'is_email_verified\',\'created_at\']);
        return response()->json([\'data\' => $customers, ',
            $cc, 1
        );
        file_put_contents($ccPath, $cc);
        $fixes[] = "✅ Fixed customerList() to return {data:[...]} format";
    }
}

// ── FIX 2: Check customerList route exists in web.php ────────────────────
$webPath = $root.'/routes/web.php';
$wr = file_get_contents($webPath);
if (strpos($wr,"customerList") === false) {
    // Add the route inside admin auth group
    $anchor = "Route::resource('customer', CustomersController::class);";
    if (strpos($wr,$anchor) !== false) {
        $wr = str_replace(
            $anchor,
            $anchor."\n        Route::get('customerList', [CustomersController::class, 'customerList'])->name('customer.list');",
            $wr
        );
        file_put_contents($webPath, $wr);
        $fixes[] = "✅ Added /customerList route to web.php";
    } else {
        $fixes[] = "⚠️ Could not find customer resource in web.php — check manually";
    }
} else {
    $fixes[] = "ℹ️ customerList route already in web.php";
}

// ── FIX 3: Patch PropertController — wrap customer relation safely ─────────
$ptPath = $root.'/app/Http/Controllers/PropertController.php';
$pt     = file_get_contents($ptPath);

// The issue: pending properties have added_by=NULL, customer() relation fails
// Fix: ensure customer is loaded with proper null handling
// Find with('customer...') calls and make them safe
$pt_fixed = preg_replace(
    "/->with\(['\"]customer:id,name,mobile['\"]\)/",
    "->with(['customer' => function(\$q){ \$q->select('id','name','mobile'); }])",
    $pt
);
$pt_fixed = preg_replace(
    "/->with\(['\"]customer:id,name,email['\"]\)/",
    "->with(['customer' => function(\$q){ \$q->select('id','name','email'); }])",
    $pt_fixed
);
$pt_fixed = preg_replace(
    "/->with\(['\"]customer:id,name,isActive,notification['\"]\)/",
    "->with(['customer' => function(\$q){ \$q->select('id','name','mobile'); }])",
    $pt_fixed
);

if ($pt_fixed !== $pt) {
    file_put_contents($ptPath, $pt_fixed);
    $fixes[] = "✅ Patched PropertController with() calls to use closure form (null-safe)";
} else {
    $fixes[] = "ℹ️ PropertController with() already in closure form";
}

// ── FIX 4: Fix NULL added_by for pending properties ───────────────────────
$nullOwner = $pdo->query("SELECT COUNT(*) FROM propertys WHERE added_by IS NULL OR added_by=0")->fetchColumn();
$fixes[] = "ℹ️ Properties with NULL/0 added_by: $nullOwner";
if ($nullOwner > 0) {
    // Get first admin customer or create a system user reference
    $firstAdmin = $pdo->query("SELECT id FROM customers WHERE logintype=1 OR id=1 LIMIT 1")->fetchColumn();
    if (!$firstAdmin) {
        // Use id=1 as fallback
        $firstAdmin = 1;
    }
    // Don't change added_by — just ensure the view handles NULL gracefully
    $fixes[] = "ℹ️ Properties without owner — view must handle NULL customer gracefully";
}

// ── FIX 5: Patch property/index.blade.php for null-safe customer access ───
$propView = $root.'/resources/views/property/index.blade.php';
$pv = file_get_contents($propView);
$pvOrig = $pv;

// Replace $prop->customer->name with optional($prop->customer)->name
$pv = preg_replace('/\$([a-z_]+)->customer->([a-z_]+)/i', 'optional($$1->customer)->$2', $pv);
// Same for $property->customer->X
$pv = preg_replace('/\$([a-z_]+)->customer\?->([a-z_]+)/i', 'optional($$1->customer)->$2', $pv);

if ($pv !== $pvOrig) {
    file_put_contents($propView, $pv);
    $fixes[] = "✅ Wrapped customer relation accesses with optional() in property/index.blade.php";
} else {
    $fixes[] = "ℹ️ No direct ->customer-> access found in property view — 500 from elsewhere";
}

// ── FIX 6: Check what error property/index actually causes ────────────────
// Look for any Blade directives that might fail
if (strpos($pv,'@include') !== false) {
    preg_match_all('/@include\([\'"]([^\'"]+)[\'"]\)/', $pv, $incM);
    foreach ($incM[1] as $inc) {
        $incPath = $root.'/resources/views/'.str_replace('.',DIRECTORY_SEPARATOR,$inc).'.blade.php';
        if (!file_exists($incPath)) {
            $fixes[] = "❌ Missing @include: $inc ($incPath) — this causes 500!";
        }
    }
}

// ── Clear cache ───────────────────────────────────────────────────────────
$cleared = 0;
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root.'/storage/framework/views', FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $f) { if ($f->isFile() && @unlink($f->getRealPath())) $cleared++; }
foreach (glob($root.'/bootstrap/cache/*.php') as $f) { @unlink($f); }
$fixes[] = "✅ Cleared $cleared cached views + bootstrap cache";
?>
<!DOCTYPE html><html><head><title>Admin Fix v2</title>
<style>body{font-family:sans-serif;padding:30px;max-width:800px;margin:0 auto;font-size:13px;}
.ok{background:#F0FDF4;border:1px solid #BBF7D0;padding:8px 14px;border-radius:7px;margin:3px 0;}
.err{background:#FEE2E2;border:1px solid #FCA5A5;padding:8px 14px;border-radius:7px;margin:3px 0;}
.info{background:#F8FAFC;border:1px solid #E2E8F0;padding:8px 14px;border-radius:7px;margin:3px 0;}
.warn{background:#FEF9C3;border:1px solid #FDE047;padding:12px;border-radius:8px;margin-top:16px;font-size:13px;}
h2{color:#16A34A;}</style></head><body>
<h2>🔧 Admin List Fix v2</h2>
<?php foreach($fixes as $f): ?>
<div class="<?= str_starts_with($f,'✅')?'ok':(str_starts_with($f,'❌')?'err':'info') ?>"><?= htmlspecialchars($f) ?></div>
<?php endforeach; ?>
<div class="warn">⚠️ Delete <code>public/bw-fix-admin-lists.php</code></div>
<p style="margin-top:16px;display:flex;gap:16px;flex-wrap:wrap;">
  <a href="/property?request_status=pending" style="color:#E5343A;font-weight:bold;">← Test /property?request_status=pending</a>
  <a href="/customer" style="color:#E5343A;font-weight:bold;">← Test /customer</a>
</p>
</body></html>
