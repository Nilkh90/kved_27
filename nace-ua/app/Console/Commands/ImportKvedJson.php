<?php

namespace App\Console\Commands;

use App\Models\Kved2010;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportKvedJson extends Command
{
    protected $signature = 'kved:import-json 
                            {--file=storage/app/kved_all_sections.json : The JSON file path relative to base path}
                            {--truncate : Delete all existing data before import}';
    protected $description = 'Import KVED-2010 data from a local JSON file';

    public function handle()
    {
        $filePath = base_path($this->option('file'));
        
        if (!File::exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $this->info("Reading JSON file...");
        $jsonContent = File::get($filePath);
        $json = json_decode($jsonContent, true);
        
        if (!$json) {
            $this->error("Invalid JSON format. Check file encoding or structure.");
            return 1;
        }

        $this->warn("Truncating kved_2010 table...");
        // Use TRUNCATE CASCADE to handle foreign keys if necessary
        DB::statement('TRUNCATE kved_2010 RESTART IDENTITY CASCADE');

        $this->info("Starting recursive import...");
        
        DB::beginTransaction();
        try {
            foreach ($json as $section) {
                $this->importNode($section, 'SECTION');
            }
            DB::commit();
            $this->info("✅ Import completed successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Import failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function importNode(array $data, string $level, ?int $parentId = null)
    {
        $descriptionRaw = $data['description'] ?? null;
        $includes = [];
        $excludes = [];
        $cleanDescription = null;

        if ($descriptionRaw) {
            // Extract includes/excludes more robustly
            if (preg_match('/<em class="Zp1">.*?<\/em>(.*?)(?=<em class="Zp3">|$)/si', $descriptionRaw, $matches)) {
                $includes = $this->parseList($matches[1]);
            }
            
            if (preg_match('/<em class="Zp3">.*?<\/em>(.*?)(?=<em class="Zp1">|$)/si', $descriptionRaw, $matches)) {
                $excludes = $this->parseList($matches[1]);
            }
            
            // Clean description: remove the include/exclude blocks and keep structure/links
            $cleanDescription = preg_replace('/<em class="Zp[13]">.*?<\/em>.*?(?=<em class="Zp[13]">|$)/si', '', $descriptionRaw);
            $cleanDescription = strip_tags($cleanDescription, '<a><br><p><b><strong><i><em>');
            $cleanDescription = trim(preg_replace('/\s+/', ' ', $cleanDescription));
            
            if (empty($cleanDescription)) {
                $cleanDescription = null;
            }
        }

        $model = Kved2010::updateOrCreate(
            ['code' => $data['code']],
            [
                'title' => $this->cleanText($data['title']),
                'level' => $level,
                'parent_id' => $parentId,
                'description' => $cleanDescription,
                'includes' => $includes,
                'excludes' => $excludes,
            ]
        );

        $this->line("  [{$level}] {$data['code']}");

        // Map child levels and keys
        $mapping = [
            'SECTION' => ['level' => 'DIVISION', 'key' => 'divisions'],
            'DIVISION' => ['level' => 'GROUP', 'key' => 'groups'],
            'GROUP' => ['level' => 'CLASS', 'key' => 'classes'],
        ];

        if (isset($mapping[$level])) {
            $childLevel = $mapping[$level]['level'];
            $childKey = $mapping[$level]['key'];
            
            if (isset($data[$childKey]) && is_array($data[$childKey])) {
                foreach ($data[$childKey] as $child) {
                    $this->importNode($child, $childLevel, $model->id);
                }
            }
        }
    }

    private function parseList(string $html): array
    {
        // First, capture content from all <li> tags
        preg_match_all('/<li[^>]*>(.*?)<\/li>/si', $html, $liMatches);
        $items = [];
        foreach ($liMatches[1] as $item) {
            $cleaned = trim(strip_tags($item, '<a><b><strong><i><em>'));
            if (!empty($cleaned)) {
                $items[] = $cleaned;
            }
        }

        // If we found <li> items, we still check if there is any "hanging" text with <a> tags 
        // that was outside <li> tags (like in the case of code 36.00).
        $outsideHtml = preg_replace('/<li[^>]*>.*?<\/li>/si', '', $html);
        $outsideHtml = preg_replace('/<ul[^>]*>|<\/ul>/si', '', $outsideHtml);
        
        // Split by <br> or just look for lines with <a>
        $parts = preg_split('/<br\s*\/?>/i', $outsideHtml);
        foreach ($parts as $part) {
            $cleaned = trim(strip_tags($part, '<a><b><strong><i><em>'));
            // Only add if it contains a link or is not empty and we don't already have it
            if (!empty($cleaned) && (strpos($cleaned, '<a') !== false || strlen($cleaned) > 10)) {
                if (!in_array($cleaned, $items)) {
                    $items[] = $cleaned;
                }
            }
        }

        return $items;
    }

    private function cleanText(string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags($text)));
    }
}
