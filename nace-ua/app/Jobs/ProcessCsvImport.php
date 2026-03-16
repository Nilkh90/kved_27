<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCsvImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $filePath,
        public string $type,
        public string $mode = 'upsert'
    ) {}

    public function handle(\App\Services\ImportService $service): void
    {
        $csv = \League\Csv\Reader::createFromPath($this->filePath, 'r');
        $csv->setHeaderOffset(0);
        $records = iterator_to_array($csv->getRecords());

        $service->processRows($records, $this->type, $this->mode);

        \Spatie\ResponseCache\Facades\ResponseCache::clear();
    }
}

