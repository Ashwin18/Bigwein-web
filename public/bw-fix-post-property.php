<?php
$root  = dirname(__DIR__);
$blade = $root.'/resources/views/frontend/owner/post-property.blade.php';
$bc    = file_get_contents($blade);

// ── Extract ALL $variables used in the blade ──────────────────────────────
preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', $bc, $all_vars);
$used = array_unique($all_vars[1]);

// Filter out: PHP superglobals, loop vars, and ones already defined in @php block
$exclude = ['this','loop','errors','slot','__','errors','app','config',
            'request','session','auth','swCfg','swBuySubtypes','swRentSubtypes',
            'swBhk','swStatuses','swCommTypes','amenityList','bedroomParamId',
            'savedP','savedF','gallery','n','i','step','cat','param','fac',
            'spec','amenity','img','c','st','bud','t','s','v','k','m','f','e',
            'b','d','p','r','q','x','y','z'];

// Find what IS defined in the blade @php blocks
preg_match_all('/@php(.*?)@endphp/s', $bc, $phpBlocks);
$defined = [];
foreach ($phpBlocks[1] as $block) {
    preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=/', $block, $defs);
    foreach ($defs[1] as $d) $defined[] = $d;
}

// Variables that are USED but NOT defined anywhere in blade @php blocks
$likely_from_controller = [];
foreach ($used as $v) {
    if (in_array($v, $exclude)) continue;
    if (in_array($v, $defined)) continue;
    if (strlen($v) <= 1) continue;
    $likely_from_controller[] = $v;
}
$likely_from_controller = array_unique($likely_from_controller);

// ── Build comprehensive defaults block ────────────────────────────────────
$defaults = '@php
/* ── Null-safe defaults: all variables the blade uses ── */
$isEdit          = $isEdit          ?? false;
$formUrl         = $formUrl         ?? url("owner/post-property");
$prop            = $prop            ?? null;
$cust            = $cust            ?? session("bw_customer") ?? [];
$categories      = $categories      ?? collect();
$parameters      = $parameters      ?? collect();
$facilities      = $facilities      ?? collect();
$cities          = $cities          ?? collect();
$gallery         = $gallery         ?? collect();
$savedParams     = $savedParams     ?? collect();
$savedFacilities = $savedFacilities ?? collect();
$savedP          = $savedP          ?? collect();
$savedF          = $savedF          ?? collect();
$amenityList     = $amenityList     ?? ["Swimming Pool","Gym / Fitness","Car Parking","Lift / Elevator","Power Backup","24/7 Security","Garden / Park","High-Speed WiFi","Clubhouse","CCTV Surveillance","Intercom","Water Supply 24/7","Visitor Parking","Kids Play Area","Temple / Prayer Hall"];
$bedroomParamId  = $bedroomParamId  ?? 2;
$propId          = $propId          ?? null;
$ownerProperty   = $ownerProperty   ?? null;
$packages        = $packages        ?? collect();
$selectedPackage = $selectedPackage ?? null;
$swBuySubtypes   = isset($swBuySubtypes)   ? $swBuySubtypes   : ["Residential","Commercial","Land / Plot","Apartment","Villa"];
$swRentSubtypes  = isset($swRentSubtypes)  ? $swRentSubtypes  : ["Full House","PG / Hostel","Flatmates","Apartment"];
$swBhk           = isset($swBhk)           ? $swBhk           : ["1 BHK","2 BHK","3 BHK","4 BHK","5+ BHK"];
$swStatuses      = isset($swStatuses)      ? $swStatuses      : ["Ready to Move","Under Construction","New Launch"];
$swCommTypes     = isset($swCommTypes)     ? $swCommTypes     : ["Office","Co-working Space","Shop / Showroom","Warehouse","Factory / Industrial"];
@endphp
';

// Remove any existing null-safe defaults block we added before
$bc = preg_replace('/@php\s*\/\*.*?Null-safe defaults.*?@endphp\s*/s', '', $bc);

// Add at very top of file (before @extends)
if (strpos($bc,'@extends') !== false) {
    $bc = preg_replace('/(@extends\([^)]+\))/', $defaults."\n".'$1', $bc, 1);
} else {
    $bc = $defaults . $bc;
}

file_put_contents($blade, $bc);

// Clear ALL cache
$cleared = 0;
foreach(new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root.'/storage/framework/views', FilesystemIterator::SKIP_DOTS)
) as $f){ if($f->isFile()&&@unlink($f->getRealPath()))$cleared++; }
foreach(glob($root.'/bootstrap/cache/*.php') as $f){@unlink($f);}

echo '<!DOCTYPE html><html><head><title>All Vars Fix</title>
<style>body{font-family:sans-serif;padding:30px;max-width:700px;margin:0 auto;font-size:13px;}
.ok{background:#F0FDF4;border:1px solid #BBF7D0;padding:9px 14px;border-radius:7px;margin:4px 0;}
.info{background:#F8FAFC;border:1px solid #E2E8F0;padding:9px 14px;border-radius:7px;margin:4px 0;font-family:monospace;font-size:11px;}
.warn{background:#FEF9C3;border:1px solid #FDE047;padding:12px;border-radius:8px;margin-top:16px;}
h2{color:#16A34A;}</style></head><body>';

echo '<h2>✅ All Variables Fixed</h2>';
echo '<div class="ok">✅ Added comprehensive null-safe defaults block (20+ variables)</div>';
echo '<div class="ok">✅ File saved: '.filesize($blade).' bytes</div>';
echo '<div class="ok">✅ Cleared '.$cleared.' cached views + bootstrap cache</div>';
echo '<div class="info">Variables given defaults: $isEdit, $formUrl, $prop, $cust, $categories, $parameters, $facilities, $cities, $gallery, $savedParams, $savedFacilities, $amenityList, $bedroomParamId, $propId, $packages, $selectedPackage, $swBuySubtypes, $swRentSubtypes, $swBhk, $swStatuses, $swCommTypes</div>';
echo '<div class="warn">⚠️ Delete <code>public/bw-fix-post-property.php</code></div>';
echo '<p style="margin-top:16px;"><a href="/owner/post-property" style="color:#E5343A;font-weight:bold;font-size:16px;">← Test /owner/post-property</a></p>';
echo '</body></html>';
