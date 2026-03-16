<?php

namespace App\Livewire;

use App\Models\TransitionMapping;
use Livewire\Component;

class PopularChanges extends Component
{
    public array $items = [];

    public function mount(): void
    {
        $this->items = TransitionMapping::orderByDesc('view_count')
            ->limit(10)
            ->get()
            ->map(function (TransitionMapping $mapping): array {
                return [
                    'id' => $mapping->id,
                    'transition_type' => $mapping->transition_type,
                    'action_required' => (bool) $mapping->action_required,
                    'view_count' => $mapping->view_count,
                    'comment' => $mapping->transition_comment,
                ];
            })
            ->all();
    }

    public function render()
    {
        return view('livewire.popular-changes');
    }
}

