<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\ImportService;

class ImportForm extends Component
{
    use WithFileUploads;

    public $file;
    public string $type = 'transition_mapping'; // Default
    public string $mode = 'upsert'; // Default
    public ?string $statusMessage = null;
    public ?string $statusType = null; // 'success' or 'error'

    protected $rules = [
        'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        'type' => 'required|in:kved_2010,nace_2027,transition_mapping',
        'mode' => 'required|in:upsert,replace',
    ];

    public function import(ImportService $importService)
    {
        $this->validate();

        try {
            $filePath = $this->file->getRealPath();
            $result = $importService->importCsv($filePath, $this->type, $this->mode);

            $this->statusType = 'success';
            $this->statusMessage = $result['message'];
            
            // clear form
            $this->reset('file');
            
        } catch (\Exception $e) {
            $this->statusType = 'error';
            $this->statusMessage = 'Помилка імпорту: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.admin.import-form');
    }
}

