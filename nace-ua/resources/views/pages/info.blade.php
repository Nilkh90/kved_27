@extends('layouts.app')

@section('title', 'Довідка та FAQ')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumbs -->
    <nav class="flex mb-8 text-sm" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li>
                <a href="{{ route('home') }}" class="text-[--color-text-hint] hover:text-[--color-primary] transition-colors">Головна</a>
            </li>
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[--color-text-hint]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-[--color-text-muted] font-medium">FAQ</span>
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="mb-12">
        <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">
            Часті запитання <span class="text-[--color-primary]">(FAQ)</span>
        </h1>
        <p class="text-lg text-[--color-text-muted] max-w-2xl leading-relaxed">
            Ми зібрали відповіді на найбільш актуальні питання щодо класифікатора КВЕД-2010 та переходу на новий стандарт NACE 2.1-UA (2027).
        </p>
    </div>

    <!-- FAQ Accordion Container -->
    <div class="space-y-4 mb-16" x-data="{ active: null }">
        @foreach($faqs as $index => $faq)
            <div 
                class="group border border-[--color-border] rounded-2xl overflow-hidden transition-all duration-300 {{ $index === 0 ? 'bg-white' : 'bg-white' }}"
                :class="active === {{ $index }} ? 'shadow-xl ring-1 ring-[--color-primary]/10' : 'hover:border-[--color-primary]/30 shadow-sm'"
            >
                <button 
                    @click="active = (active === {{ $index }} ? null : {{ $index }})"
                    class="w-full flex items-start gap-4 p-5 md:p-6 text-left focus:outline-none"
                >
                    <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-[--color-primary-light] text-[--color-primary] flex items-center justify-center font-bold text-sm">
                        Q
                    </span>
                    <span class="flex-grow text-base md:text-lg font-bold text-slate-800 pr-4 transition-colors group-hover:text-[--color-primary] pt-0.5">
                        {{ $faq['q'] }}
                    </span>
                    <span 
                        class="flex-shrink-0 w-6 h-6 mt-1 rounded-full flex items-center justify-center transition-all duration-300 bg-slate-100 text-slate-400 group-hover:bg-[--color-primary-light] group-hover:text-[--color-primary]"
                        :class="active === {{ $index }} ? 'rotate-180 bg-[--color-primary] text-white p-1' : ''"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </button>

                <!-- Answer with Animation -->
                <div 
                    x-show="active === {{ $index }}"
                    x-collapse
                    x-cloak
                >
                    <div class="px-6 pb-6 border-t border-slate-50 pt-4 bg-slate-50/50">
                        <div class="flex gap-4">
                            <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm">
                                A
                            </span>
                            <div class="text-[--color-text-muted] leading-relaxed pt-0.5 rich-text">
                                {!! nl2br(e($faq['a'])) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Methodology Link Section -->
    <div class="bg-gradient-to-br from-[--color-primary-light] to-white border border-[--color-primary]/10 rounded-3xl p-8 mb-12 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="max-w-md text-center md:text-left">
            <h3 class="text-xl font-bold text-slate-900 mb-2">Шукаєте офіційну методологію?</h3>
            <p class="text-sm text-[--color-text-muted]">
                Ви можете ознайомитися з повним текстом Наказу Держстату № 191 про впровадження NACE Rev. 2.1 в Україні.
            </p>
        </div>
        <a href="https://ukrstat.gov.ua/metod_pob/naryadi/2023/n191.pdf" 
           target="_blank"
           class="inline-flex items-center gap-2 px-6 py-3 bg-[--color-primary] text-white font-bold rounded-2xl hover:scale-105 transition-transform active:scale-95 shadow-lg shadow-[--color-primary]/20"
        >
            <span>Завантажити PDF</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
        </a>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

