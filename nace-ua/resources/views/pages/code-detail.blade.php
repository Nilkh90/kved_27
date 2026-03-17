@extends('layouts.app')

@php
    $title = $code->code . ' — ' . $code->title . ' | kved2027';
    $description = \Illuminate\Support\Str::limit($code->description ?? 'Перегляньте відповідність коду ' . $code->code . ' між класифікаторами КВЕД-2010 та NACE 2.1-UA.', 155);
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-2 text-sm mb-8" style="color:#94A3B8">
        <a href="{{ route('home') }}" class="hover:underline transition-colors" style="color:#5A6A7F">Головна</a>
        <span>/</span>
        <a href="{{ route('catalog') }}" class="hover:underline transition-colors" style="color:#5A6A7F">Каталог</a>
        
        @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
            @foreach($breadcrumbs as $bc)
                <span>/</span>
                @if($bc['active'])
                    <span class="font-mono font-semibold" style="color:#1A5FBE">{{ $bc['title'] }}</span>
                @else
                    <a href="{{ $bc['route'] }}" class="hover:underline transition-colors" style="color:#5A6A7F">{{ $bc['title'] }}</a>
                @endif
            @endforeach
        @else
            <span>/</span>
            <span class="font-mono font-semibold" style="color:#1A5FBE">{{ $code->code }}</span>
        @endif
    </nav>

    {{-- Code Header --}}
    <div class="rounded-3xl border p-8 mb-6 shadow-sm" style="background:#FFFFFF; border-color:#E2E8F2">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
            <div>
                {{-- Standard Badge --}}
                <span class="inline-block text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4"
                      style="background:#EEF4FF; color:#1A5FBE">
                    {{ strtoupper($standard === 'nace' ? 'NACE 2.1-UA' : 'КВЕД-2010') }}
                </span>

                {{-- Code --}}
                <h1 class="text-4xl font-mono font-extrabold tracking-tight mb-2" style="color:#0F1923">
                    {{ $code->code }}
                </h1>

                {{-- Title --}}
                <p class="text-xl font-semibold mb-6" style="color:#5A6A7F">{{ $code->title }}</p>

                {{-- Description --}}
                @if (!empty($code->description))
                    <div class="mt-4 prose prose-slate prose-lg max-w-none">
                        <p class="text-lg leading-relaxed text-slate-600 italic font-medium border-l-4 border-blue-500 pl-6 py-2 bg-slate-50 rounded-r-2xl">
                            {{ $code->description }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Status Badge --}}
            @isset($mapping)
                <div class="flex-shrink-0">
                    <x-status-badge
                        :type="$mapping->transition_type"
                        :action-required="$mapping->action_required"
                        size="lg"
                    />
                </div>
            @endisset
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-3 mt-6 pt-6 border-t" style="border-color:#E2E8F2">
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all hover:bg-gray-50"
                    style="border-color:#E2E8F2; color:#5A6A7F">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Надрукувати
            </button>
            <button type="button" onclick="navigator.clipboard?.writeText(window.location.href)"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all hover:bg-gray-50"
                    style="border-color:#E2E8F2; color:#5A6A7F">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
                Поділитися
            </button>
        </div>
    </div>

    {{-- Mapping Panel --}}
    @isset($mapping)
        <x-mapping-panel
            :old-code="$oldCode ?? null"
            :new-code="$newCode ?? null"
            :mapping="$mapping"
        />
    @endisset

    {{-- Includes / Excludes --}}
    <x-includes-excludes
        class="mt-6"
        :includes="$code->includes ?? []"
        :excludes="$code->excludes ?? []"
    />


    {{-- Navigation Links --}}
    <div class="mt-8 pt-6 border-t flex items-center justify-between gap-4" style="border-color:#E2E8F2">
        <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 text-sm font-medium hover:underline" style="color:#5A6A7F">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Повернутись до каталогу
        </a>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl text-white transition-all hover:opacity-90" style="background-color:#1A5FBE">
            Новий пошук
        </a>
    </div>
</div>
@endsection
