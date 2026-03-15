<?php

namespace App\Livewire;

use App\Services\ClassifierService;
use Livewire\Component;

class ClassifierTree extends Component
{
    public string $standard = 'kved';

    public array $expanded = [];

    public array $nodes = [];

    public function mount(): void
    {
        $this->nodes = app(ClassifierService::class)->getRootNodes($this->standard);
    }

    public function toggle(string $id): void
    {
        if (in_array($id, $this->expanded, true)) {
            $this->expanded = array_values(array_diff($this->expanded, [$id]));
        } else {
            $this->expanded[] = $id;
        }
    }

    public function render()
    {
        return view('livewire.classifier-tree');
    }
}

