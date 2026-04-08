<?php
// Mock server variables usually set by Apache
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_X_AUTH_TOKEN'] = '82194ff7f41f021e90581fdb702330a6';

// Mock php://input
function mock_input($data) {
    $tmp = tempnam(sys_get_temp_dir(), 'mock_input_');
    file_put_contents($tmp, json_encode($data));
    return $tmp;
}

$inputFile = mock_input([
    'productUrl' => 'https://www.amazon.in/Apple-iPhone-15-128-GB/dp/B0CHX28TNC',
    'forceRefresh' => true
]);

// We can't easily mock php://input for the script being included, 
// so we'll use a wrapper that defines a constant or similar.
// Actually, let's just modify fetch_product.php slightly to check for a test constant.

include 'C:/xampp/htdocs/price_plot/api/products/fetch_product.php';
unlink($inputFile);
