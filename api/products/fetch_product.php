<?php
// api/products/fetch_product.php
// Scrapes the submitted product URL with the native platform scraper, then searches
// Flipkart/Snapdeal/Amazon for the same product to build a multi-platform comparison.
// Returns prices + computed AI score + recommendation to the Android app.

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../helpers/auth.php';
require_once '../../scrapers/BaseScraper.php';
require_once '../../scrapers/AmazonScraper.php';
require_once '../../scrapers/FlipkartScraper.php';
require_once '../../scrapers/SnapdealScraper.php';
require_once '../../scrapers/MyntraScraper.php';
require_once '../../scrapers/PriceAggregatorScraper.php';
require_once '../../scrapers/CrossPlatformScraper.php';

set_time_limit(120);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$user  = requireAuth();
$input = json_decode(file_get_contents('php://input'), true);

$productUrl   = trim($input['productUrl']   ?? '');
$forceRefresh = (bool)($input['forceRefresh'] ?? false);

if (empty($productUrl) || !filter_var($productUrl, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Valid product URL is required']);
    exit;
}

// ----- Platform detection -----
$supported = [
    'amazon'   => 'amazon',
    'amzn'     => 'amazon',
    'flipkart' => 'flipkart',
    'snapdeal' => 'snapdeal',
    'myntra'   => 'myntra',
];

$host     = strtolower(parse_url($productUrl, PHP_URL_HOST) ?? '');
$platform = null;
foreach ($supported as $fragment => $label) {
    if (strpos($host, $fragment) !== false) {
        $platform = $label;
        break;
    }
}

if ($platform === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Unsupported platform. Supported: Amazon, Flipkart, Snapdeal, Myntra',
    ]);
    exit;
}

$db      = Database::getConnection();
$urlHash = hash('sha256', $productUrl);

// ----- Cache check -----
if (!$forceRefresh) {
    $cacheStmt = $db->prepare(
        'SELECT scraped_data FROM price_cache WHERE url_hash = ? AND (expires_at IS NULL OR expires_at > NOW())'
    );
    $cacheStmt->execute([$urlHash]);
    $cached = $cacheStmt->fetch();
    if ($cached) {
        $db->prepare('UPDATE price_cache SET hit_count = hit_count + 1 WHERE url_hash = ?')
           ->execute([$urlHash]);
        $data              = json_decode($cached['scraped_data'], true);
        $data['fromCache'] = true;
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
}

// ----- Primary platform scrape -----
$startTime = microtime(true);

switch ($platform) {
    case 'amazon':   $scraper = new AmazonScraper();   break;
    case 'flipkart': $scraper = new FlipkartScraper(); break;
    case 'snapdeal': $scraper = new SnapdealScraper(); break;
    case 'myntra':   $scraper = new MyntraScraper();   break;
}
$primary = $scraper->scrape($productUrl);

$responseTimeMs = (int)round((microtime(true) - $startTime) * 1000);

// Log attempt
$logStmt = $db->prepare(
    'INSERT INTO scrape_requests (user_id, product_url, request_ip, success, response_time_ms, error_message)
     VALUES (?, ?, ?, ?, ?, ?)'
);

if (!$primary['success']) {
    $errMsg = $primary['error'] ?? 'Scraper returned no data';
    $logStmt->execute([$user['user_id'], $productUrl, $_SERVER['REMOTE_ADDR'] ?? null, 0, $responseTimeMs, $errMsg]);
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $errMsg]);
    exit;
}

$logStmt->execute([$user['user_id'], $productUrl, $_SERVER['REMOTE_ADDR'] ?? null, 1, $responseTimeMs, null]);

$productName  = $primary['productName']  ?? 'Unknown Product';
$productImage = $primary['productImage'] ?? null;

// Primary price entry
$prices = [[
    'platform'     => $primary['platform'],
    'price'        => (float)$primary['price'],
    'currency'     => $primary['currency']     ?? 'INR',
    'availability' => $primary['availability'] ?? 'In Stock',
    'link'         => $primary['link']         ?? $productUrl,
]];

// ----- Cross-platform search -----
// Primary: use price-comparison aggregators (MySmartPrice / 91mobiles)
// They return Amazon, Flipkart, Croma, TataCliq, Vijay Sales, etc. in one page.
// Fallback: direct per-platform search (CrossPlatformScraper) when aggregator yields < 2 results.
try {
    $aggScraper = new PriceAggregatorScraper();
    $extras     = $aggScraper->searchAll($productName, $platform);

    // If aggregator found fewer than 2 stores, supplement with direct scraper
    if (count($extras) < 2) {
        error_log('[fetch_product] aggregator got ' . count($extras) . ' results, trying CrossPlatformScraper');
        $crossScraper  = new CrossPlatformScraper();
        $crossExtras   = $crossScraper->searchAll($productName, $platform);
        // Merge (keep lowest price per platform)
        $extrasMap = [];
        foreach ($extras      as $e) { $extrasMap[strtolower($e['platform'])] = $e; }
        foreach ($crossExtras  as $e) {
            $k = strtolower($e['platform']);
            if (!isset($extrasMap[$k]) || $e['price'] < $extrasMap[$k]['price']) {
                $extrasMap[$k] = $e;
            }
        }
        $extras = array_values($extrasMap);
    }

    foreach ($extras as $ex) {
        if (!empty($ex['price']) && $ex['price'] >= 10) {
            $prices[] = [
                'platform'     => $ex['platform'],
                'price'        => (float)$ex['price'],
                'currency'     => $ex['currency']     ?? 'INR',
                'availability' => $ex['availability'] ?? 'In Stock',
                'link'         => $ex['link']         ?? '',
            ];
        }
    }
} catch (Throwable $e) {
    error_log('[fetch_product] cross-platform error: ' . $e->getMessage());
}

