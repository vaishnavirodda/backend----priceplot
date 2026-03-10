<?php
// api/flash_deals/get.php
require_once '../../config/cors.php';
require_once '../../config/database.php';

$db   = Database::getConnection();
$stmt = $db->prepare('SELECT * FROM flash_deals WHERE is_active = 1 ORDER BY deal_id ASC');
$stmt->execute();
$deals = $stmt->fetchAll();

// Cast numeric fields
foreach ($deals as &$d) {
    $d['deal_id']        = (int)$d['deal_id'];
    $d['price']          = (float)$d['price'];
    $d['original_price'] = (float)$d['original_price'];
    $d['discount_pct']   = (int)$d['discount_pct'];
    $d['is_active']      = (bool)$d['is_active'];
}

echo json_encode(['success' => true, 'data' => $deals]);
