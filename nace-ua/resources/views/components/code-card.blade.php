@props([
    'code',
    'title',
    'standard' => 'kved',
    'href' => null,
    'excerpt' => null,
])

@php
    $url = $href ?? route('code.show', [$standard, $code]);
@endphp

<article {{ $attributes->merge(['class' => 'group rounded-xl border border-slate-200 bg-white/80 p-4 shadow-sm shadow-slate-100 transition hover:border-emerald-300 hover:shadow-md']) }}>
    <header class="flex items-baseline justify-between gap-3">
        <div>
            <a href="{{ $url }}" class="inline-flex items-center gap-2 text-sm font-mono text-slate-900 group-hover:text-emerald-700">
                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-xs font-semibold text-slate-700">
                    {{ strtoupper($standard) }}
                </span>
                <span>{{ $code }}</span>
            </a>
            <p class="mt-1 text-sm font-medium text-slate-900">
                {{ $title }}
            </p>
        </div>
    </header>

    @if ($excerpt)
        <p class="mt-2 line-clamp-2 text-sm text-slate-600">
            {{ $excerpt }}
        </p>
    @endif
</article>


