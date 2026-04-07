@extends('layouts.app')

@php
    $isNace = $standard === 'nace';
    $displayName = $isNace ? 'NACE 2.1-UA (2027)' : 'КВЕД-2010';
    $title = 'Каталог класифікатора ' . $displayName . ' | kved.biz.ua';
    $description = 'Повний каталог классифікатора ' . $displayName . '. Порівняння стандартів, пошук та ієрархія.';
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Page Header --}}
    <div class="mb-12">
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <nav class="flex items-center gap-2 text-sm" style="color:#94A3B8">
                <a href="{{ route('home') }}" class="hover:underline" style="color:#5A6A7F">Головна</a>
                <span>/</span>
                <span style="color:#0F1923; font-weight:500">Каталог</span>
            </nav>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $isNace ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                {{ $displayName }}
            </span>
        </div>

        <h1 class="text-4xl font-extrabold tracking-tight" style="color:#0F1923">
            Каталог <span class="{{ $isNace ? 'text-emerald-600' : 'text-blue-600' }}">{{ $displayName }}</span>
        </h1>
        
        @if($isNace)
            <div class="mt-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex gap-4 items-start">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex-shrink-0 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-emerald-900">Новий стандарт NACE 2.1-UA</h4>
                    <p class="text-sm text-emerald-700 leading-relaxed">
                        Ви переглядаєте перспективний класифікатор, який впроваджується з 2027 року. 
                        Для поточної звітності використовуйте <a href="{{ route('catalog.index', ['standard' => 'kved']) }}" class="font-bold underline">КВЕД-2010</a>.
                    </p>
                </div>
            </div>
        @else
            <p class="mt-4 text-lg max-w-2xl" style="color:#5A6A7F">
                Офіційний діючий класифікатор видів економічної діяльності.
            </p>
        @endif
    </div>

    {{-- Sections Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($sections as $section)
            <a href="{{ route('catalog.section', ['standard' => $standard, 'code' => strtolower($section->code)]) }}" 
               class="group relative p-8 rounded-3xl border transition-all hover:shadow-xl {{ $isNace ? 'hover:border-emerald-300' : 'hover:border-blue-300' }} overflow-hidden"
               style="background:#FFFFFF; border-color:#E2E8F2">
                
                {{-- Decorative background code --}}
                <div class="absolute -top-4 -right-4 text-7xl font-mono font-black opacity-[0.03] transition-opacity group-hover:opacity-[0.05]" style="color:#1A5FBE">
                    {{ $section->code }}
                </div>

                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-mono font-bold text-xl mb-6 transition-colors group-hover:bg-blue-600 group-hover:text-white"
                         style="background:#F0F7FF; color:#1A5FBE">
                        {{ $section->code }}
                    </div>
                    <h3 class="text-xl font-bold mb-3 group-hover:text-blue-700 transition-colors" style="color:#0F1923">
                        {{ $section->title }}
                    </h3>
                    @if($section->description)
                        <p class="text-sm leading-relaxed line-clamp-3" style="color:#5A6A7F">
                            {{ strip_tags($section->description) }}
                        </p>
                    @endif
                    
                    <div class="mt-6 flex items-center gap-2 text-sm font-bold transition-colors group-hover:text-blue-700" style="color:#1A5FBE">
                        Переглянути розділи
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-20 px-6 rounded-3xl border-2 border-dashed" style="border-color:#E2E8F2">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Каталог порожній</h3>
                <p class="text-slate-500 max-w-sm mx-auto">
                    Зараз триває імпорт даних. Будь ласка, завітайте пізніше або скористайтесь пошуком.
                </p>
                <div class="mt-8">
                    @livewire('search-bar')
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
