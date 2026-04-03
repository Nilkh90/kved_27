<?php

namespace App\Livewire;

use App\Services\SearchService;
use Livewire\Attributes\Url;
use Livewire\Component;

class SearchBar extends Component
{
    public string $query = '';

    public array $results = [];

    public function mount(): void
    {
        $this->results = [];
    }

    public function updatedQuery(string $value): void
    {
        if (trim($value) === '') {
            $this->results = [];

            return;
        }

        $this->results = app(SearchService::class)->suggest($value, limit: 8);
    }

    public function render()
    {
        return view('livewire.search-bar');
    }
}

