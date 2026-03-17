<?php

namespace App\Console\Commands;

use App\Models\Kved2010;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportKved2010 extends Command
{
    protected $signature = 'kved:import {--fresh : Truncate all existing data before import}';

    protected $description = 'Import the full 4-level KVED-2010 hierarchy from the official Ukrstat website';

    private const BASE_URL = 'https://kved.ukrstat.gov.ua';

    // All 21 official KVED-2010 sections
    private const SECTIONS = [
        'A' => 'Сільське господарство, лісове господарство та рибне господарство',
        'B' => 'Добувна промисловість і розроблення кар\'єрів',
        'C' => 'Переробна промисловість',
        'D' => 'Постачання електроенергії, газу, пари та кондиційованого повітря',
        'E' => 'Водопостачання; каналізація, поводження з відходами',
        'F' => 'Будівництво',
        'G' => 'Оптова та роздрібна торгівля; ремонт автотранспортних засобів і мотоциклів',
        'H' => 'Транспорт, складське господарство, поштова та кур\'єрська діяльність',
        'I' => 'Тимчасове розміщування й організація харчування',
        'J' => 'Інформація та телекомунікації',
        'K' => 'Фінансова та страхова діяльність',
        'L' => 'Операції з нерухомим майном',
        'M' => 'Професійна, наукова та технічна діяльність',
        'N' => 'Діяльність у сфері адміністративного та допоміжного обслуговування',
        'O' => 'Державне управління й оборона; обов\'язкове соціальне страхування',
        'P' => 'Освіта',
        'Q' => 'Охорона здоров\'я та надання соціальної допомоги',
        'R' => 'Мистецтво, спорт, розваги та відпочинок',
        'S' => 'Надання інших видів послуг',
        'T' => 'Діяльність домашніх господарств як роботодавців; недиференційована діяльність домашніх господарств',
        'U' => 'Діяльність екстериторіальних організацій і органів',
    ];

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->warn('Truncating existing kved_2010 data...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            Kved2010::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('Truncated.');
        }

        $this->info('Starting KVED-2010 import from Ukrstat...');
        $this->newLine();

        // Step 1: Create all Sections
        $this->info('[1/4] Creating Sections...');
        $sectionModels = $this->importSections();
        $this->info('  ✓ ' . count($sectionModels) . ' sections created.');
        $this->newLine();

        // Step 2: For each Section, fetch its Divisions
        $this->info('[2/4] Fetching Divisions for each Section...');
        $divisionModels = [];
        foreach ($sectionModels as $letter => $sectionModel) {
            $divisions = $this->importDivisionsForSection($letter, $sectionModel->id);
            $divisionModels = array_merge($divisionModels, $divisions);
            $this->line("  Section $letter → " . count($divisions) . ' divisions');
        }
        $this->info('  ✓ ' . count($divisionModels) . ' divisions total.');
        $this->newLine();

        // Step 3: For each Division, fetch its Groups
        $this->info('[3/4] Fetching Groups for each Division...');
        $groupModels = [];
        foreach ($divisionModels as $divCode => $divisionModel) {
            $groups = $this->importGroupsForDivision($divCode, $divisionModel->id);
            $groupModels = array_merge($groupModels, $groups);
        }
        $this->info('  ✓ ' . count($groupModels) . ' groups total.');
        $this->newLine();

        // Step 4: For each Group, fetch its Classes
        $this->info('[4/4] Fetching Classes for each Group...');
        $classCount = 0;
        foreach ($groupModels as $groupCode => $groupModel) {
            $classes = $this->importClassesForGroup($groupCode, $groupModel->id);
            $classCount += count($classes);
        }
        $this->info("  ✓ $classCount classes total.");
        $this->newLine();

        $this->info('✅ KVED-2010 import completed successfully!');
        return Command::SUCCESS;
    }

    /**
     * Create Section records directly from our known list.
     */
    private function importSections(): array
    {
        $models = [];
        foreach (self::SECTIONS as $letter => $title) {
            // Try to fetch description from section detail page
            $description = $this->fetchSectionDescription($letter);

            $models[$letter] = Kved2010::updateOrCreate(
                ['code' => $letter],
                [
                    'title' => $title,
                    'level' => 'SECTION',
                    'parent_id' => null,
                    'description' => $description,
                ]
            );
        }
        return $models;
    }

    /**
     * Fetch Divisions listed on a Section page.
     */
    private function importDivisionsForSection(string $letter, int $sectionId): array
    {
        $url = self::BASE_URL . "/KVED2010/SECT/KVED10_{$letter}.html";
        $rows = $this->fetchListRows($url);
        $models = [];

        foreach ($rows as $row) {
            $code = $row['code'];
            $title = $row['title'];

            // Validate it looks like a 2-digit division
            if (!preg_match('/^\d{2}$/', $code)) {
                continue;
            }

            $description = $this->fetchNodeDescription($row['href']);

            $models[$code] = Kved2010::updateOrCreate(
                ['code' => $code],
                [
                    'title' => $title,
                    'level' => 'DIVISION',
                    'parent_id' => $sectionId,
                    'description' => $description,
                ]
            );
        }

        return $models;
    }

    /**
     * Fetch Groups listed on a Division page.
     */
    private function importGroupsForDivision(string $divCode, int $divisionId): array
    {
        $divNum = (int) $divCode;
        $url = self::BASE_URL . "/KVED2010/{$divNum}/KVED10_{$divCode}.html";
        $rows = $this->fetchListRows($url);
        $models = [];

        foreach ($rows as $row) {
            $code = $row['code'];
            $title = $row['title'];

            // Validate it looks like a group: "62.0", "01.1" etc.
            if (!preg_match('/^\d{2}\.\d$/', $code)) {
                continue;
            }

            $description = $this->fetchNodeDescription($row['href']);

            $models[$code] = Kved2010::updateOrCreate(
                ['code' => $code],
                [
                    'title' => $title,
                    'level' => 'GROUP',
                    'parent_id' => $divisionId,
                    'description' => $description,
                ]
            );
        }

        return $models;
    }

    /**
     * Fetch Classes listed on a Group page.
     */
    private function importClassesForGroup(string $groupCode, int $groupId): array
    {
        $divCode = explode('.', $groupCode)[0];
        $groupSuffix = str_replace('.', '_', $groupCode);
        $url = self::BASE_URL . "/KVED2010/{$divCode}/KVED10_{$groupSuffix}.html";
        $rows = $this->fetchListRows($url);
        $models = [];

        foreach ($rows as $row) {
            $code = $row['code'];
            $title = $row['title'];

            // Validate it looks like a class: "62.01", "01.11" etc.
            if (!preg_match('/^\d{2}\.\d{2}$/', $code)) {
                continue;
            }

            $description = $this->fetchNodeDescription($row['href']);

            $models[$code] = Kved2010::updateOrCreate(
                ['code' => $code],
                [
                    'title' => $title,
                    'level' => 'CLASS',
                    'parent_id' => $groupId,
                    'description' => $description,
                ]
            );
        }

        return $models;
    }

    /**
     * Parse all <tr class="List_Row"> from a given Ukrstat URL.
     * Returns array of ['code' => ..., 'title' => ..., 'href' => ...].
     */
    private function fetchListRows(string $url): array
    {
        sleep(1); // Polite delay to avoid hammering the server
        try {
            $response = Http::timeout(15)->get($url);
            if (!$response->successful()) {
                $this->warn("  Could not fetch: $url (HTTP {$response->status()})");
                return [];
            }
            $html = $response->body();
        } catch (\Exception $e) {
            $this->warn("  Error fetching $url: " . $e->getMessage());
            return [];
        }

        return $this->parseListRows($html, $url);
    }

    /**
     * Parse the rows from raw HTML content.
     */
    private function parseListRows(string $html, string $baseUrl = ''): array
    {
        $results = [];

        // Find all List_Row table rows
        if (!preg_match_all('/<tr[^>]+class="List_Row"[^>]*>(.*?)<\/tr>/si', $html, $matches)) {
            return [];
        }

        foreach ($matches[1] as $rowHtml) {
            // Extract the code from the link text
            if (!preg_match('/<a[^>]+href="([^"]+)"[^>]*>([^<]+)<\/a>/si', $rowHtml, $linkMatch)) {
                continue;
            }
            $href = $linkMatch[1];
            $code = trim($linkMatch[2]);

            // Extract title from the <p> tag
            if (!preg_match('/<p[^>]*>(.*?)<\/p>/si', $rowHtml, $titleMatch)) {
                continue;
            }
            $title = trim(strip_tags($titleMatch[1]));

            if (empty($code) || empty($title)) {
                continue;
            }

            $results[] = [
                'code' => $code,
                'title' => $this->cleanText($title),
                'href' => $href,
            ];
        }

        return $results;
    }

    /**
     * Fetch the text description from a node's detail page.
     * Description is in the "Цей клас включає:" / "Цей вид включає:" block.
     */
    private function fetchNodeDescription(string $href): ?string
    {
        sleep(1);
        $url = self::BASE_URL . $href;

        try {
            $response = Http::timeout(15)->get($url);
            if (!$response->successful()) {
                return null;
            }
            $html = $response->body();
        } catch (\Exception $e) {
            return null;
        }

        return $this->parseDescription($html);
    }

    /**
     * Fetch description specifically from a Section page.
     */
    private function fetchSectionDescription(string $letter): ?string
    {
        $url = self::BASE_URL . "/KVED2010/SECT/KVED10_{$letter}.html";
        sleep(1);

        try {
            $response = Http::timeout(15)->get($url);
            if (!$response->successful()) {
                return null;
            }
            return $this->parseDescription($response->body());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract the "includes" description text from a detail page.
     */
    private function parseDescription(string $html): ?string
    {
        // Look for the main content text block — usually a <td> with class "Info_DR" or similar
        // or the main description table with class "SD_Text"
        if (preg_match('/<td[^>]+class="[^"]*Info_DR[^"]*"[^>]*>(.*?)<\/td>/si', $html, $match)) {
            $text = strip_tags($match[1]);
            $text = $this->cleanText($text);
            return $text ?: null;
        }

        // Fallback: try to grab first substantial paragraph in the content area
        if (preg_match('/<td[^>]+class="[^"]*SD_Text[^"]*"[^>]*>(.*?)<\/td>/si', $html, $match)) {
            $text = strip_tags($match[1]);
            $text = $this->cleanText($text);
            return $text ?: null;
        }

        return null;
    }

    /**
     * Clean up text extracted from HTML.
     */
    private function cleanText(string $text): string
    {
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        return $text;
    }
}
