<?php
require_once __DIR__ . '/CrossPlatformScraper.php';
require_once __DIR__ . '/PriceAggregatorScraper.php';

$cross = new CrossPlatformScraper();
$agg = new PriceAggregatorScraper();

echo "Running CrossPlatformScraper for 'Apple iPhone 15 Pro'\n";
$res1 = $cross->searchAll('Apple iPhone 15 Pro');
print_r($res1);

echo "Running PriceAggregatorScraper for 'Apple iPhone 15 Pro'\n";
$res2 = $agg->searchAll('Apple iPhone 15 Pro');
print_r($res2);
