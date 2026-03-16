@extends('layouts.app')

@php
    $title = 'kved2027 — Переведіть свій КВЕД-2010 на NACE 2.1-UA безкоштовно';
    $description = 'Сервіс для швидкого та безпечного переходу українського бізнесу з класифікатора КВЕД-2010 на NACE 2.1-UA. Знайдіть свій код та дізнайтесь що потрібно зробити.';
@endphp

@section('content')
{{-- HERO SECTION --}}
<section class="relative overflow-hidden py-20 sm:py-28" style="background: linear-gradient(180deg, #EEF4FF 0%, #FFFFFF 100%)">
    {{-- Background accent --}}
    <div class="absolute -top-10 -right-10 w-80 h-80 rounded-full opacity-30 pointer-events-none" style="background: radial-gradient(circle, #93C5FD 0%, transparent 70%)"></div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        {{-- Eyebrow --}}
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold mb-8"
             style="background:#EEF4FF; color:#1A5FBE; border:1px solid #BAD6FC">
            📋 Наказ Держстату № 191 · Дедлайн 2027
        </div>

        {{-- Headline --}}
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6 leading-[1.1]" style="color:#0F1923">
            Ваш КВЕД переходить<br>
            <span style="color:#1A5FBE">на NACE 2.1&#8209;UA</span>
        </h1>

        {{-- Sub-headline --}}
        <p class="text-lg sm:text-xl mb-10 max-w-2xl mx-auto" style="color:#5A6A7F; line-height:1.7">
            Введіть старий код КВЕД — і за 10 секунд дізнайтесь:
            чи потрібно звертатись до реєстратора, чи все відбудеться автоматично.
        </p>

        {{-- Search Bar --}}
        <div class="max-w-2xl mx-auto mb-6">
            @livewire('search-bar')
        </div>

        {{-- AI Button placeholder --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('catalog') }}" class="text-sm font-medium flex items-center gap-2 hover:underline" style="color:#5A6A7F">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Відкрити повний каталог
            </a>
            <span style="color:#E2E8F2">|</span>
            <button type="button" class="text-sm font-medium flex items-center gap-2 opacity-50 cursor-not-allowed" style="color:#5A6A7F" title="Незабаром">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                Описати бізнес (AI) · Незабаром
            </button>
        </div>
    </div>
</section>

{{-- POPULAR CHANGES TABLE --}}
<section class="py-16" style="background:#FFFFFF">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @livewire('popular-changes')
    </div>
</section>

{{-- VALUE PROPOSITION --}}
<x-value-proposition style="background:#F8F9FC; border-top:1px solid #E2E8F2; border-bottom:1px solid #E2E8F2" />

{{-- CTA BLOCK --}}
<x-cta-section />

@endsection
