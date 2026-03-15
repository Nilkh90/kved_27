@extends('layouts.app')

@section('title', 'Каталог класифікатора')

@section('content')
    <h2>Каталог ({{ $standard }})</h2>
    @livewire('classifier-tree', ['standard' => $standard])
@endsection

