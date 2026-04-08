<?php
require_once __DIR__ . '/../config/cors.php';
echo json_encode([
    'headers' => getallheaders(),
    'SERVER'  => $_SERVER,
    'X-Auth-Token' => $_SERVER['HTTP_X_AUTH_TOKEN'] ?? 'NOT FOUND'
]);
