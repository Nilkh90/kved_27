<?php

namespace App\Console\Commands;

use App\Models\Kved2010;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportKvedLocal extends Command
{
    protected $signature = 'kved:import-local {--fresh : Truncate all existing data before import}';

    protected $description = 'Import KVED-2010 hierarchy from local HTML files';

    private string $sourcePath;

    public function handle(): int
    {
        $this->sourcePath = base_path('../html_source/KVED2010');

        if (!File::isDirectory($this->sourcePath)) {
            $this->error("Source directory not found: {$this->sourcePath}");
            return Command::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('Deleting existing kved_2010 data...');
            Kved2010::query()->delete();
            $this->info('Deleted.');
        }

        $this->info('Starting KVED-2010 local import...');

        // Step 1: Import Sections
        $this->info('[1/3] Importing Sections from SECT folder...');
        $this->importSections();

        // Step 2: Import Divisions, Groups, Classes from numbered folders
        $this->info('[2/3] Importing Divisions, Groups and Classes from numbered folders...');
        $this->importHierarchicalFolders();

        // Step 3: Cleanup/Verification
        $count = Kved2010::count();
        $this->info("✅ KVED-2010 import completed! Total records: $count");

        return Command::SUCCESS;
    }

    private function importSections(): void
    {
        $sectPath = $this->sourcePath . DIRECTORY_SEPARATOR . 'SECT';
        if (!File::isDirectory($sectPath)) {
            $this->warn('SECT folder not found.');
            return;
        }

        $files = File::files($sectPath);
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (!preg_match('/KVED10_([A-U])\.html$/i', $filename, $matches)) {
                continue;
            }

            $letter = strtoupper($matches[1]);
            $html = $this->readFile($file->getPathname());
            $data = $this->parseNodePage($html);

            Kved2010::updateOrCreate(
                ['code' => $letter],
                [
                    'title' => $data['title'] ?? "Секція $letter",
                    'level' => 'SECTION',
                    'parent_id' => null,
                    'description' => $this->processHtml($data['description'] ?? ''),
                ]
            );
            $this->line("  Section $letter imported.");
        }
    }

    private function importHierarchicalFolders(): void
    {
        $directories = File::directories($this->sourcePath);
        sort($directories);

        foreach ($directories as $dir) {
            $folderName = basename($dir);
            if ($folderName === 'SECT') continue;
            if (!preg_match('/^\d{2}$/', $folderName)) continue;

            $this->info("  Processing folder $folderName...");
            
            $files = File::files($dir);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                // KVED10_32.html -> Division
                // KVED10_32_9.html -> Group
                // KVED10_32_99.html -> Class
                
                if (!preg_match('/KVED10_(\d{2}(?:_\d{1,2})?)\.html$/i', $filename, $matches)) {
                    continue;
                }

                $codeRaw = $matches[1];
                $code = str_replace('_', '.', $codeRaw);
                
                $html = $this->readFile($file->getPathname());
                $data = $this->parseNodePage($html);
                
                if (!$data['title']) {
                    // Fallback to filename if title parsing failed
                    $data['title'] = "Назва для $code";
                }

                $level = $this->determineLevel($code);
                $parentId = $this->findParentId($code, $level, $html);

                Kved2010::updateOrCreate(
                    ['code' => $code],
                    [
                        'title' => $data['title'],
                        'level' => $level,
                        'parent_id' => $parentId,
                        'description' => $this->processHtml($data['description'] ?? ''),
                        'includes' => $data['includes'] ?? [],
                        'includes_also' => $data['includes_also'] ?? [],
                        'excludes' => $data['excludes'] ?? [],
                    ]
                );
            }
        }
    }

    private function readFile(string $path): string
    {
        $content = File::get($path);
        return mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
    }

    private function determineLevel(string $code): string
    {
        if (preg_match('/^[A-Z]$/', $code)) return 'SECTION';
        if (preg_match('/^\d{2}$/', $code)) return 'DIVISION';
        if (preg_match('/^\d{2}\.\d$/', $code)) return 'GROUP';
        if (preg_match('/^\d{2}\.\d{2}$/', $code)) return 'CLASS';
        return 'UNKNOWN';
    }

    private function findParentId(string $code, string $level, string $html): ?int
    {
        if ($level === 'SECTION') return null;

        if ($level === 'DIVISION') {
            // Division parent is a Section, found in Up_Link_Mar_1
            if (preg_match('/class=["\'][^"\']*Up_Link_Mar_1[^"\']*["\'][^>]*>(.*?)<\/table>/si', $html, $tableMatch)) {
                if (preg_match('/<td[^>]+class=["\'][^"\']*UpLink_col1[^"\']*["\'][^>]*>(.*?)<\/td>/si', $tableMatch[1], $cellMatch)) {
                    if (preg_match('/KVED10_([A-Z])\.html/i', $cellMatch[1], $m)) {
                        $sectionLetter = strtoupper($m[1]);
                        return Kved2010::where('code', $sectionLetter)->first()?->id;
                    }
                }
            }
        }

        if ($level === 'GROUP') {
            // Group parent is a Division, found in Up_Link_Mar_2
            if (preg_match('/class=["\'][^"\']*Up_Link_Mar_2[^"\']*["\'][^>]*>(.*?)<\/table>/si', $html, $tableMatch)) {
                if (preg_match('/<td[^>]+class=["\'][^"\']*UpLink_col1[^"\']*["\'][^>]*>(.*?)<\/td>/si', $tableMatch[1], $cellMatch)) {
                    if (preg_match('/KVED10_(\d{2})\.html/i', $cellMatch[1], $m)) {
                        return Kved2010::where('code', $m[1])->first()?->id;
                    }
                }
            }
            // Fallback for groups
            $divCode = substr($code, 0, 2);
            return Kved2010::where('code', $divCode)->first()?->id;
        }

        if ($level === 'CLASS') {
            // Class parent is a Group, found in Up_Link_Mar_3
            if (preg_match('/class=["\'][^"\']*Up_Link_Mar_3[^"\']*["\'][^>]*>(.*?)<\/table>/si', $html, $tableMatch)) {
                if (preg_match('/<td[^>]+class=["\'][^"\']*UpLink_col1[^"\']*["\'][^>]*>(.*?)<\/td>/si', $tableMatch[1], $cellMatch)) {
                    if (preg_match('/KVED10_(\d{2}_\d+)\.html/i', $cellMatch[1], $m)) {
                        $groupCode = str_replace('_', '.', $m[1]);
                        return Kved2010::where('code', $groupCode)->first()?->id;
                    }
                }
            }
            // Fallback for classes
            if (preg_match('/^(\d{2}\.\d)/', $code, $m)) {
                return Kved2010::where('code', $m[1])->first()?->id;
            }
        }

        return null;
    }

    private function parseNodePage(string $html): array
    {
        $data = [
            'title' => null,
            'description' => null,
            'includes' => [],
            'includes_also' => [],
            'excludes' => [],
        ];

        // 1. Title - Prioritize <p class="Na">
        if (preg_match('/<p class="Na">(.*?)<\/p>/si', $html, $m)) {
            $data['title'] = trim(strip_tags($m[1]));
        }

        // Fallback to <h1>
        if (!$data['title'] && preg_match('/<h1>(?:.*?:)?\s*(?:(?:Клас|Група|Розділ|Секція)\s+[\dA-Z\.]+)\s*(.*?)<\/h1>/si', $html, $m)) {
            $data['title'] = trim(strip_tags($m[1]));
        }

        // 2. Main Description (usually for Sections/Divisions)
        // Extract content after Na paragraph and before any Zp lists
        if (preg_match('/<p class="Na">.*?<\/p>(.*?)<em class="Zp/si', $html, $m)) {
            $desc = trim($m[1]);
            $data['description'] = $this->cleanText($desc);
        } elseif (preg_match('/<p class="Na">.*?<\/p>(.*?)<table/si', $html, $m)) {
             $desc = trim($m[1]);
             $data['description'] = $this->cleanText($desc);
        }

        // 3. Includes (Zp1)
        if (preg_match('/<em class="Zp1">.*?<\/em>\s*<ul>(.*?)<\/ul>/si', $html, $m)) {
            $data['includes'] = $this->parseList($m[1]);
        }

        // 4. Includes also (Zp2)
        if (preg_match('/<em class="Zp2">.*?<\/em>\s*<ul>(.*?)<\/ul>/si', $html, $m)) {
            $data['includes_also'] = $this->parseList($m[1]);
        }

        // 5. Excludes (Zp3)
        if (preg_match('/<em class="Zp3">.*?<\/em>\s*<ul>(.*?)<\/ul>/si', $html, $m)) {
            $data['excludes'] = $this->parseList($m[1]);
        }

        return $data;
    }

    private function parseList(string $ulContent): array
    {
        preg_match_all('/<li[^>]*>(.*?)<\/li>/si', $ulContent, $matches);
        $items = [];
        foreach ($matches[1] as $li) {
            // Keep <a> tags, but clean other markup
            $items[] = $this->processHtml($li);
        }
        return array_filter($items);
    }

    private function processHtml(?string $html): string
    {
        if (empty($html)) return '';

        // 1. Convert legacy <a> tags completely
        // Handle patterns like: KVED10_F.html, SECT/KVED10_F.html, 28/KVED10_28.html, etc.
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
        $html = $this->cleanText($html);

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

            // Class (XX.XX) - Match codes like 33.12, 95.11
            $part = preg_replace_callback('/(?<![\d\.])(\d{2}\.\d{2})(?![\d\.])/', function($m) {
                $url = route('catalog.show_by_code', ['standard' => 'kved', 'code' => $m[1]], false);
                return '<a href="' . $url . '">' . $m[1] . '</a>';
            }, $part);

            // Group (XX.X) - Match codes like 33.1
            $part = preg_replace_callback('/(?<![\d\.])(\d{2}\.\d)(?![\d\.])/', function($m) {
                $url = route('catalog.show_by_code', ['standard' => 'kved', 'code' => $m[1]], false);
                return '<a href="' . $url . '">' . $m[1] . '</a>';
            }, $part);

            // Section (Standalone A-U) - Match codes like Секція C
            $part = preg_replace_callback('/(?<=\s|^|див\.|Секція|код)\s*([A-U])(?=\s|$|\.|\,)/u', function($m) {
                 $url = route('catalog.show_by_code', ['standard' => 'kved', 'code' => $m[1]], false);
                 return ' <a href="' . $url . '">' . $m[1] . '</a>';
            }, $part);

            // Division (XX) - Match codes like 33
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

    private function cleanText(string $text): string
    {
        $text = html_entity_decode($text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}
