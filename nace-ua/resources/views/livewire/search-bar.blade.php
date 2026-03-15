<div>
    <input
        type="search"
        wire:model.debounce.300ms="query"
        wire:input="updateResults($event.target.value)"
        placeholder="Введіть код або опис діяльності..."
    >

    @if ($results)
        <ul>
            @foreach ($results as $item)
                <li>
                    <a href="{{ route('code.show', [$item['standard'] ?? 'kved', $item['code'] ?? '']) }}">
                        {{ $item['code'] ?? '' }} — {{ $item['title'] ?? '' }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>

