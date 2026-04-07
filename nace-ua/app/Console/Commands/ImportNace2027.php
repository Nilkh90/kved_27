<?php

namespace App\Console\Commands;

use App\Models\Nace2027;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportNace2027 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nace:import {--fresh : Truncate the table before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import NACE 2.1-UA (2027) hierarchy from sources/translated_ready_final.txt with full descriptions';

    private $currentSectionId = null;
    private $currentDivisionId = null;
    private $currentGroupId = null;

    private $recordsCount = 0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = base_path('../sources/translated_ready_final.txt');

        if (!file_exists($filePath)) {
            $this->error("Source file not found at: $filePath");
            return Command::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('Truncating nace_2027 table...');
            Nace2027::query()->delete(); // Safer for pgsql permissions
            $this->info('Truncated.');
        }

        // STEP 1: Process master list to ensure all codes exist
        $masterPath = base_path('../sources/2027.txt');
        if (file_exists($masterPath)) {
            $this->info('Step 1: Importing master list from 2027.txt...');
            $lines = file($masterPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $currentCode = null;
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (preg_match('/^([A-Z]|\d{2}|\d{2}\.\d|\d{2}\.\d{2})$/', $trimmed)) {
                    $currentCode = $trimmed;
                } elseif ($currentCode) {
                    $level = 'SECTION';
                    if (preg_match('/^\d{2}\.\d{2}$/', $currentCode)) $level = 'CLASS';
                    elseif (preg_match('/^\d{2}\.\d$/', $currentCode)) $level = 'GROUP';
                    elseif (preg_match('/^\d{2}$/', $currentCode)) $level = 'DIVISION';
                    
                    $this->saveRecord([
                        'code' => $currentCode,
                        'title' => $trimmed,
                        'level' => $level,
                        'description' => '',
                        'includes' => [],
                        'excludes' => [],
                        'includes_also' => [],
                    ]);
                    $currentCode = null;
                }
            }
            $this->info("Master list processed. Total records so far: {$this->recordsCount}");
        }

        // STEP 2: Process detailed descriptions
        $this->info('Step 2: Importing detailed descriptions from translated_ready_final.txt...');
        $handle = fopen($filePath, "r");
        if (!$handle) {
            $this->error("Could not open file.");
            return Command::FAILURE;
        }

        $currentRecord = null;
        $currentBlock = 'description';
        // Reset hierarchy trackers for second pass
        $this->currentSectionId = null;
        $this->currentDivisionId = null;
        $this->currentGroupId = null;

        while (($line = fgets($handle)) !== false) {
            // Remove UTF-8 BOM if present at the start of the file
            if ($this->recordsCount === 0) {
                $line = str_replace("\xEF\xBB\xBF", "", $line);
            }
            $line = trim($line, "\r\n");
            $trimmed = trim($line);

            if (empty($trimmed)) {
                continue;
            }

            // Skip bullet lines immediately before node detection
            if ($this->isBullet($trimmed)) {
                if ($currentRecord) {
                    $this->addLineToBlock($currentRecord, $currentBlock, $trimmed);
                }
                continue;
            }

            // Detect new hierarchy node
            $newNode = null;
            if (preg_match('/^(?:Розділ|Секція)\s+([А-ЯA-ZІЇЄ])(?:\s*[—–-]\s*(.*))?$/ui', $trimmed, $matches)) {
                $char = mb_strtoupper($matches[1]);
                $map = [
                    'А' => 'A', 'Б' => 'B', 'В' => 'C', 'С' => 'C',
                    'Г' => 'D', 'Д' => 'E', 'Е' => 'F', 'Є' => 'G', 'Ж' => 'H', 
                    'З' => 'I', 'И' => 'J', 'І' => 'K', 'Ї' => 'L', 'Й' => 'M', 
                    'К' => 'N', 'Л' => 'O', 'М' => 'P', 'Н' => 'Q', 'О' => 'R', 
                    'П' => 'S', 'Р' => 'T', 'У' => 'U', 'Т' => 'V', 'Ф' => 'V', 'V' => 'V' 
                ];
                $latCode = $map[$char] ?? $char;
                $newNode = ['code' => $latCode, 'title' => trim($matches[2] ?? ''), 'level' => 'SECTION'];
            } elseif (preg_match('/^(\d{2}\.\d{2})(?:\s+(.*))?$/u', $trimmed, $matches)) {
                $newNode = ['code' => $matches[1], 'title' => trim($matches[2] ?? ''), 'level' => 'CLASS'];
            } elseif (preg_match('/^(\d{2}\.\d)(?:\s+(.*))?$/u', $trimmed, $matches)) {
                $newNode = ['code' => $matches[1], 'title' => trim($matches[2] ?? ''), 'level' => 'GROUP'];
            } elseif (preg_match('/^(\d{2})(?:\s+(.*))?$/u', $trimmed, $matches)) {
                $newNode = ['code' => $matches[1], 'title' => trim($matches[2] ?? ''), 'level' => 'DIVISION'];
            }

            if ($newNode) {
                // If title is missing from the same line, it might be on the next line
                if (empty($newNode['title'])) {
                    $nextPos = ftell($handle);
                    $nextLine = fgets($handle);
                    if ($nextLine !== false) {
                        $trimmedNext = trim($nextLine);
                        if (!empty($trimmedNext) && !$this->isCode($trimmedNext)) {
                            $newNode['title'] = $trimmedNext;
                        } else {
                            fseek($handle, $nextPos); // rollback if it's another code
                        }
                    }
                }

                // Save previous record
                if ($currentRecord) {
                    $this->saveRecord($currentRecord);
                    if ($this->recordsCount % 100 === 0) {
                        $this->info("Imported {$this->recordsCount} records...");
                    }
                }

                // Start new record
                $currentRecord = [
                    'code' => $newNode['code'],
                    'title' => $newNode['title'],
                    'level' => $newNode['level'],
                    'description' => '',
                    'includes' => [],
                    'excludes' => [],
                    'includes_also' => [],
                ];
                $currentBlock = 'description';
                continue;
            }

            if (!$currentRecord) {
                continue; // Skip lines until first node
            }

            // Detect block markers (can be in middle of line)
            $markerRegex = '/(Цей\s+(?:клас|підрозділ|розділ|група|спільнота)\s+(?:також\s+)?(?:включає|не\s+включає):|До\s+цього\s+класу\s+(?:також\s+)?належать:)/ui';
            
            if (preg_match($markerRegex, $trimmed, $m, PREG_OFFSET_CAPTURE)) {
                $marker = $m[0][0];
                $offset = $m[0][1];
                $before = trim(substr($trimmed, 0, $offset));
                $after = trim(substr($trimmed, $offset + strlen($marker)));
                
                // If there was content before the marker, add it to the current block
                if (!empty($before)) {
                    $this->addLineToBlock($currentRecord, $currentBlock, $before);
                }
                
                // Determine new block type based on marker
                if (mb_stripos($marker, 'не включає') !== false || mb_stripos($marker, 'не належать') !== false) {
                    $currentBlock = 'excludes';
                } elseif (mb_stripos($marker, 'також') !== false) {
                    $currentBlock = 'includes_also';
                } else {
                    $currentBlock = 'includes';
                }
                
                // If there is content after the marker on the same line, add it to the new block
                if (!empty($after)) {
                    $currentRecord[$currentBlock][] = $this->cleanBullet($after);
                }
                continue;
            }

            // Normal line processing
            $this->addLineToBlock($currentRecord, $currentBlock, $trimmed);
        }

        // Save last record
        if ($currentRecord) {
            $this->saveRecord($currentRecord);
        }

        fclose($handle);

        $this->info("Successfully imported {$this->recordsCount} records into nace_2027.");
        return Command::SUCCESS;
    }

    private function addLineToBlock(array &$currentRecord, string $currentBlock, string $trimmed)
    {
        if ($currentBlock === 'description') {
            // If the line doesn't start with a bullet, it's part of description
            if (!$this->isBullet($trimmed)) {
                $currentRecord['description'] .= ($currentRecord['description'] ? "\n" : "") . $trimmed;
            } else {
                // It's a bullet in description? Fallback to includes if it looks like a list
                $currentRecord['includes'][] = $this->cleanBullet($trimmed);
            }
        } else {
            // In a list block
            if ($this->isBullet($trimmed)) {
                $currentRecord[$currentBlock][] = $this->cleanBullet($trimmed);
            } else {
                // Continuation of a bullet or new paragraph in block
                $lastIdx = count($currentRecord[$currentBlock]) - 1;
                if ($lastIdx >= 0) {
                    $currentRecord[$currentBlock][$lastIdx] .= " " . $trimmed;
                } else {
                    $currentRecord[$currentBlock][] = $trimmed;
                }
            }
        }
    }

    private function saveRecord(array $data)
    {
        $this->info("DEBUG: Saving record with code: [{$data['code']}] level: [{$data['level']}] description length: " . strlen($data['description']));
        $parentId = null;
        if ($data['level'] === 'DIVISION') {
            $parentId = $this->currentSectionId;
        } elseif ($data['level'] === 'GROUP') {
            $parentId = $this->currentDivisionId;
        } elseif ($data['level'] === 'CLASS') {
            $parentId = $this->currentGroupId;
        }

        // Apply autolinking
        $data['excludes'] = array_map(fn($item) => $this->autoLink($item), $data['excludes']);
        $data['includes'] = array_map(fn($item) => $this->autoLink($item), $data['includes']);
        $data['includes_also'] = array_map(fn($item) => $this->autoLink($item), $data['includes_also']);

        $model = Nace2027::updateOrCreate(
            ['code' => $data['code']],
            [
                'title' => $data['title'],
                'level' => $data['level'],
                'parent_id' => $parentId,
                'description' => $this->autoLink($data['description']),
                'includes' => $data['includes'],
                'excludes' => $data['excludes'],
                'includes_also' => $data['includes_also'],
            ]
        );

        if ($data['level'] === 'SECTION') {
            $this->currentSectionId = $model->id;
            $this->currentDivisionId = null;
            $this->currentGroupId = null;
        } elseif ($data['level'] === 'DIVISION') {
            $this->currentDivisionId = $model->id;
            $this->currentGroupId = null;
        } elseif ($data['level'] === 'GROUP') {
            $this->currentGroupId = $model->id;
        }

        $this->recordsCount++;
    }

    private function isCode(string $line): bool
    {
        return preg_match('/^Розділ\s+[A-U]/ui', $line) || 
               preg_match('/^(\d{2}|\d{2}\.\d|\d{2}\.\d{2})(\s|$)/u', $line);
    }

    private function isBullet(string $line): bool
    {
        // Use literal multibyte characters for better cross-platform compatibility
        // Includes –, •, ◦, -
        return preg_match('/^[–•◦-]/u', $line);
    }

    private function cleanBullet(string $line): string
    {
        return trim(preg_replace('/^[\x{2013}\x{2022}\x{25E6}-]\s*/u', '', $line));
    }

    /**
     * Replicates the auto_link_kved logic but for the NACE catalog.
     */
    private function autoLink(?string $text): ?string
    {
        if (!$text) return $text;

        $parts = preg_split('/(<a[^>]*>.*?<\/a>)/si', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as &$part) {
            if (strpos($part, '<a') === 0) continue;
            
            // Match Class (XX.XX)
            $part = preg_replace_callback('/\b(\d{2})\.(\d{2})\b/', function($m) {
                return '<a href="/code/nace/' . $m[1] . '.' . $m[2] . '">' . $m[0] . '</a>';
            }, $part);
            
            // Match Group (XX.X)
            $part = preg_replace_callback('/\b(\d{2})\.(\d)\b/', function($m) {
                return '<a href="/code/nace/' . $m[1] . '.' . $m[2] . '">' . $m[0] . '</a>';
            }, $part);
    
            // Match Division (XX)
            $part = preg_replace_callback('/(?<=див\.|вид\.|розділу)\s+\b(\d{2})\b/u', function($m) {
                 return ' <a href="/code/nace/' . $m[1] . '">' . $m[1] . '</a>';
            }, $part);
        }
        return implode('', $parts);
    }
}
