@extends('layouts.app')

@section('content')
<x-seo
    :title="'Каталог класифікатора ' . strtoupper($standard) . ' | kved2027'"
    description="Повний каталог класифікаторів КВЕД-2010 та NACE 2.1-UA з можливістю пошуку та перегляду ієрархічного дерева видів діяльності."
/>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Page Header --}}
    <div class="mb-8">
        <nav class="flex items-center gap-2 text-sm mb-3" style="color:#94A3B8">
            <a href="{{ route('home') }}" class="hover:underline" style="color:#5A6A7F">Головна</a>
            <span>/</span>
            <span style="color:#0F1923; font-weight:500">Каталог</span>
        </nav>
        <h1 class="text-3xl font-bold" style="color:#0F1923">Каталог класифікаторів</h1>
        <p class="mt-2 text-base" style="color:#5A6A7F">
            Ієрархічне дерево видів діяльності. Оберіть стандарт та знайдіть потрібний розділ.
        </p>
    </div>

    {{-- Two-column Desktop Layout --}}
    <div class="lg:grid lg:grid-cols-[360px_1fr] gap-8">
        {{-- Left: Tree --}}
        <div>
            @livewire('classifier-tree', ['standard' => $standard])
        </div>

        {{-- Right: Info Panel (placeholder) --}}
        <div class="hidden lg:flex items-start justify-center pt-16">
            <div class="text-center max-w-sm">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-6 mx-auto" style="background:#EEF4FF">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#1A5FBE">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold mb-2" style="color:#0F1923">Розгорніть дерево</h3>
                <p class="text-sm leading-relaxed" style="color:#5A6A7F">
                    Оберіть розділ у дереві ліворуч, щоб переглянути деталі та знайти потрібний код.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
