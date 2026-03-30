<?php

namespace App\Console\Commands;

use App\Models\Kved2010;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportKvedFromJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kved:import-json {--truncate : Delete all existing data before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import KVED-2010 data from a local JSON file (full hierarchy)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filename = 'kved_all_sections.json';
        
        $filePath = storage_path('app/' . $filename);
        
        if (!file_exists($filePath)) {
            $this->error("File kved_all_sections.json not found at: $filePath");
            return Command::FAILURE;
        }

        if ($this->option('truncate')) {
            $this->warn('Deleting existing kved_2010 data...');
            Kved2010::query()->delete();
            $this->info('Deleted.');
        }

        $this->info("Loading data from $filePath...");
        $json = file_get_contents($filePath);
        $sections = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("JSON Decoding Error: " . json_last_error_msg());
            return Command::FAILURE;
        }

        $this->info("Starting import of " . count($sections) . " sections...");
        $bar = $this->output->createProgressBar(count($sections));
        $bar->start();

        foreach ($sections as $sectionData) {
            $section = Kved2010::updateOrCreate(
                ['code' => $sectionData['code']],
                [
                    'title'       => $sectionData['title'],
                    'level'       => 'SECTION',
                    'parent_id'   => null,
                    'description' => $sectionData['description'] ?? null,
                ]
            );

            foreach ($sectionData['divisions'] ?? [] as $divData) {
                $division = Kved2010::updateOrCreate(
                    ['code' => $divData['code']],
                    [
                        'title'       => $divData['title'],
                        'level'       => 'DIVISION',
                        'parent_id'   => $section->id,
                        'description' => $divData['description'] ?? null,
                    ]
                );

                foreach ($divData['groups'] ?? [] as $grpData) {
                    $group = Kved2010::updateOrCreate(
                        ['code' => $grpData['code']],
                        [
                            'title'       => $grpData['title'],
                            'level'       => 'GROUP',
                            'parent_id'   => $division->id,
                            'description' => $grpData['description'] ?? null,
                        ]
                    );

                    foreach ($grpData['classes'] ?? [] as $clsData) {
                        Kved2010::updateOrCreate(
                            ['code' => $clsData['code']],
                            [
                                'title'       => $clsData['title'],
                                'level'       => 'CLASS',
                                'parent_id'   => $group->id,
                                'description' => $clsData['description'] ?? null,
                            ]
                        );
                    }
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Success! Total records: " . Kved2010::count());

        return Command::SUCCESS;
    }
}
