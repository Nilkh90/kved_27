@props([
    'code',
    'title',
    'standard' => 'kved',
    'href'     => null,
    'excerpt'  => null,
    'status'   => null,         // transition type string, e.g. '1_TO_1'
    'actionRequired' => false,
])

@php
    $url = $href ?? route('code.show', [$standard, $code]);
    $standardLabel = strtoupper($standard === 'nace' ? 'NACE 2027' : 'КВЕД 2010');
    $standardColor = $standard === 'nace' ? '#0284C7' : '#5A6A7F';
@endphp

<a href="{{ $url }}" {{ $attributes->merge(['class' => 'group block rounded-2xl border bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-blue-200']) }}
   style="border-color: #E2E8F2">
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            {{-- Standard label --}}
            <span class="inline-block text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded mb-2"
                  style="background-color:#EEF4FF; color:{{ $standardColor }}">
                {{ $standardLabel }}
            </span>

            {{-- Code (monospaced) --}}
            <div class="text-lg font-mono font-bold text-slate-900 group-hover:text-blue-700 transition-colors">
                {{ $code }}
            </div>

            {{-- Title --}}
            <p class="mt-1 text-sm text-slate-600 line-clamp-2">
                {{ $title }}
            </p>

            @if ($excerpt)
                <p class="mt-2 text-xs text-slate-400 line-clamp-2">
                    {{ $excerpt }}
                </p>
            @endif
        </div>

        {{-- Arrow icon --}}
        <div class="flex-shrink-0 text-slate-300 group-hover:text-blue-500 transition-colors mt-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </div>

    @if ($status)
        <div class="mt-3 pt-3 border-t" style="border-color:#E2E8F2">
            <x-status-badge :type="$status" :action-required="$actionRequired" size="sm" />
        </div>
    @endif
</a>
