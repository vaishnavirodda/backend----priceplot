<?php
$url = 'http://localhost:8080/api/products/fetch_product.php';
$data = [
    'productUrl' => 'https://www.amazon.in/Apple-iPhone-15-128-GB/dp/B0CHX28TNC',
    'forceRefresh' => true
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n" .
                     "X-Auth-Token: 82194ff7f41f021e90581fdb702330a6\r\n",
        'content' => json_encode($data),
        'ignore_errors' => true
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Response Headers:\n";
print_r($http_response_header);
echo "\nResponse Body:\n";
echo $result;