// ----- Deduplicate by platform (keep lowest price per platform) -----
$byPlatform = [];
foreach ($prices as $pe) {
    $key = strtolower($pe['platform']);
    if (!isset($byPlatform[$key]) || $pe['price'] < $byPlatform[$key]['price']) {
        $byPlatform[$key] = $pe;
    }
}
$prices = array_values($byPlatform);

// ----- Sort ascending -----
usort($prices, fn($a, $b) => $a['price'] <=> $b['price']);

$minPrice = $prices[0]['price'];
$maxPrice = $prices[count($prices) - 1]['price'];
$avgPrice = array_sum(array_column($prices, 'price')) / count($prices);
$savings  = round($maxPrice - $minPrice, 2);

$bestPrice = [
    'platform' => $prices[0]['platform'],
    'price'    => $minPrice,
    'savings'  => $savings,
];

// ----- Compute AI Score -----
$platformCount = count($prices);
$variance      = $maxPrice > 0 ? ($maxPrice - $minPrice) / $maxPrice : 0;

$aiScore = 65;
$aiScore += min($platformCount * 5, 25);   // +5 per platform up to +25
if ($variance < 0.05)       $aiScore += 5; // consistent pricing  
elseif ($variance > 0.20)   $aiScore -= 5; // big price gap
if ($platformCount >= 3)    $aiScore += 5; // well-researched

$aiScore = max(60, min(95, $aiScore));

if      ($aiScore >= 85) $aiLabel = 'Highly Recommended';
elseif  ($aiScore >= 75) $aiLabel = 'Good Buy';
else                     $aiLabel = 'Fair Deal';

$recommendation = sprintf(
    'Buy on %s — cheapest at ₹%s. Compared across %d platform%s.',
    $prices[0]['platform'],
    number_format($minPrice, 0, '.', ','),
    $platformCount,
    $platformCount > 1 ? 's' : ''
);

// ----- Upsert product record -----
$existStmt = $db->prepare('SELECT product_id FROM products WHERE url_hash = ?');
$existStmt->execute([$urlHash]);
$existing = $existStmt->fetch();

if ($existing) {
    $productId = (int)$existing['product_id'];
    $db->prepare(
        'UPDATE products SET product_name = ?, product_image_url = ?,
         last_scraped_at = NOW(), scrape_count = scrape_count + 1 WHERE product_id = ?'
    )->execute([$productName, $productImage, $productId]);
} else {
    $ins = $db->prepare(
        'INSERT INTO products (original_url, url_hash, product_name, product_image_url) VALUES (?, ?, ?, ?)'
    );
    $ins->execute([$productUrl, $urlHash, $productName, $productImage]);
    $productId = (int)$db->lastInsertId();
}

// ----- Insert price history -----
$priceStmt = $db->prepare(
    'INSERT INTO prices (product_id, platform, price, currency, availability, product_link)
     VALUES (?, ?, ?, ?, ?, ?)'
);
foreach ($prices as $pe) {
    $priceStmt->execute([
        $productId,
        $pe['platform'],
        $pe['price'],
        $pe['currency'],
        $pe['availability'],
        $pe['link'],
    ]);
}

// ----- Build response -----
$responseData = [
    'productId'      => $productId,
    'productName'    => $productName,
    'productImage'   => $productImage,
    'prices'         => $prices,
    'bestPrice'      => $bestPrice,
    'averagePrice'   => round($avgPrice, 2),
    'totalResults'   => count($prices),
    'aiScore'        => $aiScore,
    'aiLabel'        => $aiLabel,
    'recommendation' => $recommendation,
    'flashSavings'   => $savings,
    'lastUpdated'    => date('c'),
    'fromCache'      => false,
];

// ----- Cache (1 hour) -----
$db->prepare(
    'INSERT INTO price_cache (url_hash, scraped_data, expires_at)
     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
     ON DUPLICATE KEY UPDATE
       scraped_data = VALUES(scraped_data),
       expires_at   = VALUES(expires_at),
       cached_at    = NOW(),
       hit_count    = 0'
)->execute([$urlHash, json_encode($responseData)]);

echo json_encode(['success' => true, 'data' => $responseData]);