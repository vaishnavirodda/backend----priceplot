<?php
// scrapers/RelianceDigitalScraper.php
require_once __DIR__ . '/BaseScraper.php';

class RelianceDigitalScraper extends BaseScraper {
    public function scrape($url) {
        // satisfaction of abstract method
        return ['success' => false, 'error' => 'Use search() for Reliance Digital'];
    }

    public function search($query) {
        $searchUrl = "https://www.reliancedigital.in/search?q=" . urlencode($query) . ":relevance";
        error_log("[RelianceDigitalScraper] Searching: $searchUrl");

        $response = $this->fetchHTML($searchUrl, 'https://www.google.com/');
        if (!$response) return [];

        $html = $response['html'];

        $results = [];
        $xpath = $this->createXPath($html);
        
        // Reliance Digital often uses div.sp__product or similar
        // Let's look for li.slider-item or div.plp__container
        $items = $xpath->query("//div[contains(@class,'plp__container')]//li[contains(@class,'grid')]");
        if ($items->length === 0) {
             $items = $xpath->query("//li[contains(@class,'product-item')]");
        }

        foreach ($items as $item) {
            $nameNode = $xpath->query(".//p[contains(@class,'name')]", $item)->item(0);
            $priceNode = $xpath->query(".//span[contains(@class,'price')]", $item)->item(0);
            $linkNode = $xpath->query(".//a", $item)->item(0);

            if ($nameNode && $priceNode) {
                $name = $this->cleanText($nameNode->textContent);
                $price = $this->cleanPrice($priceNode->textContent);
                $link = $linkNode ? "https://www.reliancedigital.in" . $linkNode->getAttribute('href') : "";

                if ($price > 0) {
                    $results[] = [
                        'platform' => 'Reliance Digital',
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
