<div class="relative" x-data="{ open: false }" @click.away="open = false">
    {{-- Search Input --}}
    <div class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#94A3B8">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
        </div>

        <input
            type="search"
            wire:model.live.debounce.150ms="query"
            @focus="open = true"
            placeholder="Введіть код або опис діяльності..."
            autocomplete="off"
            class="w-full pl-12 pr-4 py-4 text-base rounded-2xl border-2 outline-none transition-all font-sans"
            style="border-color:#E2E8F2; color:#0F1923; background:#FFFFFF"
            x-on:focus="$el.style.borderColor='#1A5FBE'; $el.style.boxShadow='0 0 0 4px rgba(26,95,190,0.12)'"
            x-on:blur="$el.style.borderColor='#E2E8F2'; $el.style.boxShadow='none'"
        >
    </div>

    {{-- Results Dropdown --}}
    @if ($results && count($results) > 0)
        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="absolute z-20 mt-2 w-full rounded-2xl border shadow-xl overflow-hidden"
             style="background:#FFFFFF; border-color:#E2E8F2; box-shadow: 0 8px 30px rgba(15,25,35,0.12)">

            <ul class="py-2 max-h-96 overflow-y-auto divide-y" style="divide-color:#F1F5F9">
                @foreach ($results as $item)
                    <li>
                        <a href="{{ route('code.show', [$item['standard'] ?? 'kved', $item['code'] ?? '']) }}"
                           class="flex items-center gap-4 px-4 py-3 hover:bg-blue-50 transition-colors"
                           @click="open = false">

                            @php
                                $itemIsNace = ($item['standard'] ?? 'kved') === 'nace';
                            @endphp
                            {{-- Code badge --}}
                            <span class="font-mono text-sm font-bold px-2 py-1 rounded-lg flex-shrink-0"
                                  style="background:{{ $itemIsNace ? '#ECFDF5' : '#EEF4FF' }}; color:{{ $itemIsNace ? '#059669' : '#1A5FBE' }}">
                                {{ $item['code'] ?? '' }}
                            </span>

                            {{-- Title --}}
                            <span class="text-sm flex-1 min-w-0 truncate" style="color:#0F1923">
                                {{ $item['title'] ?? '' }}
                            </span>

                            {{-- Standard tag --}}
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded flex-shrink-0"
                                  style="background:{{ $itemIsNace ? '#D1FAE5' : '#F1F5F9' }}; color:{{ $itemIsNace ? '#065F46' : '#5A6A7F' }}">
                                {{ strtoupper($item['standard'] ?? 'KVED') }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="px-4 py-3 border-t flex items-center justify-between" style="background:#F8F9FC; border-color:#E2E8F2">
                <span class="text-xs" style="color:#94A3B8">{{ count($results) }} результатів</span>
                <button type="button" class="text-xs font-medium" style="color:#1A5FBE"
                        @click="open = false">Закрити</button>
            </div>
        </div>
    @elseif ($query && strlen($query) >= 2)
        <div x-show="open"
             class="absolute z-20 mt-2 w-full rounded-2xl border shadow-xl px-6 py-8 text-center"
             style="background:#FFFFFF; border-color:#E2E8F2">
            <p class="text-sm" style="color:#94A3B8">
                Нічого не знайдено для «{{ $query }}» — спробуйте інший запит.
            </p>
        </div>
    @endif
</div>
