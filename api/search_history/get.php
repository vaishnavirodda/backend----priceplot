<?php
// api/search_history/get.php
// Returns the authenticated user's recent searches with full price list.
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../helpers/auth.php';

$user  = requireAuth();
$db    = Database::getConnection();
$limit = min((int)($_GET['limit'] ?? 50), 100);

// Fetch distinct products searched, most recent first
$stmt = $db->prepare(
    'SELECT sh.history_id, sh.searched_at,
            p.product_id, p.product_name, p.product_image_url, p.original_url
     FROM search_history sh
     JOIN products p ON p.product_id = sh.product_id
     WHERE sh.user_id = ?
     ORDER BY sh.searched_at DESC
     LIMIT ?'
);
$stmt->execute([$user['user_id'], $limit]);
$rows = $stmt->fetchAll();

$results = [];
foreach ($rows as $row) {
    // Load all prices for this product (latest per platform)
    $priceStmt = $db->prepare(
        'SELECT platform, MIN(price) AS price, currency, availability, product_link
         FROM prices
         WHERE product_id = ?
         GROUP BY platform
         ORDER BY price ASC'
    );
    $priceStmt->execute([$row['product_id']]);
    $prices = $priceStmt->fetchAll();

    $results[] = [
        'history_id'        => (int)$row['history_id'],
        'searched_at'       => $row['searched_at'],
        'product_id'        => (int)$row['product_id'],
        'product_name'      => $row['product_name'],
        'product_image_url' => $row['product_image_url'],
        'original_url'      => $row['original_url'],
        'prices'            => $prices,
        'best_price'        => !empty($prices) ? (float)$prices[0]['price'] : null,
        'best_platform'     => !empty($prices) ? $prices[0]['platform']    : null,
    ];
}

echo json_encode(['success' => true, 'data' => $results]);
