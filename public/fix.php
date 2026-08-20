<?php
// Simple path fixer - no Laravel dependencies needed
// Upload to: public_html/bigweinadmin2/public/fix.php

$base = dirname(__DIR__); // points to bigweinadmin2/
$old  = '/public_html/bigweinadmin/';
$new  = '/public_html/bigweinadmin2/';
$fixed = 0;

$files = [
    $base . '/vendor/composer/autoload_static.php',
    $base . '/vendor/composer/autoload_classmap.php',
    $base . '/vendor/composer/autoload_files.php',
    $base . '/vendor/composer/autoload_psr4.php',
    $base . '/vendor/composer/autoload_namespaces.php',
    $base . '/vendor/composer/autoload_real.php',
    $base . '/vendor/autoload.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) { echo "NOT FOUND: " . basename($file) . "<br>"; continue; }
    $content = file_get_contents($file);
    $count   = substr_count($content, $old);
    if ($count > 0) {
        file_put_contents($file, str_replace($old, $new, $content));
        $fixed += $count;
        echo "FIXED " . $count . " paths in: " . basename($file) . "<br>";
    } else {
        echo "OK (no old paths): " . basename($file) . "<br>";
    }
}

foreach (glob($base . '/bootstrap/cache/*.php') as $f) { unlink($f); echo "DELETED: bootstrap/cache/" . basename($f) . "<br>"; }
foreach (glob($base . '/bootstrap/cache/*.tmp') as $f) { unlink($f); }
foreach (glob($base . '/storage/framework/views/*.php') as $f) { unlink($f); }
echo "CLEARED: compiled views<br>";
echo "<br><b>Done! Total replacements: $fixed — DELETE this file now!</b>";
