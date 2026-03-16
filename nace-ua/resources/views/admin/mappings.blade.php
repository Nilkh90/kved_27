@extends('layouts.admin')

@section('admin')
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-[--color-text]">Таблиця перехідних ключів (Маппінг)</h2>
        <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-[--color-primary] hover:underline">
            &larr; Назад на дашборд
        </a>
    </div>
    
    <livewire:admin.data-table modelName="TransitionMapping" />
@endsection
