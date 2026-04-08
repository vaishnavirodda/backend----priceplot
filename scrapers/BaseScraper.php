<?php
// scrapers/BaseScraper.php
abstract class BaseScraper {

    protected $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4.1 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36'
    ];

    // Returns ['html' => string, 'finalUrl' => string] or false on failure.
    public function fetchHTML($url, $referrer = '') {
        // Random delay to mimic human behavior
        usleep(rand(500000, 1500000));

        $ua = $this->userAgents[array_rand($this->userAgents)];
        $cookieFile = tempnam(sys_get_temp_dir(), 'scraper_cookie_');

        $host = parse_url($url, PHP_URL_HOST);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => $ua,
            CURLOPT_ENCODING       => '',
            CURLOPT_COOKIEJAR      => $cookieFile,
            CURLOPT_COOKIEFILE     => $cookieFile,
            CURLOPT_HTTPHEADER     => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language: en-IN,en-GB;q=0.9,en;q=0.8',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Pragma: no-cache',
                'Referer: ' . $referrer,
                'sec-ch-ua: "Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"',
                'Sec-Fetch-Dest: document',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: ' . (strpos($referrer, $host) !== false ? 'same-origin' : 'cross-site'),
                'Sec-Fetch-User: ?1',
                'Upgrade-Insecure-Requests: 1',
                'dnt: 1',
            ],
        ]);

        $html     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $error    = curl_error($ch);
        curl_close($ch);

        if (file_exists($cookieFile)) { @unlink($cookieFile); }

        if ($error) {
            error_log("[BaseScraper] cURL error for $url: $error");
            return false;
        }
        
        // Handle Cloudflare challenge pages or other blocks if possible
        if ($httpCode === 403 || $httpCode === 401) {
            error_log("[BaseScraper] Access denied (HTTP $httpCode) for $url");
            if (stripos($html, 'Access Denied') !== false || stripos($html, 'Cloudflare') !== false) {
                error_log("[BaseScraper] Shield/WAF detected for $url");
            }
            // If we got some HTML back, maybe it's a partial or soft block we can still use
            if (!empty($html) && strlen($html) > 5000) {
                return ['html' => $html, 'finalUrl' => $finalUrl];
            }
            return false;
        }

        if ($httpCode < 200 || $httpCode >= 400) {
            error_log("[BaseScraper] HTTP $httpCode for $url");
            return false;
        }

        return ['html' => $html, 'finalUrl' => $finalUrl];
    }

    protected function createXPath($html) {
        if (empty($html)) return null;
        if (!class_exists('DOMDocument')) {
            error_log("[BaseScraper] DOMDocument class not found! Please enable php-xml/php-dom extension.");
            return null;
        }
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        // Clean up HTML to avoid parser warnings
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        @$dom->loadHTML($html);
        libxml_clear_errors();
        return new DOMXPath($dom);
    }

    protected function safeQuery($xpath, $query, $context = null) {
        if (!$xpath) return []; // Return empty array
        $res = $xpath->query($query, $context);
        return $res ? $res : [];
    }

    // Try a list of XPath expressions; return textContent of first match
    protected function xpathFirst($xpath, array $selectors) {
        if (!$xpath) return '';
        foreach ($selectors as $sel) {
            $nodes = $this->safeQuery($xpath, $sel);
            if ($nodes && count($nodes) > 0) {
                return $nodes[0]->textContent;
            }
        }
        return '';
    }

    // Same as xpathFirst but for attribute-node selectors (e.g. //img/@src)
    protected function xpathAttr($xpath, array $selectors) {
        if (!$xpath) return '';
        foreach ($selectors as $sel) {
            $nodes = $xpath->query($sel);
            if ($nodes && count($nodes) > 0) {
                return $nodes[0]->textContent;
            }
        }
        return '';
    }

    protected function cleanPrice($text) {
        if (empty($text)) return 0.0;
        // Handle formats like "₹93,499.00" or "Rs. 93499"
        $cleaned = preg_replace('/[^\d.]/', '', $text);
        return floatval($cleaned);
    }

    protected function cleanText($text) {
        return trim(preg_replace('/\s+/', ' ', (string)$text));
    }

    abstract public function scrape($url);
}
?>