<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'Price Plot API is running.',
    'version' => '1.0',
    'endpoints' => [
        'fetch_product' => '/api/products/fetch_product.php (Requires POST with productUrl)'
    ]
]);
