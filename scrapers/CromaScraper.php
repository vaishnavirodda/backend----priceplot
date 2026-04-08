<?php
require_once __DIR__ . '/BaseScraper.php';

class CromaScraper extends BaseScraper {
    private $searchUrl = "https://www.croma.com/searchB?q=";

    public function search($query) {
        $url = $this->searchUrl . urlencode($query) . ":relevance";
        $res = $this->fetchHTML($url);
        if (!$res) return [];

        $html = $res['html'];

        $xpath = $this->createXPath($html);
        $results = [];

        // Croma search results are often in JSON or specific tags
        // We'll try common CSS selectors for PLP
        $items = $xpath->query("//li[contains(@class, 'product-item')]");
        if ($items->length === 0) {
            // Fallback: search for generic product containers
            $items = $xpath->query("//div[contains(@class, 'cp-product')]");
        }

        foreach ($items as $item) {
            $nameNode = $xpath->query(".//h3[contains(@class, 'product-title')] | .//a[contains(@class, 'product-title')]", $item)->item(0);
            $priceNode = $xpath->query(".//span[contains(@class, 'amount')] | .//span[contains(@class, 'new-price')]", $item)->item(0);
            $linkNode = $xpath->query(".//a[contains(@href, '/p/')]", $item)->item(0);

            if ($nameNode && $priceNode && $linkNode) {
                $name = trim($nameNode->textContent);
                $price = $this->cleanPrice($priceNode->textContent);
                $link = $linkNode->getAttribute('href');
                if (strpos($link, 'http') !== 0) $link = "https://www.croma.com" . $link;

                // Simple match check
                $queryTokens = explode(' ', strtolower($query));
                $matchCount = 0;
                foreach ($queryTokens as $token) {
                    if (strpos(strtolower($name), $token) !== false) $matchCount++;
                }

                if ($matchCount >= count($queryTokens) * 0.5) {
                    $results[] = [
                        'name' => $name,
                        'price' => $price,
                        'link' => $link,
                        'platform' => 'Croma'
                    ];
                }
            }
        }

        return $results;
    }

    public function scrape($url) {
        // Not needed for now as we get price from search
        return [];
    }
}
