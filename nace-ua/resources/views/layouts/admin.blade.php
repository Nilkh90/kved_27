@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex gap-8">
        
        <!-- Sidebar Navigation -->
        <aside class="w-1/4">
            <div class="bg-white rounded-xl shadow-sm border border-[--color-border] p-4 flex flex-col space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[--color-surface] text-[--color-primary]' : 'text-[--color-text] hover:bg-gray-50' }}">
                    Дашборд
                </a>
                <a href="{{ route('admin.import') }}" class="px-4 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.import') ? 'bg-[--color-surface] text-[--color-primary]' : 'text-[--color-text] hover:bg-gray-50' }}">
                    Завантаження
                </a>
                <div class="border-t border-[--color-border] my-2"></div>
                <div class="px-4 py-1 text-xs font-bold text-[--color-text-muted] uppercase tracking-wider">Бази даних</div>
                <a href="{{ route('admin.kved') }}" class="px-4 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.kved') ? 'bg-[--color-surface] text-[--color-primary]' : 'text-[--color-text] hover:bg-gray-50' }}">
                    КВЕД-2010
                </a>
                <a href="{{ route('admin.nace') }}" class="px-4 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.nace') ? 'bg-[--color-surface] text-[--color-primary]' : 'text-[--color-text] hover:bg-gray-50' }}">
                    NACE-2027
                </a>
                <a href="{{ route('admin.mappings') }}" class="px-4 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.mappings') ? 'bg-[--color-surface] text-[--color-primary]' : 'text-[--color-text] hover:bg-gray-50' }}">
                    Маппінг
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="w-3/4">
            @yield('admin')
        </main>

    </div>
@endsection

