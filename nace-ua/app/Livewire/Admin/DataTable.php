<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;

class DataTable extends Component
{
    use WithPagination;

    public string $modelName;
    public string $search = '';
    public string $sortField = 'id';
    public string $sortAsc = 'desc';

    // Edit state
    public $editingId = null;
    public array $editingData = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortAsc' => ['except' => 'desc'],
    ];

    public function mount(string $modelName)
    {
        $this->modelName = $modelName;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortAsc = $this->sortAsc === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortAsc = 'asc';
        }
    }

    public function editRow($id)
    {
        $this->editingId = $id;
        $modelClass = "App\\Models\\{$this->modelName}";
        $record = $modelClass::find($id);
        
        // Define which fields are editable based on the model
        if ($this->modelName === 'Kved2010' || $this->modelName === 'Nace2027') {
            $this->editingData = ['title' => $record->title];
        } elseif ($this->modelName === 'TransitionMapping') {
            $this->editingData = ['comment' => $record->comment];
        }
    }

    public function saveRow()
    {
        if (!$this->editingId) return;

        $modelClass = "App\\Models\\{$this->modelName}";
        $record = $modelClass::find($this->editingId);

        if ($record) {
            $record->update($this->editingData);
        }

        $this->editingId = null;
        $this->editingData = [];
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->editingData = [];
    }

    public function deleteRow($id)
    {
        $modelClass = "App\\Models\\{$this->modelName}";
        $modelClass::destroy($id);
    }

    public function render()
    {
        $modelClass = "App\\Models\\{$this->modelName}";
        
        $query = $modelClass::query();

        // Very basic simple search depending on model
        if (!empty($this->search)) {
            if ($this->modelName === 'Kved2010' || $this->modelName === 'Nace2027') {
                $query->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('title', 'like', '%' . $this->search . '%');
            } elseif ($this->modelName === 'TransitionMapping') {
                $query->where('kved_code', 'like', '%' . $this->search . '%')
                      ->orWhere('nace_code', 'like', '%' . $this->search . '%')
                      ->orWhere('comment', 'like', '%' . $this->search . '%');
            }
        }

        $records = $query->orderBy($this->sortField, $this->sortAsc)
                         ->paginate(15);

        // Define columns to display based on model
        $columns = [];
        if ($this->modelName === 'Kved2010') {
            $columns = ['code' => 'Код КВЕД', 'title' => 'Назва (КВЕД)'];
        } elseif ($this->modelName === 'Nace2027') {
            $columns = ['code' => 'Код NACE', 'title' => 'Назва (NACE)', 'level' => 'Рівень'];
        } elseif ($this->modelName === 'TransitionMapping') {
            $columns = [
                'kved_code' => 'Код КВЕД', 
                'nace_code' => 'Код NACE', 
                'mapping_type' => 'Тип зв\'язку', 
                'comment' => 'Коментар'
            ];
        }

        return view('livewire.admin.data-table', [
            'records' => $records,
            'columns' => $columns,
        ]);
    }
}
