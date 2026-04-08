<?php
// scrapers/test_scrapers_v5.php
require_once __DIR__ . '/CrossPlatformScraper.php';
require_once __DIR__ . '/PriceAggregatorScraper.php';
require_once __DIR__ . '/VijaySalesScraper.php';
require_once __DIR__ . '/PoorvikaScraper.php';
require_once __DIR__ . '/iPlanetScraper.php';
require_once __DIR__ . '/CromaScraper.php';
require_once __DIR__ . '/RelianceDigitalScraper.php';

$query = "iphone 15";
echo "Testing CrossPlatformScraper for '$query'...\n";

$cross = new CrossPlatformScraper();
$results = $cross->searchAll($query, 'amazon');

echo "Final results: " . count($results) . "\n";
foreach ($results as $r) {
    echo "- [" . $r['platform'] . "] " . $r['price'] . " INR\n";
}

if (count($results) == 0) {
    echo "\nTesting Direct Scrapers for debugging...\n";
    // require_once __DIR__ . '/VijaySalesScraper.php'; // Already required at the top
    $vsScraper = new VijaySalesScraper();
    $vsResults = $vsScraper->search($query);
    echo "VijaySales found " . count($vsResults) . " results.\n";

    $poorvikaScraper = new PoorvikaScraper();
    $poorvikaResults = $poorvikaScraper->search($query);
    echo "Poorvika found " . count($poorvikaResults) . " results.\n";

    $iPlanetScraper = new iPlanetScraper();
    $iPlanetResults = $iPlanetScraper->search($query);
    echo "iPlanet found " . count($iPlanetResults) . " results.\n";

    $cromaScraper = new CromaScraper();
    $cromaResults = $cromaScraper->search($query);
    echo "Croma found " . count($cromaResults) . " results.\n";

    $relianceScraper = new RelianceDigitalScraper();
    $relianceResults = $relianceScraper->search($query);
    echo "Reliance Digital found " . count($relianceResults) . " results.\n";

    $aggScraper = new PriceAggregatorScraper();
    $aggResults = $aggScraper->searchAll($query);
    echo "PriceAggregator found " . count($aggResults) . " results.\n";

    $vsCount = count($vsResults);
    $cromaCount = count($cromaResults);
    $relianceCount = count($relianceResults);
    $poorvikaCount = count($poorvikaResults);
    $iplanetCount = count($iPlanetResults);
    $aggCount = count($aggResults);
    
    echo "\nSummary of Direct Scraper Results:\n";
    echo "- Vijay Sales: $vsCount\n";
    echo "- Croma: $cromaCount\n";
    echo "- Reliance Digital: $relianceCount\n";
    echo "- Poorvika: $poorvikaCount\n";
    echo "- iPlanet: $iplanetCount\n";
    echo "- PriceAggregator (MySmartPrice/91M): $aggCount\n";
}
