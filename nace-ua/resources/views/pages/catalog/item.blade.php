@extends('layouts.app')

@php
    $title = $item->code . ' ' . $item->title . ' | kved2027';
    $description = 'Каталог класифікатора NACE 2.1-UA. ' . $item->title;
    
    // Build breadcrumbs
    $breadcrumbs = [];
    $current = $item;
    while ($current) {
        $breadcrumbs[] = [
            'title' => $current->code . ($current->level === 'SECTION' ? '' : ''),
            'route' => route('catalog.' . strtolower($current->level), $current->id),
            'active' => $current->id === $item->id
        ];
        $current = $current->parent;
    }
    $breadcrumbs = array_reverse($breadcrumbs);
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-2 text-sm mb-8" style="color:#94A3B8">
        <a href="{{ route('home') }}" class="hover:underline transition-colors" style="color:#5A6A7F">Головна</a>
        <span>/</span>
        <a href="{{ route('catalog') }}" class="hover:underline transition-colors" style="color:#5A6A7F">Каталог</a>
        @foreach($breadcrumbs as $bc)
            <span>/</span>
            @if($bc['active'])
                <span style="color:#0F1923; font-weight:600">{{ $bc['title'] }}</span>
            @else
                <a href="{{ $bc['route'] }}" class="hover:underline transition-colors" style="color:#5A6A7F">{{ $bc['title'] }}</a>
            @endif
        @endforeach
    </nav>

    <div class="lg:grid lg:grid-cols-12 gap-12">
        {{-- Left: Current Item Info --}}
        <div class="lg:col-span-4">
            <div class="sticky top-24">
                <span class="inline-block text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4"
                      style="background:#EEF4FF; color:#1A5FBE">
                    {{ strtoupper($level) }}
                </span>
                <h1 class="text-3xl font-bold mb-4" style="color:#0F1923">
                    <span class="font-mono">{{ $item->code }}</span><br>
                    {{ $item->title }}
                </h1>
                
                @if($item->description)
                    <div class="p-6 rounded-2xl border mb-6" style="border-color:#E2E8F2; background:#FFFFFF">
                        <h2 class="text-sm font-bold uppercase tracking-wider mb-3 text-slate-400">Опис</h2>
                        <p class="text-base leading-relaxed" style="color:#5A6A7F">
                            {{ $item->description }}
                        </p>
                    </div>
                @endif

                <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 text-sm font-medium hover:underline" style="color:#5A6A7F">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Повернутись до початку
                </a>
            </div>
        </div>

        {{-- Right: Children List --}}
        <div class="lg:col-span-8 mt-12 lg:mt-0">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-bold" style="color:#0F1923">
                    @if($childLevel === 'division') Раздели (Divisions)
                    @elseif($childLevel === 'group') Группы (Groups)
                    @elseif($childLevel === 'class') Классы (Classes)
                    @endif
                </h2>
                <span class="text-sm px-3 py-1 rounded-lg border text-slate-500" style="border-color:#E2E8F2">
                    {{ $children->count() }} елементів
                </span>
            </div>

            <div class="grid gap-4">
                @forelse($children as $child)
                    <a href="{{ route('catalog.' . $childLevel, $child->id) }}" 
                       class="group block p-6 rounded-2xl border transition-all hover:shadow-md hover:border-blue-200"
                       style="background:#FFFFFF; border-color:#E2E8F2">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center font-mono font-bold text-lg transition-colors group-hover:bg-blue-600 group-hover:text-white"
                                 style="background:#F8FAFC; color:#1A5FBE">
                                {{ $child->code }}
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold mb-1 group-hover:text-blue-700 transition-colors" style="color:#0F1923">
                                    {{ $child->title }}
                                </h3>
                                @if($child->description)
                                    <p class="text-sm line-clamp-2" style="color:#5A6A7F">
                                        {{ Str::limit($child->description, 120) }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex-shrink-0 self-center">
                                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#94A3B8">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-12 px-6 rounded-3xl border border-dashed" style="border-color:#E2E8F2">
                        <p style="color:#94A3B8">Дані відсутні для цього рівня.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
