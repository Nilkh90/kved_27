<?php

namespace App\Console\Commands;

use App\Models\Kved2010;
use App\Models\Nace2027;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixInterlinking extends Command
{
    protected $signature = 'catalog:fix-interlinking {--dry-run : Display changes without saving} {--standard=all : Standard to fix (kved, nace, all)}';
    protected $description = 'Fix legacy HTML links and apply autolinking to all catalog records';

    public function handle(): int
    {
        $standard = $this->option('standard');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE: No changes will be saved to the database.');
        }

        if ($standard === 'all' || $standard === 'kved') {
            $this->fixStandard(Kved2010::class, 'KVED-2010', $dryRun);
        }

        if ($standard === 'all' || $standard === 'nace') {
            $this->fixStandard(Nace2027::class, 'NACE-2027', $dryRun);
        }

        $this->info('Interlinking fix completed!');
        return Command::SUCCESS;
    }

    private function fixStandard(string $modelClass, string $label, bool $dryRun): void
    {
        $this->info("Processing $label...");
        $items = $modelClass::all();
        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        $changesCount = 0;

        foreach ($items as $item) {
            $changed = false;

            // Fields to process
            $fields = ['description', 'includes', 'includes_also', 'excludes'];

            foreach ($fields as $field) {
                $original = $item->$field;
                if (empty($original)) continue;

                $processed = null;
                if (is_array($original)) {
                    $processed = array_map(fn($val) => $this->processHtml($val), $original);
                    if ($processed !== $original) {
                        $item->$field = $processed;
                        $changed = true;
                    }
                } else {
                    $processed = $this->processHtml($original);
                    if ($processed !== $original) {
                        $item->$field = $processed;
                        $changed = true;
                    }
                }
            }

            if ($changed) {
                $changesCount++;
                if (!$dryRun) {
                    $item->save();
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  $label: $changesCount records " . ($dryRun ? "would be changed" : "updated") . ".");
    }

    private function processHtml(?string $html): string
    {
        if (empty($html)) return '';

        // 1. Convert legacy <a> tags completely
        $html = preg_replace_callback('/<a\s+[^>]*?href\s*=\s*(["\'])(.*?)\1[^>]*?>(.*?)<\/a>/si', function($m) {
            $href = $m[2];
            $text = $m[3];
            
            // Match KVED10_ followed by code (possibly with underscores) and ending in .html
            // Allow optional leading paths like SECT/ or 28/ or ../
            if (preg_match('/(?:[\.\.\/]*)(?:SECT\/|[\d_]+\/)*KVED10_([A-Z0-9_]+)\.html/i', $href, $matches)) {
                $code = str_replace('_', '.', $matches[1]);
                $url = route('catalog.show_by_code', ['standard' => 'kved', 'code' => $code], false);
                return '<a href="' . $url . '">' . $text . '</a>';
            }
            
            // Fix broken links like ../95//catalog/kved/95.11
            if (strpos($href, '/catalog/kved/') !== false) {
                 if (preg_match('/\/catalog\/kved\/([A-Z0-9\.]+)/i', $href, $matches)) {
                     $code = $matches[1];
                     $url = route('catalog.show_by_code', ['standard' => 'kved', 'code' => $code], false);
                     return '<a href="' . $url . '">' . $text . '</a>';
                 }
            }

            return $m[0];
        }, $html);

        // 2. Clean remaining tags but preserve <a>
        $html = strip_tags($html, '<a>');
        
        // 3. Autolink plain text codes
        return $this->autoLink($html);
    }

    private function autoLink(string $text): string
    {
        if (empty($text)) return '';

        // Skip already linked content
        $parts = preg_split('/(<a[^>]*>.*?<\/a>)/si', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as &$part) {
            if (strpos($part, '<a') === 0) continue;

            // Class (XX.XX)
            $part = preg_replace_callback('/(?<![\d\.])(\d{2}\.\d{2})(?![\d\.])/', function($m) {
                // Determine which standard to link to? Defaulting to kved for now as requested.
                $url = route('catalog.show_by_code', ['standard' => 'kved', 'code' => $m[1]], false);
                return '<a href="' . $url . '">' . $m[1] . '</a>';
            }, $part);

            // Group (XX.X)
            $part = preg_replace_callback('/(?<![\d\.])(\d{2}\.\d)(?![\d\.])/', function($m) {
                $url = route('catalog.show_by_code', ['standard' => 'kved', 'code' => $m[1]], false);
                return '<a href="' . $url . '">' . $m[1] . '</a>';
            }, $part);

            // Section (Standalone A-U)
            $part = preg_replace_callback('/(?<=\s|^|див\.|Секція|код)\s*([A-U])(?=\s|$|\.|\,)/u', function($m) {
                 $url = route('catalog.show_by_code', ['standard' => 'kved', 'code' => $m[1]], false);
                 return ' <a href="' . $url . '">' . $m[1] . '</a>';
            }, $part);

            // Division (XX)
            $part = preg_replace_callback('/(?<=див\.|Розділ|код)\s*(\d{2})(?=\s|$|\.|\,)/u', function($m) {
                 if (is_numeric($m[1]) && (int)$m[1] >= 1 && (int)$m[1] <= 99) {
                    $url = route('catalog.show_by_code', ['standard' => 'kved', 'code' => $m[1]], false);
                    return ' <a href="' . $url . '">' . $m[1] . '</a>';
                 }
                 return $m[0];
            }, $part);
        }
        return implode('', $parts);
    }
}
