@extends('layouts.app')

@php
    $title = $item->code . ' ' . $item->title . ' | kved2027';
    $description = 'Каталог класифікатора NACE 2.1-UA. ' . $item->title;
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
        <div class="lg:col-span-12 mb-8">
            <div class="bg-white rounded-3xl border p-8 shadow-sm" style="border-color:#E2E8F2">
                <span class="inline-block text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4"
                      style="background:#EEF4FF; color:#1A5FBE">
                    {{ strtoupper($level) === 'SECTION' ? 'Секція' : (strtoupper($level) === 'DIVISION' ? 'Розділ' : (strtoupper($level) === 'GROUP' ? 'Група' : $level)) }}
                </span>
                
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                    <div class="max-w-3xl">
                        <h1 class="text-3xl md:text-4xl font-bold mb-4" style="color:#0F1923">
                            <span class="font-mono text-blue-600">{{ $item->code }}</span> — {{ $item->title }}
                        </h1>
                        
                        @if($item->description)
                            <div class="mt-6 p-6 rounded-2xl bg-slate-50 border border-slate-100">
                                <h2 class="text-xs font-bold uppercase tracking-wider mb-2 text-slate-400">Опис категорії</h2>
                                <p class="text-lg leading-relaxed text-slate-600 italic">
                                    {{ $item->description }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="hidden md:block">
                        <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-all hover:bg-slate-50" style="border-color:#E2E8F2; color:#5A6A7F">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Всього каталогу
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Children List --}}
        <div class="lg:col-span-12">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-bold" style="color:#0F1923">
                    @if($childLevel === 'division') Доступні Розділи (Divisions)
                    @elseif($childLevel === 'group') Доступні Групи (Groups)
                    @elseif($childLevel === 'class') Доступні Класи (Classes)
                    @endif
                </h2>
                <span class="text-sm font-medium px-4 py-1.5 rounded-full bg-slate-100 text-slate-600">
                    {{ $children->count() }} елементів
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($children as $child)
                    <a href="{{ route('catalog.' . strtolower($child->level), $child->id) }}" 
                       class="group block p-6 rounded-2xl border transition-all hover:shadow-lg hover:border-blue-300 hover:scale-[1.01]"
                       style="background:#FFFFFF; border-color:#E2E8F2">
                        <div class="flex items-start gap-5">
                            <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center font-mono font-bold text-xl transition-all group-hover:bg-blue-600 group-hover:text-white group-hover:rotate-3 shadow-sm"
                                 style="background:#F0F7FF; color:#1A5FBE">
                                {{ $child->code }}
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold mb-2 group-hover:text-blue-700 transition-colors leading-snug" style="color:#0F1923">
                                    {{ $child->title }}
                                </h3>
                                @if($child->description)
                                    <p class="text-sm line-clamp-2 text-slate-500 leading-relaxed">
                                        {{ Str::limit($child->description, 130) }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex-shrink-0 self-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all group-hover:bg-blue-50 group-hover:translate-x-1" style="color:#94A3B8">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-16 px-6 rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50/50">
                        <p class="text-slate-400 font-medium">Дані відсутні для цього рівня.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
