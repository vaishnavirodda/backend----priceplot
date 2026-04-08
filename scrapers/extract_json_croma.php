<?php
$html = file_get_contents('C:/Users/HP/.gemini/antigravity/brain/969997ab-ffaa-47bc-b7fb-338631636d0f/debug_search_Croma.html');
if (preg_match('/window\.__INITIAL_DATA__\s*=\s*({.*?});/s', $html, $matches)) {
    $json = $matches[1];
    file_put_contents('C:/Users/HP/.gemini/antigravity/brain/969997ab-ffaa-47bc-b7fb-338631636d0f/croma_data.json', $json);
    echo "Extracted JSON to croma_data.json\n";
} else {
    echo "INITIAL_DATA not found.\n";
}
