<?php
require_once __DIR__ . '/BaseScraper.php';
require_once __DIR__ . '/AmazonScraper.php';
require_once __DIR__ . '/FlipkartScraper.php';
require_once __DIR__ . '/PoorvikaScraper.php';
require_once __DIR__ . '/RelianceDigitalScraper.php';
require_once __DIR__ . '/iPlanetScraper.php';
require_once __DIR__ . '/VijaySalesScraper.php';
require_once __DIR__ . '/CromaScraper.php';
require_once __DIR__ . '/CrossPlatformScraper.php';

$query = 'iPhone 15';
echo "=== COMPREHENSIVE SCRAPER TEST ===\n\n";

$scrapers = [
    'Poorvika' => new PoorvikaScraper(),
    'Reliance' => new RelianceDigitalScraper(),
    'iPlanet'  => new iPlanetScraper(),
    'VijaySales' => new VijaySalesScraper(),
    'Croma'    => new CromaScraper(),
];

foreach ($scrapers as $name => $s) {
    echo "Testing $name search for '$query'...\n";
    $res = $s->search($query);
    echo "Found " . count($res) . " results.\n";
    if (!empty($res)) {
        echo "First: {$res[0]['platform']} - ₹{$res[0]['price']}\n";
    }
    echo "-------------------\n";
}

echo "\nTesting CrossPlatformScraper.searchAll('$query')...\n";
$cp = new CrossPlatformScraper();
$all = $cp->searchAll($query);
echo "Final Aggregated Count: " . count($all) . "\n";
foreach ($all as $item) {
    echo "- {$item['platform']}: ₹{$item['price']}\n";
}
