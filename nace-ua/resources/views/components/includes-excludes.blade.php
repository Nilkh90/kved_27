@props([
    'includes' => [],
    'excludes' => [],
    'includesAlso' => [],
])

@php
    $includesList = is_string($includes) ? array_filter(preg_split('/\r\n|\r|\n/', $includes)) : (array) $includes;
    $excludesList = is_string($excludes) ? array_filter(preg_split('/\r\n|\r|\n/', $excludes)) : (array) $excludes;
    $includesAlsoList = is_string($includesAlso) ? array_filter(preg_split('/\r\n|\r|\n/', $includesAlso)) : (array) $includesAlso;
@endphp

<section class="grid gap-6 md:grid-cols-2 mt-6">
    <div>
        <h3 class="text-sm font-semibold text-emerald-700 uppercase tracking-wide mb-2">Включає</h3>

        @if (count($includesList))
            <ul class="space-y-1 text-sm text-slate-800 rich-text">
                @foreach ($includesList as $item)
                    <li class="flex gap-2">
                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span>{!! $item !!}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-slate-500">Немає спеціальних включень.</p>
        @endif

        @if (count($includesAlsoList))
            <div class="mt-6">
                <h3 class="text-sm font-semibold text-emerald-600 uppercase tracking-wide mb-2 opacity-80">Також включає</h3>
                <ul class="space-y-1 text-sm text-slate-800 rich-text">
                    @foreach ($includesAlsoList as $item)
                        <li class="flex gap-2">
                            <span class="mt-1 h-1.5 w-1.5 rounded-full border border-emerald-500 bg-transparent"></span>
                            <span>{!! $item !!}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div>
        <h3 class="text-sm font-semibold text-rose-700 uppercase tracking-wide mb-2">Не включає</h3>

        @if (count($excludesList))
            <ul class="space-y-1 text-sm text-slate-800 rich-text">
                @foreach ($excludesList as $item)
                    <li class="flex gap-2">
                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                        <span>{!! $item !!}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-slate-500">Немає спеціальних виключень.</p>
        @endif
    </div>
</section>


