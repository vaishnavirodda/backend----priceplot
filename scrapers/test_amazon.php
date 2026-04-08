<?php
require_once 'C:/xampp/htdocs/price_plot/scrapers/AmazonScraper.php';
$scraper = new AmazonScraper();
$url = "https://www.amazon.in/s?k=iphone+15";
$result = $scraper->scrape($url);
print_r($result);
