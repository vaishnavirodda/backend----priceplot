<?php
$html = file_get_contents('C:/Users/HP/.gemini/antigravity/brain/969997ab-ffaa-47bc-b7fb-338631636d0f/debug_search_Croma.html');
$pos = stripos($html, 'iPhone 15');
if ($pos !== false) {
    echo "Found 'iPhone 15' at position $pos\n";
    echo "Context: " . substr($html, $pos - 100, 200) . "\n";
} else {
    echo "'iPhone 15' NOT found in HTML.\n";
}

$pos = stripos($html, 'INITIAL_DATA');
if ($pos !== false) {
    echo "Found 'INITIAL_DATA' at position $pos\n";
    echo "Context: " . substr($html, $pos, 200) . "\n";
}
