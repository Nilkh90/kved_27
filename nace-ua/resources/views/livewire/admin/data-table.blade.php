<div class="bg-white rounded-xl p-6 shadow-sm border border-[--color-border]">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-[--color-text]">Записи: {{ $modelName }}</h3>
        <div class="w-1/3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Пошук..." 
                class="w-full px-4 py-2 bg-[--color-surface] border border-[--color-border] rounded-lg focus:outline-none focus:ring-2 focus:ring-[--color-primary] focus:border-transparent text-[--color-text]">
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-[--color-border]">
        <table class="w-full text-left text-sm text-[--color-text]">
            <thead class="text-xs text-[--color-text-muted] uppercase bg-[--color-surface] border-b border-[--color-border]">
                <tr>
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('id')">
                        ID @if($sortField === 'id') {!! $sortAsc === 'asc' ? '&#8593;' : '&#8595;' !!} @endif
                    </th>
                    @foreach($columns as $field => $label)
                        <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('{{ $field }}')">
                            {{ $label }} @if($sortField === $field) {!! $sortAsc === 'asc' ? '&#8593;' : '&#8595;' !!} @endif
                        </th>
                    @endforeach
                    <th scope="col" class="px-6 py-3 text-right">Дії</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr class="bg-white border-b border-[--color-border] hover:bg-[--color-surface]">
                        <td class="px-6 py-4">{{ $record->id }}</td>
                        
                        @foreach($columns as $field => $label)
                            <td class="px-6 py-4">
                                @if($editingId === $record->id && array_key_exists($field, $editingData))
                                    <input type="text" wire:model="editingData.{{ $field }}" 
                                        class="w-full px-2 py-1 bg-white border border-[--color-primary] rounded text-sm focus:outline-none focus:ring-1 focus:ring-[--color-primary]">
                                @else
                                    {{ Str::limit($record->$field, 50) }}
                                @endif
                            </td>
                        @endforeach
                        
                        <td class="px-6 py-4 text-right space-x-2">
                            @if($editingId === $record->id)
                                <button wire:click="saveRow" class="text-[--color-success] hover:underline font-medium">Зберегти</button>
                                <button wire:click="cancelEdit" class="text-[--color-text-muted] hover:underline font-medium">Скасувати</button>
                            @else
                                <!-- Check if it is an editable field in our array -->
                                @php
                                    $isEditableField = ($modelName === 'Kved2010' || $modelName === 'Nace2027') || ($modelName === 'TransitionMapping');
                                @endphp
                                @if($isEditableField)
                                    <button wire:click="editRow({{ $record->id }})" class="text-[--color-primary] hover:underline font-medium">Редагувати</button>
                                @endif
                                <button wire:click="deleteRow({{ $record->id }})" wire:confirm="Ви впевнені, що хочете видалити цей запис?" class="text-[--color-error] hover:underline font-medium">Видалити</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + 2 }}" class="px-6 py-8 text-center text-[--color-text-muted]">
                            Записів не знайдено.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $records->links() }}
    </div>
</div>
