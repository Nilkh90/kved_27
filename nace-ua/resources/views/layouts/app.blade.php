<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-seo :title="$title ?? null" :description="$description ?? null" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
@php
    $currentStandard = request()->route('standard') ?? 'kved';
@endphp
<body class="min-h-screen bg-[--color-bg] text-[--color-text] font-sans selection:bg-[--color-primary-light] selection:text-[--color-primary] {{ $currentStandard === 'nace' ? 'theme-nace' : '' }}">

    <!-- Header / Navigation -->
    <header class="glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold transition-colors" style="background-color: var(--color-primary)">
                            K
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-900">
                            kved<span style="color: var(--color-primary)">2027</span>
                        </span>
                    </a>
                </div>

                <!-- Central Switcher (New) -->
                <div class="hidden sm:flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200">
                    <a href="{{ route('catalog.index', ['standard' => 'kved']) }}" 
                       class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all {{ $currentStandard === 'kved' ? 'bg-white shadow-sm text-blue-700' : 'text-slate-500 hover:text-slate-700' }}">
                        КВЕД 2010
                    </a>
                    <a href="{{ route('catalog.index', ['standard' => 'nace']) }}" 
                       class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all {{ $currentStandard === 'nace' ? 'bg-white shadow-sm text-emerald-600' : 'text-slate-500 hover:text-slate-700' }}">
                        NACE 2027
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ route('catalog') }}" class="text-sm font-medium transition-colors {{ request()->routeIs('catalog*') ? 'text-[--color-primary] font-bold' : 'text-slate-700 hover:text-blue-700' }}">
                        Каталог
                    </a>
                    <a href="{{ route('info') }}" class="text-sm font-medium transition-colors {{ request()->routeIs('info*') ? 'text-blue-700' : 'text-slate-700 hover:text-blue-700' }}">
                        Методологія
                    </a>
                    <a href="#" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-semibold rounded-xl text-white shadow-sm transition-all hover:scale-[1.02] active:scale-[0.98]" style="background-color:#1A5FBE">
                        Описати бізнес
                    </a>
                </nav>

                <!-- Mobile menu button (placeholder for Alpine) -->
                <div class="md:hidden flex items-center" x-data="{ open: false }">
                    <button @click="open = !open" class="text-[--color-text-muted] hover:text-[--color-text]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <!-- Mobile Menu (Alpine) -->
                    <div x-show="open" @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute top-16 right-4 w-48 py-2 bg-white rounded-2xl shadow-xl border border-[--color-border] z-50">
                        <a href="{{ route('catalog') }}" class="block px-4 py-2 text-sm hover:bg-[--color-surface]">Каталог</a>
                        <a href="{{ route('info') }}" class="block px-4 py-2 text-sm hover:bg-[--color-surface]">Методологія</a>
                        <div class="border-t border-[--color-border] my-1"></div>
                        <a href="#" class="block px-4 py-2 text-sm text-[--color-primary] font-bold">Описати бізнес</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[--color-surface] border-t border-[--color-border] py-12 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-[--color-primary] rounded-lg flex items-center justify-center text-white font-bold">
                            K
                        </div>
                        <span class="text-xl font-bold tracking-tight">kved<span class="text-[--color-primary]">2027</span></span>
                    </div>
                    <p class="text-[--color-text-muted] max-w-sm mb-6">
                        Професійний сервіс переходу з класифікатора КВЕД-2010 на NACE 2.1-UA. 
                        Зрозуміло, швидко та безкоштовно для українського бізнесу.
                    </p>
                    <a href="https://ukrstat.gov.ua/metod_pob/naryadi/2023/n191.pdf" target="_blank" class="text-sm font-medium text-[--color-primary] hover:underline flex items-center gap-2">
                        <span>Наказ Держстату № 191</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
                <div>
                    <h4 class="font-bold text-sm uppercase tracking-wider mb-6 text-[--color-text-hint]">Навігація</h4>
                    <ul class="space-y-4 text-sm">
                        <li><a href="{{ route('home') }}" class="text-[--color-text-muted] hover:text-[--color-primary]">Головна</a></li>
                        <li><a href="{{ route('catalog') }}" class="text-[--color-text-muted] hover:text-[--color-primary]">Каталог</a></li>
                        <li><a href="{{ route('info') }}" class="text-[--color-text-muted] hover:text-[--color-primary]">Методологія</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-sm uppercase tracking-wider mb-6 text-[--color-text-hint]">Допомога</h4>
                    <ul class="space-y-4 text-sm">
                        <li><a href="{{ route('info') }}" class="text-[--color-text-muted] hover:text-[--color-primary]">FAQ</a></li>
                        <li><a href="#" class="text-[--color-text-muted] hover:text-[--color-primary]">Зворотний зв'язок</a></li>
                        <li><a href="#" class="text-[--color-text-muted] hover:text-[--color-primary]">Політика конфіденційності</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-[--color-border] pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-[--color-text-hint]">
                    &copy; {{ date('Y') }} kved2027.ua. Всі права захищені.
                </p>
                <div class="flex gap-6">
                    <!-- Placeholder icons or social links -->
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
