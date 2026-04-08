<?php
require_once 'C:/xampp/htdocs/price_plot/scrapers/CrossPlatformScraper.php';
$scraper = new CrossPlatformScraper();
echo "Searching for iPhone 15...\n";
$results = $scraper->searchAll('iPhone 15');
echo "Found " . count($results) . " results.\n";
foreach ($results as $r) {
    echo "- {$r['platform']}: {$r['price']}\n";
}
