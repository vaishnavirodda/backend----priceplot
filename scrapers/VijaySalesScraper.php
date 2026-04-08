<?php
require_once __DIR__ . '/BaseScraper.php';

class VijaySalesScraper extends BaseScraper {
    private $searchUrl = "https://www.vijaysales.com/search/";

    public function search($query) {
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($query));
        $url = $this->searchUrl . trim($slug, '-');
        $res = $this->fetchHTML($url);
        if (!$res) {
            return [];
        }
        $html = $res['html'];

        $xpath = $this->createXPath($html);
        $results = [];

        // Vijay Sales uses a specific structure for product items
        // Based on the HTML seen, it uses class "productcollection__item"
        $items = $xpath->query("//a[contains(@class, 'productcollection__item')]");

        foreach ($items as $item) {
            $nameNode = $xpath->query(".//div[contains(@class, 'productcollection__item-title')]/span", $item)->item(0);
            $priceNode = $xpath->query(".//div[contains(@class, 'price')]/span", $item)->item(0);
            $link = $item->getAttribute('href');

            if ($nameNode && $priceNode) {
                $name = trim($nameNode->nodeValue);
                $priceText = trim($priceNode->nodeValue);
                $price = $this->cleanPrice($priceText);

                // Basic relevance check: query tokens should be in the name
                $queryTokens = explode(' ', strtolower($query));
                $matchCount = 0;
                foreach ($queryTokens as $token) {
                    if (strpos(strtolower($name), $token) !== false) {
                        $matchCount++;
                    }
                }

                if ($matchCount >= count($queryTokens) * 0.5) {
                    $results[] = [
                        'platform' => 'Vijay Sales',
                        'name' => $name,
                        'price' => $price,
                        'link' => strpos($link, 'http') === 0 ? $link : "https://www.vijaysales.com" . $link,
                        'image' => '' // Can add later if needed
                    ];
                }
            }
        }

        // Sort by price and take the best match (simplified: just take first for now)
        return array_slice($results, 0, 5);
    }

    public function scrape($url) {
        return ['success' => false]; // Not supported for direct URL yet
    }
}
