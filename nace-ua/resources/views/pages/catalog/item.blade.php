@extends('layouts.app')

@php
    $isNace = $standard === 'nace';
    $displayName = $isNace ? 'NACE 2.1-UA (2027)' : 'КВЕД-2010';
    $title = $item->code . ' ' . $item->title . ' | ' . $displayName . ' | kved2027';
    $description = 'Каталог класифікатора ' . $displayName . '. ' . $item->title;
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                            <span itemprop="name" style="color:#0F1923; font-weight:600">{{ $bc['title'] }}</span>
                            <link itemprop="item" href="{{ $bc['route'] }}" />
                        @else
                            <a itemprop="item" href="{{ $bc['route'] }}" class="hover:underline transition-colors" style="color:#5A6A7F">
                                <span itemprop="name">{{ $bc['title'] }}</span>
                            </a>
                        @endif
                        <meta itemprop="position" content="{{ $loop->iteration + 1 }}" />
                    </li>
                @endforeach
            @endif
        </ol>
    </nav>

    <div class="lg:grid lg:grid-cols-12 gap-12">
        {{-- Left: Current Item Info --}}
        <div class="lg:col-span-12 mb-8">
            <div class="bg-white rounded-3xl border p-8 shadow-sm" style="border-color:#E2E8F2">
                <span class="inline-block text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4 {{ $isNace ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ strtoupper($level) === 'SECTION' ? 'Секція' : (strtoupper($level) === 'DIVISION' ? 'Розділ' : (strtoupper($level) === 'GROUP' ? 'Група' : $level)) }}
                </span>
                
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                    <div class="max-w-4xl">
                        <h1 class="text-3xl md:text-4xl font-bold mb-6" style="color:#0F1923">
                            <span class="font-mono {{ $isNace ? 'text-emerald-600' : 'text-blue-600' }}">{{ $item->code }}</span> — {{ $item->title }}
                        </h1>
                        
                        @if($item->description)
                            <div class="mt-4 prose prose-slate prose-lg max-w-none">
                                <div class="text-xl leading-relaxed text-slate-600 italic font-medium border-l-4 {{ $isNace ? 'border-emerald-500' : 'border-blue-500' }} pl-6 py-2 bg-slate-50 rounded-r-2xl rich-text">
                                    {!! $item->description !!}
                                </div>
                            </div>
                        @endif

                        {{-- Inclusions/Exclusions for Sections/Divisions/Groups --}}
                        <x-includes-excludes
                            class="mt-8"
                            :includes="$item->includes ?? []"
                            :includes-also="$item->includes_also ?? []"
                            :excludes="$item->excludes ?? []"
                        />
                    </div>

                    <div class="hidden md:block">
                        <a href="{{ route('catalog.index', ['standard' => $standard]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-all hover:bg-slate-50" style="border-color:#E2E8F2; color:#5A6A7F">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Весь каталог
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
                    @php
                        $childParams = ['standard' => $standard];
                        if ($child->level === 'DIVISION') {
                            $childParams['division_code'] = $child->code;
                        } elseif ($child->level === 'GROUP') {
                            $childParams['division_code'] = $item->code;
                            $childParams['group_code'] = $child->slug;
                        } elseif ($child->level === 'CLASS') {
                            $isGroup = $item->level === 'GROUP';
                            $childParams['division_code'] = $isGroup ? $item->parent->code : $item->code;
                            $childParams['group_code'] = $isGroup ? $item->slug : $child->parent->slug;
                            $childParams['class_code'] = $child->slug;
                        }
                    @endphp
                    <a href="{{ route('catalog.' . strtolower($child->level), $childParams) }}" 
                       class="group block p-6 rounded-2xl border transition-all hover:shadow-lg {{ $isNace ? 'hover:border-emerald-300' : 'hover:border-blue-300' }} hover:scale-[1.01]"
                       style="background:#FFFFFF; border-color:#E2E8F2">
                        <div class="flex items-start gap-5">
                            <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center font-mono font-bold text-xl transition-all {{ $isNace ? 'group-hover:bg-emerald-600' : 'group-hover:bg-blue-600' }} group-hover:text-white group-hover:rotate-3 shadow-sm"
                                 style="{{ $isNace ? 'background:#F0FDF4; color:#059669' : 'background:#F0F7FF; color:#1A5FBE' }}">
                                {{ $child->code }}
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold mb-2 {{ $isNace ? 'group-hover:text-emerald-700' : 'group-hover:text-blue-700' }} transition-colors leading-snug" style="color:#0F1923">
                                    {{ $child->title }}
                                </h3>
                                @if($child->description)
                                    <div class="text-sm line-clamp-2 text-slate-500 leading-relaxed mb-3">
                                        {{ Str::limit(strip_tags($child->description), 130) }}
                                    </div>
                                @endif
                                <div class="mt-3">
                                    <button type="button" @click.prevent="add('{{ $child->code }}', '{{ addslashes(str_replace(PHP_EOL, '', ltrim($child->title))) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 text-slate-600 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-colors shadow-sm"
                                            :class="items && items.find(i => i.code === '{{ $child->code }}') ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : ''">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Додати до списку
                                    </button>
                                </div>
                            </div>
                            <div class="flex-shrink-0 self-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all group-hover:bg-slate-50 group-hover:translate-x-1" style="color:#94A3B8">
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
