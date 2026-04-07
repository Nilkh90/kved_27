@extends('layouts.app')

@php
    $isNace = $standard === 'nace';
    $displayName = $isNace ? 'NACE 2.1-UA (2027)' : 'КВЕД-2010';
    $title = $code->code . ' — ' . $code->title . ' | ' . $displayName . ' | kved.biz.ua';
    $description = \Illuminate\Support\Str::limit($code->description ? strip_tags($code->description) : 'Перегляньте деталі коду ' . $code->code . ' у класифікаторі ' . $displayName, 155);
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-2 text-sm mb-8" style="color:#94A3B8">
        <ol class="flex items-center gap-2" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a itemprop="item" href="{{ route('home') }}" class="hover:underline transition-colors" style="color:#5A6A7F">
                    <span itemprop="name">Головна</span>
                </a>
                <meta itemprop="position" content="1" />
            </li>
            
            @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                @foreach($breadcrumbs as $bc)
                    <span>/</span>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        @if($bc['active'])
                            <span itemprop="name" class="font-mono font-semibold {{ $isNace ? 'text-emerald-600' : 'text-blue-600' }}">{{ $bc['title'] }}</span>
                            <link itemprop="item" href="{{ $bc['route'] }}" />
                        @else
                            <a itemprop="item" href="{{ $bc['route'] }}" class="hover:underline transition-colors" style="color:#5A6A7F">
                                <span itemprop="name">{{ $bc['title'] }}</span>
                            </a>
                        @endif
                        <meta itemprop="position" content="{{ $loop->iteration + 1 }}" />
                    </li>
                @endforeach
            @else
                <span>/</span>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <span itemprop="name" class="font-mono font-semibold {{ $isNace ? 'text-emerald-600' : 'text-blue-600' }}">{{ $code->code }}</span>
                    <link itemprop="item" href="{{ url()->current() }}" />
                    <meta itemprop="position" content="2" />
                </li>
            @endif
        </ol>
    </nav>

    {{-- Code Header --}}
    <div class="rounded-3xl border p-8 mb-6 shadow-sm" style="background:#FFFFFF; border-color:#E2E8F2">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
            <div>
                {{-- Standard Badge --}}
                <span class="inline-block text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 {{ $isNace ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ strtoupper($isNace ? 'NACE 2.1-UA (2027)' : 'КВЕД-2010') }}
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
                        <div class="text-lg leading-relaxed text-slate-600 italic font-medium border-l-4 {{ $isNace ? 'border-emerald-500' : 'border-blue-500' }} pl-6 py-2 bg-slate-50 rounded-r-2xl rich-text">
                            {!! $code->description !!}
                        </div>
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
            <button type="button" @click="add('{{ $code->code }}', '{{ addslashes(str_replace(PHP_EOL, '', ltrim($code->title))) }}')"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5"
                    style="background-color: var(--color-primary)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Додати до списку
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
        :includes-also="$code->includes_also ?? []"
        :excludes="$code->excludes ?? []"
    />


    {{-- Navigation Links --}}
    <div class="mt-8 pt-6 border-t flex items-center justify-between gap-4" style="border-color:#E2E8F2">
        <a href="{{ route('catalog.index', ['standard' => $standard]) }}" class="inline-flex items-center gap-2 text-sm font-medium hover:underline" style="color:#5A6A7F">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Повернутись до каталогу
        </a>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl text-white transition-all hover:opacity-90" style="background-color: var(--color-primary)">
            Новий пошук
        </a>
    </div>
</div>
@endsection

