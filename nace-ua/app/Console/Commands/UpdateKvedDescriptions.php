<?php

namespace App\Console\Commands;

use App\Models\Kved2010;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateKvedDescriptions extends Command
{
    protected $signature = 'kved:update-descriptions {--all : Update all records including classes}';
    protected $description = 'Update descriptions and inclusions for Sections, Divisions, and Groups from HTML source';

    private string $sourcePath;
    private $importer;

    public function handle(): int
    {
        $this->sourcePath = base_path('../html_source/KVED2010');
        $this->importer = new ImportKvedLocal();

        $this->info('Starting KVED-2010 description update...');

        $query = Kved2010::query();
        if (!$this->option('all')) {
            $query->whereIn('level', ['SECTION', 'DIVISION', 'GROUP']);
        }

        $records = $query->get();
        $bar = $this->output->createProgressBar($records->count());

        foreach ($records as $record) {
            $path = $this->findHtmlPath($record);
            if ($path && File::exists($path)) {
                $html = $this->readFile($path);
                $data = $this->parseNodePage($html);

                $description = $this->processHtml($data['description'] ?? '');
                
                // For non-classes, if we have text-based inclusions, merge them into description
                // to make them show up as a primary content block.
                if ($record->level !== 'CLASS') {
                    $extraParts = [];
                    if (!empty($data['includes'])) { $extraParts[] = "<strong>Включає:</strong><br>" . implode('<br>', $data['includes']); }
                    if (!empty($data['includes_also'])) { $extraParts[] = "<strong>Також включає:</strong><br>" . implode('<br>', $data['includes_also']); }
                    
                    if (!empty($extraParts)) {
                        $description = $description ? $description . '<br><br>' . implode('<br><br>', $extraParts) : implode('<br><br>', $extraParts);
                    }
                }

                $record->update([
                    'description' => $description,
                    'includes' => $data['includes'] ?? [],
                    'includes_also' => $data['includes_also'] ?? [],
                    'excludes' => $data['excludes'] ?? [],
                ]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info("\n✅ Updated descriptions for " . $records->count() . " records.");

        return Command::SUCCESS;
    }

    private function findHtmlPath(Kved2010 $record): ?string
    {
        $code = $record->code;
        if ($record->level === 'SECTION') {
            return $this->sourcePath . "/SECT/KVED10_{$code}.html";
        }

        // For others, we need to know the folder (which is the first 2 digits)
        $divisionCode = substr($record->code, 0, 2);
        $fileNameCode = str_replace('.', '_', $record->code);
        
        return $this->sourcePath . "/{$divisionCode}/KVED10_{$fileNameCode}.html";
    }

    // Helper methods duplicated from ImportKvedLocal for simplicity or I could make them public there
    // But since I want a self-contained patch, I'll just reflect them or duplicate if small.
    // Actually, I'll just use a reflection-like approach or duplicate the logic.
    
    private function readFile(string $path): string
    {
        $content = File::get($path);
        return mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
    }

    private function parseNodePage(string $html): array
    {
        // Calling the improved method I just modified in ImportKvedLocal
        // I'll use a temporary instance and call its private method via reflection or just duplicate it here.
        // Duplication is safer for a one-off script.
        
        $data = [
            'title' => null,
            'description' => null,
            'includes' => [],
            'includes_also' => [],
            'excludes' => [],
        ];

        if (preg_match('/<p class="Na">(.*?)<\/p>/si', $html, $m)) {
            $data['title'] = trim(strip_tags($m[1]));
        }

        if (preg_match('/<!-- ПЗП -->(.*?)<!-- КЗП -->/si', $html, $m)) {
            $content = $m[1];
            $content = preg_replace('/<p class="Na">.*?<\/p>/si', '', $content);
            $parts = preg_split('/(<em class="Zp[123]">.*?<\/em>)/si', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
            $currentSection = 'description';
            foreach ($parts as $part) {
                $trimmed = trim($part);
                if (empty($trimmed)) continue;
                if (preg_match('/<em class="Zp1">/i', $trimmed)) { $currentSection = 'includes'; continue; }
                if (preg_match('/<em class="Zp2">/i', $trimmed)) { $currentSection = 'includes_also'; continue; }
                if (preg_match('/<em class="Zp3">/i', $trimmed)) { $currentSection = 'excludes'; continue; }
                
                if ($currentSection === 'description') {
                    $data['description'] .= $part;
                } else {
                    if (preg_match('/<ul>(.*?)<\/ul>/si', $part, $ulMatch)) {
                        $data[$currentSection] = array_merge($data[$currentSection], $this->parseList($ulMatch[1]));
                    } else {
                        $subItems = preg_split('/<br\s*\/?>|<\/p>|<p>/i', $part);
                        foreach ($subItems as $si) {
                            $si = trim(strip_tags($si, '<a>'));
                            if (!empty($si)) {
                                $data[$currentSection][] = $this->processHtml($si);
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    private function parseList(string $ulContent): array
    {
        preg_match_all('/<li[^>]*>(.*?)<\/li>/si', $ulContent, $matches);
        return array_map(fn($li) => trim(strip_tags($li, '<a>')), $matches[1]);
    }

    private function processHtml(?string $html): string
    {
        if (empty($html)) return '';

        // Re-using the same logic as in main importer
        $html = preg_replace_callback('/<a\s+[^>]*?href\s*=\s*(["\'])(.*?)\1[^>]*?>(.*?)<\/a>/si', function($m) {
            $href = $m[2];
            $text = $m[3];
            if (preg_match('/(?:[\.\.\/]*)(?:SECT\/|[\d_]+\/)*KVED10_([A-Z0-9_]+)\.html/i', $href, $matches)) {
                $code = str_replace('_', '.', $matches[1]);
                return '<a href="/catalog/kved/' . $code . '">' . $text . '</a>';
            }
            return $m[0];
        }, $html);

        $html = strip_tags($html, '<a>');
        $html = html_entity_decode($html);
        $html = preg_replace('/\s+/', ' ', $html);
        return trim($html);
    }
}
