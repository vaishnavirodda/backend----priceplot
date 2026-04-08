<?php
// scrapers/PoorvikaScraper.php
require_once __DIR__ . '/BaseScraper.php';

class PoorvikaScraper extends BaseScraper {
    public function scrape($url) {
        return ['success' => false, 'error' => 'Use search() for Poorvika'];
    }

    public function search($query) {
        $searchUrl = "https://www.poorvika.com/s?q=" . urlencode($query);
        error_log("[PoorvikaScraper] Searching: $searchUrl");

        $response = $this->fetchHTML($searchUrl, 'https://www.google.com/');
        if (!$response) return [];

        $html = $response['html'];

        $results = [];
        $xpath = $this->createXPath($html);
        $results = [];

        if (!$xpath) {
            // Regex Fallback (Partial)
            preg_match_all('~product-cardlist_card__IeCc4.*?href="(/p/[^"]+)".*?<b>(.*?)</b>.*?product-cardlist_price__1aKwZ.*?<span>([^<]+)</span>~is', $html, $matches, PREG_SET_ORDER);
            foreach ($matches as $m) {
                $link = "https://www.poorvika.com" . $m[1];
                $name = $this->cleanText($m[2]);
                $price = $this->cleanPrice($m[3]);
                if ($price > 0 && stripos($name, 'iPhone 15') !== false) {
                    $results[] = [
                        'platform' => 'Poorvika',
                        'productName' => $name,
                        'price' => $price,
                        'currency' => 'INR',
                        'link' => $link,
                        'availability' => 'In Stock'
                    ];
                }
            }
            return $results;
        }

        $items = $xpath->query("//div[contains(@class,'product-cardlist_card')]");

        foreach ($items as $item) {
            $nameNode = $xpath->query(".//a[contains(@href,'/p')]//b", $item)->item(0);
            $priceNode = $xpath->query(".//div[contains(@class,'price')]//span", $item)->item(0);
            $linkNode = $xpath->query(".//a[contains(@href,'/p')]", $item)->item(0);

            if ($nameNode && $priceNode) {
                $name = $this->cleanText($nameNode->textContent);
                $price = $this->cleanPrice($priceNode->textContent);
                $link = $linkNode ? $linkNode->getAttribute('href') : "";
                
                if ($link && strpos($link, 'http') !== 0) {
                    $link = "https://www.poorvika.com" . $link;
                }

                if ($price > 0 && stripos($name, 'iPhone 15') !== false) {
                    $results[] = [
                        'platform' => 'Poorvika',
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
