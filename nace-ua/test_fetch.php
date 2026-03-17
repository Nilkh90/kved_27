<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://kved.ukrstat.gov.ua/KVED2010/SECT/KVED10_J.html';
echo "Fetching: $url\n";

try {
    $response = Http::timeout(15)->get($url);
    echo "HTTP Status: " . $response->status() . "\n";
    if ($response->successful()) {
        $html = $response->body();
        echo "Body length: " . strlen($html) . " bytes\n";

        // Parse List_Row rows
        preg_match_all('/<tr[^>]+class="List_Row"[^>]*>(.*?)<\/tr>/si', $html, $matches);
        echo "Found " . count($matches[1]) . " rows\n";
        foreach ($matches[1] as $i => $rowHtml) {
            if (preg_match('/<a[^>]+href="([^"]+)"[^>]*>([^<]+)<\/a>/si', $rowHtml, $linkMatch)) {
                $code = trim($linkMatch[2]);
                if (preg_match('/<p[^>]*>(.*?)<\/p>/si', $rowHtml, $titleMatch)) {
                    $title = trim(strip_tags($titleMatch[1]));
                    echo "  [$code] $title\n";
                }
            }
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
