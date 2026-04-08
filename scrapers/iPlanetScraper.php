<?php
// scrapers/iPlanetScraper.php
require_once __DIR__ . '/BaseScraper.php';

class iPlanetScraper extends BaseScraper {
    public function scrape($url) {
        return ['success' => false, 'error' => 'Use search() for iPlanet'];
    }

    public function search($query) {
        $searchUrl = "https://iplanet.one/search?q=" . urlencode($query);
        error_log("[iPlanetScraper] Searching: $searchUrl");

        $response = $this->fetchHTML($searchUrl, 'https://www.google.com/');
        if (!$response) return [];

        $html = $response['html'];

        $results = [];
        $xpath = $this->createXPath($html);
        
        // iPlanet (Shopify based) usually uses div.product-item or div.grid-box
        $items = $xpath->query("//div[contains(@class,'product-item')]");
        if ($items->length === 0) {
             $items = $xpath->query("//div[contains(@class,'grid-view-item')]");
        }

        foreach ($items as $item) {
            $nameNode = $xpath->query(".//a[contains(@class,'product-item__title')]", $item)->item(0);
            if (!$nameNode) $nameNode = $xpath->query(".//div[contains(@class,'title')]//a", $item)->item(0);
            
            $priceNode = $xpath->query(".//span[contains(@class,'price')]", $item)->item(0);
            if (!$priceNode) $priceNode = $xpath->query(".//div[contains(@class,'price')]", $item)->item(0);

            if ($nameNode && $priceNode) {
                $name = $this->cleanText($nameNode->textContent);
                $price = $this->cleanPrice($priceNode->textContent);
                $link = $nameNode->getAttribute('href');
                if ($link && strpos($link, 'http') !== 0) {
                    $link = "https://iplanet.one" . $link;
                }

                if ($price > 0 && stripos($name, 'iPhone 15') !== false) {
                    $results[] = [
                        'platform' => 'iPlanet',
                        'productName' => $name,
                        'price' => $price,
                        'currency' => 'INR',
                        'link' => $link,
                        'availability' => 'In Stock'
                    ];
                }
            }
        }

        return $results;
    }
}
