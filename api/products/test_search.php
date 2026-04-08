<?php
require_once __DIR__ . '/../../scrapers/CrossPlatformScraper.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Temp Dir: " . sys_get_temp_dir() . "\n";
$query = $_GET['q'] ?? 'iPhone 15';
$platform = $_GET['p'] ?? 'all';

$scraper = new CrossPlatformScraper();

echo "Testing search for: $query (Platform: $platform)\n\n";

if ($platform === 'all') {
    $results = $scraper->searchAll($query);
} else {
    // Manually call specific scrapers to isolate
    require_once __DIR__ . '/../../scrapers/PoorvikaScraper.php';
    require_once __DIR__ . '/../../scrapers/RelianceDigitalScraper.php';
    require_once __DIR__ . '/../../scrapers/iPlanetScraper.php';
    
    switch($platform) {
        case 'poorvika': $s = new PoorvikaScraper(); break;
        case 'reliance': $s = new RelianceDigitalScraper(); break;
        case 'iplanet':  $s = new iPlanetScraper(); break;
        default: die("Invalid platform");
    }
    $results = $s->search($query);
}

echo "Found " . count($results) . " results.\n";
print_r($results);
