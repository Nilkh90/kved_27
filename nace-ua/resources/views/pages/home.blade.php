@extends('layouts.app')

@section('title', 'Головна — NACE 2.1-UA')

@section('content')
    <h2>Пошук відповідності КВЕД → NACE 2.1-UA</h2>
    @livewire('search-bar')
    @livewire('popular-changes')
@endsection

