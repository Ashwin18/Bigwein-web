<?php
$root     = dirname(__DIR__);
$ctrlPath = $root.'/app/Http/Controllers/PropertController.php';
$ctrl     = file_get_contents($ctrlPath);
$orig     = $ctrl;

// Count occurrences
$count = substr_count($ctrl, 'function notifyOwnerApproval');
echo "<!DOCTYPE html><html><head><title>Fix Duplicate</title>
<style>body{font-family:sans-serif;padding:30px;max-width:700px;margin:0 auto;font-size:13px;}
.ok{background:#F0FDF4;border:1px solid #BBF7D0;padding:9px 14px;border-radius:7px;margin:4px 0;}
.err{background:#FEE2E2;border:1px solid #FCA5A5;padding:9px 14px;border-radius:7px;margin:4px 0;}
.warn{background:#FEF9C3;border:1px solid #FDE047;padding:12px;border-radius:8px;margin-top:16px;}
h2{color:#16A34A;}</style></head><body><h2>🔧 Remove Duplicate Method</h2>";

echo "<div class='".($count>1?'err':'ok')."'>notifyOwnerApproval() count: $count ".($count>1?'— DUPLICATE FOUND':'— OK')."</div>";

if ($count > 1) {
    // Find ALL occurrences and keep only the first one
    // Split on the method signature
    $pattern = '/(\s*\/\*\*.*?\*\/\s*)?private function notifyOwnerApproval\([^)]*\).*?(?=\s*(public|private|protected) function|\s*\}$)/s';
    
    preg_match_all($pattern, $ctrl, $matches, PREG_OFFSET_CAPTURE);
    
    echo "<div class='ok'>Found ".count($matches[0])." method blocks</div>";
    
    // Remove all occurrences after the first
    if (count($matches[0]) > 1) {
        // Keep the first, remove all subsequent
        for ($i = count($matches[0]) - 1; $i >= 1; $i--) {
            $start = $matches[0][$i][1];
            $len   = strlen($matches[0][$i][0]);
            $ctrl  = substr($ctrl, 0, $start) . substr($ctrl, $start + $len);
        }
        echo "<div class='ok'>✅ Removed ".(count($matches[0])-1)." duplicate(s)</div>";
    } else {
        // Simple approach: find the method body start positions
        $pos1 = strpos($ctrl, 'function notifyOwnerApproval');
        $pos2 = strpos($ctrl, 'function notifyOwnerApproval', $pos1 + 1);
        
        if ($pos2 !== false) {
            // Find the start of the second method (including any docblock before it)
            // Go back to find the start of the docblock or previous newline
            $methodStart = $pos2;
            // Go back to find opening /** or just a newline
            $lookback = substr($ctrl, max(0, $pos2-200), 200);
            if (($dpos = strrpos($lookback, '/**')) !== false) {
                $methodStart = $pos2 - (200 - $dpos);
            } elseif (($dpos = strrpos($lookback, "\n    private")) !== false || ($dpos = strrpos($lookback, "\n\n")) !== false) {
                $methodStart = $pos2 - (200 - $dpos);
            }
            
            // Find the end of the second method
            // Count braces from opening { to find matching }
            $braceStart = strpos($ctrl, '{', $pos2);
            $depth = 0;
            $methodEnd = $braceStart;
            for ($i = $braceStart; $i < strlen($ctrl); $i++) {
                if ($ctrl[$i] === '{') $depth++;
                elseif ($ctrl[$i] === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $methodEnd = $i + 1;
                        break;
                    }
                }
            }
            
            // Remove from methodStart to methodEnd
            $removed = substr($ctrl, $methodStart, $methodEnd - $methodStart);
            $ctrl    = substr($ctrl, 0, $methodStart) . substr($ctrl, $methodEnd);
            
            echo "<div class='ok'>✅ Removed second notifyOwnerApproval() (chars $methodStart-$methodEnd)</div>";
            echo "<div class='ok' style='font-size:11px;font-family:monospace;'>Removed: ".htmlspecialchars(substr($removed,0,100))."...</div>";
        }
    }
    
    // Verify
    $remaining = substr_count($ctrl, 'function notifyOwnerApproval');
    echo "<div class='".($remaining===1?'ok':'err')."'>After fix: $remaining occurrence(s) ".($remaining===1?'✅':'❌')."</div>";
    
    if ($remaining === 1) {
        file_put_contents($ctrlPath, $ctrl);
        echo "<div class='ok'>✅ PropertController.php saved (".strlen($ctrl)." bytes)</div>";
    }
}

// Clear cache
$cleared = 0;
foreach(new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root.'/storage/framework/views', FilesystemIterator::SKIP_DOTS)
) as $f){ if($f->isFile()&&@unlink($f->getRealPath()))$cleared++; }
foreach(glob($root.'/bootstrap/cache/*.php') as $f){@unlink($f);}
echo "<div class='ok'>✅ Cleared $cleared cached views + bootstrap cache</div>";

echo "<div class='warn'>⚠️ Delete <code>public/bw-fix-duplicate-method.php</code></div>";
echo "<p style='margin-top:16px;'><a href='/property?request_status=pending' style='color:#E5343A;font-weight:bold;font-size:16px;'>← Test /property?request_status=pending</a></p>";
echo "</body></html>";
