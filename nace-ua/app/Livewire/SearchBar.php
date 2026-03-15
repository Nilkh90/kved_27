<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class SearchBar extends Component
{
    #[Url]
    public string $query = '';

    public array $results = [];

    #[On('search-updated')]
    public function updatedQuery(): void
    {
        $this->dispatch('search-updated', query: $this->query);
    }

    public function render()
    {
        return view('livewire.search-bar');
    }
}

