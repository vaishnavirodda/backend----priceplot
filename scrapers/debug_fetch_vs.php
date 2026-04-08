<?php
require_once __DIR__ . '/BaseScraper.php';

class DebugScraper extends BaseScraper {
    public function test($url) {
        return $this->fetchHTML($url);
    }
    public function scrape($url) { return []; }
    public function search($query) { return []; }
}

$ds = new DebugScraper();
$url = "https://www.vijaysales.com/search/iphone-15";
echo "Fetching $url...\n";
$res = $ds->test($url);
if ($res) {
    file_put_contents('C:/Users/HP/.gemini/antigravity/brain/969997ab-ffaa-47bc-b7fb-338631636d0f/vs_test.html', $res['html']);
    echo "Saved to vs_test.html\n";
    echo "HTTP Status: " . $res['status'] . "\n";
} else {
    echo "Failed to fetch.\n";
}
