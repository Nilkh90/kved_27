<?php

namespace App\Services;

class ImportService
{
    /**
     * @param string $filePath
     * @param string $type  'kved_2010', 'nace_2027', 'transition_mapping'
     * @param string $mode  'upsert', 'replace'
     */
    public function importCsv(string $filePath, string $type, string $mode = 'upsert')
    {
        $csv = \League\Csv\Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        $records = iterator_to_array($csv->getRecords());
        
        $count = count($records);

        if ($count > 1000) {
            \App\Jobs\ProcessCsvImport::dispatch($filePath, $type, $mode);
            return ['status' => 'queued', 'message' => "Файл поставлено в чергу ($count записів)."];
        }

        // Process synchronously
        $this->processRows($records, $type, $mode);
        
        // Clear cache
        \Spatie\ResponseCache\Facades\ResponseCache::clear();

        return ['status' => 'success', 'message' => "Успішно імпортовано $count записів."];
    }

    public function processRows(array $records, string $type, string $mode)
    {
        if ($mode === 'replace') {
            \Illuminate\Support\Facades\DB::table($type)->truncate();
        }

        foreach ($records as $row) {
            if ($type === 'transition_mapping') {
                $kved = \Illuminate\Support\Facades\DB::table('kved_2010')->where('code', $row['old_kved_code'] ?? '')->first();
                $nace = \Illuminate\Support\Facades\DB::table('nace_2027')->where('code', $row['new_nace_code'] ?? '')->first();
                
                if (is_object($kved) && is_object($nace)) {
                    \Illuminate\Support\Facades\DB::table('transition_mapping')->updateOrInsert(
                        ['old_kved_id' => $kved->id, 'new_nace_id' => $nace->id],
                        [
                            'transition_type' => $row['transition_type'] ?? '1_TO_1',
                            'action_required' => filter_var($row['action_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'transition_comment' => $row['transition_comment'] ?? null,
                        ]
                    );
                }
            } else {
                // For KVED / NACE tables
                \Illuminate\Support\Facades\DB::table($type)->updateOrInsert(
                    ['code' => $row['code']],
                    [
                        'title' => $row['title'],
                        'level' => $row['level'],
                        'description' => $row['description'] ?? null,
                        'includes' => isset($row['includes']) ? json_encode(explode('|', $row['includes'])) : null,
                        'excludes' => isset($row['excludes']) ? json_encode(explode('|', $row['excludes'])) : null,
                    ]
                );
            }
        }
    }
}

