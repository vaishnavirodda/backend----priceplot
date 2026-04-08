<?php
$jsonStr = file_get_contents('C:/Users/HP/.gemini/antigravity/brain/969997ab-ffaa-47bc-b7fb-338631636d0f/croma_data.json');

// Fix JS-isms
$jsonStr = str_replace([':undefined', ':NaN', ':Infinity'], [':null', ':null', ':null'], $jsonStr);
// Also handle case where it's at the end of an array or object
$jsonStr = preg_replace('/([,\[{])\s*undefined\s*([,\]}])/s', '$1null$2', $jsonStr);
$jsonStr = preg_replace('/([,\[{])\s*NaN\s*([,\]}])/s', '$1null$2', $jsonStr);

$data = json_decode($jsonStr, true);

if (!$data) {
    echo "Failed to decode JSON. Error: " . json_last_error_msg() . "\n";
    
    // Find where it failed
    for ($i = 0; $i < 5; $i++) {
        $part = substr($jsonStr, 0, (int)(strlen($jsonStr) * ( ($i+1) / 5 )));
        if (!json_decode($part . '}', true)) {
           // echo "Failed in section " . ($i+1) . "\n";
        }
    }
    
    exit;
}

echo "JSON Decoded successfully.\n";
function findKey($data, $searchKey, &$found) {
    if (!is_array($data)) return;
    foreach ($data as $key => $value) {
        if ($key === $searchKey) {
            $found[] = $value;
        }
        findKey($value, $searchKey, $found);
    }
}
$results = [];
findKey($data, 'products', $results);
echo "Found " . count($results) . " 'products' arrays.\n";
foreach ($results as $arr) {
    if (is_array($arr)) {
        echo "Array size: " . count($arr) . "\n";
        foreach (array_slice($arr, 0, 5) as $p) {
             echo "- " . ($p['name'] ?? 'N/A') . ": " . ($p['price']['formattedValue'] ?? 'N/A') . "\n";
        }
    }
}
