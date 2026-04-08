<?php
require_once 'C:/xampp/htdocs/price_plot/scrapers/PoorvikaScraper.php';
$s = new PoorvikaScraper();
$res = $s->search('iPhone 15');
echo "Count: " . count($res) . "\n";
print_r(array_slice($res, 0, 2));
