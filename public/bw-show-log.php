<?php
$logPath = dirname(__DIR__).'/storage/logs/laravel.log';
if (!file_exists($logPath)) { die("Log not found at: $logPath"); }

$size    = filesize($logPath);
$content = '';

// Read last 30KB only
if ($size > 30720) {
    $fh = fopen($logPath,'r');
    fseek($fh, -30720, SEEK_END);
    $content = fread($fh, 30720);
    fclose($fh);
} else {
    $content = file_get_contents($logPath);
}

// Extract last 10 ERROR blocks
preg_match_all('/\[\d{4}-\d{2}-\d{2}[^\]]+\] production\.ERROR.*?(?=\[\d{4}|\z)/s', $content, $m);
$errors = array_slice($m[0], -10);

echo '<!DOCTYPE html><html><head><title>Laravel Error Log</title>
<style>
body{font-family:monospace;padding:20px;background:#0F172A;color:#E2E8F0;font-size:12px;}
h2{font-family:sans-serif;color:#E5343A;margin-bottom:4px;}
.sub{color:#64748B;font-size:12px;font-family:sans-serif;margin-bottom:20px;}
.block{background:#1E293B;border:1px solid #334155;border-radius:8px;padding:14px;margin-bottom:14px;word-break:break-all;white-space:pre-wrap;}
.ts{color:#94A3B8;font-size:11px;}
.msg{color:#FCA5A5;font-weight:bold;margin:4px 0;}
.trace{color:#64748B;font-size:10px;margin-top:6px;}
.warn{background:#FEF9C3;color:#92400E;border-radius:8px;padding:12px;margin-top:16px;font-family:sans-serif;font-size:13px;}
</style></head><body>';

echo '<h2>BigWein — Laravel Error Log</h2>';
echo '<div class="sub">Last '.count($errors).' errors · Log size: '.number_format($size).' bytes</div>';

if (empty($errors)) {
    echo '<div class="block" style="color:#4ADE80">No errors found in last 30KB of log ✅</div>';
} else {
    foreach (array_reverse($errors) as $i => $block) {
        // Extract timestamp
        preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $block, $ts);
        // Extract error message
        preg_match('/production\.ERROR:\s*([^\n]+)/', $block, $msg);
        // Get first 3 stack trace lines
        preg_match_all('/#\d+[^\n]+/', $block, $trace);
        $traceLines = implode("\n", array_slice($trace[0],0,5));

        echo '<div class="block">';
        echo '<div class="ts">'.htmlspecialchars($ts[1]??'').'</div>';
        echo '<div class="msg">'.htmlspecialchars(substr($msg[1]??$block,0,300)).'</div>';
        if ($traceLines) echo '<div class="trace">'.htmlspecialchars($traceLines).'</div>';
        echo '</div>';
    }
}

echo '<div class="warn">⚠️ Delete <code>public/bw-show-log.php</code> immediately after viewing — never leave log viewer public.</div>';
echo '</body></html>';
