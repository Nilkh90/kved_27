@extends('layouts.app')

@section('title', $code->code . ' — ' . $code->title)

@section('content')
    <x-seo
        :title="$code->code . ' — ' . $code->title . ' | NACE 2.1-UA'"
        :description="\Illuminate\Support\Str::limit($code->description ?? '', 140)"
        :canonical="route('code.show', [$standard, $code->code])"
    />

    <x-breadcrumbs class="mb-4">
        <li class="inline-flex items-center text-xs text-slate-600">
            <a href="{{ route('home') }}" class="hover:text-slate-900">Головна</a>
            <span class="mx-1 text-slate-400">/</span>
            <a href="{{ route('catalog') }}" class="hover:text-slate-900">Каталог</a>
            <span class="mx-1 text-slate-400">/</span>
            <span class="font-medium text-slate-800">{{ $code->code }}</span>
        </li>
    </x-breadcrumbs>

    <h1 class="text-xl font-semibold tracking-tight text-slate-900">
        {{ $code->code }} — {{ $code->title }}
    </h1>

    @isset($mapping)
        <x-mapping-panel :old-code="$oldCode ?? null" :new-code="$newCode ?? null" :mapping="$mapping" />
    @endisset

    <x-includes-excludes
        class="mt-6"
        :includes="$code->includes ?? []"
        :excludes="$code->excludes ?? []"
    />
@endsection

