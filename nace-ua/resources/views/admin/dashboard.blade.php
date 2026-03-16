@extends('layouts.admin')

@section('admin')
    <h2 class="text-2xl font-bold text-[--color-text] mb-6">Дашборд</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Metric Card 1 -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-[--color-border] flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-[--color-text-muted] mb-1">Коди КВЕД-2010</p>
                <h3 class="text-3xl font-bold text-[--color-text]">{{ number_format($stats['kved_count'], 0, ',', ' ') }}</h3>
            </div>
            <div class="bg-[--color-surface] p-3 rounded-lg text-[--color-primary]">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
        </div>

        <!-- Metric Card 2 -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-[--color-border] flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-[--color-text-muted] mb-1">Коди NACE-2027</p>
                <h3 class="text-3xl font-bold text-[--color-text]">{{ number_format($stats['nace_count'], 0, ',', ' ') }}</h3>
            </div>
            <div class="bg-indigo-50 p-3 rounded-lg text-indigo-600">
                 <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
        </div>

        <!-- Metric Card 3 -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-[--color-border] flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-[--color-text-muted] mb-1">Зв'язки (Маппінг)</p>
                <h3 class="text-3xl font-bold text-[--color-text]">{{ number_format($stats['mapping_count'], 0, ',', ' ') }}</h3>
            </div>
            <div class="bg-[--color-success-bg] p-3 rounded-lg text-[--color-success]">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </div>
        </div>
    </div>
@endsection

