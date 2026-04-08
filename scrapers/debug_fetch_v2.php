<?php
// Mock server variables
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_X_AUTH_TOKEN'] = '82194ff7f41f021e90581fdb702330a6';

// Bypass requireAuth by defining it before including
function requireAuth() {
    return ['user_id' => 1];
}

// Global variable to trick fetch_product.php if we can
$MOCK_INPUT = [
    'productUrl' => 'https://www.amazon.in/Apple-iPhone-15-128-GB/dp/B0CHX28TNC',
    'forceRefresh' => true
];

// Since we can't easily mock php://input without a stream wrapper, 
// let's just modify the script to allow a global override for testing.

$content = file_get_contents('C:/xampp/htdocs/price_plot/api/products/fetch_product.php');
// Replace php://input with a variable if it exists
$content = str_replace("file_get_contents('php://input')", "(\$GLOBALS['MOCK_INPUT_JSON'] ?? file_get_contents('php://input'))", $content);
$GLOBALS['MOCK_INPUT_JSON'] = json_encode($MOCK_INPUT);

eval('?>' . $content);
