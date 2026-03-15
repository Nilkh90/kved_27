@extends('layouts.app')

@section('title', 'Деталі коду')

@section('content')
    <h2>Код {{ $code }} ({{ $standard }})</h2>
    <x-status-badge type="1_TO_1" />
    <x-mapping-panel />
@endsection

