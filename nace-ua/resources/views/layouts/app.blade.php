<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-seo :title="$title ?? null" :description="$description ?? null" />

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @include('partials.analytics')
</head>
@php
    $currentStandard = request()->route('standard') ?? 'kved';
@endphp
<body x-data="kvedFavorites()" class="min-h-screen bg-[--color-bg] text-[--color-text] font-sans selection:bg-[--color-primary-light] selection:text-[--color-primary] {{ $currentStandard === 'nace' ? 'theme-nace' : '' }}">

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
                            kved<span style="color: var(--color-primary)">.biz.ua</span>
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
                        FAQ
                    </a>
                    <button @click="isDrawerOpen = true" class="relative group flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 hover:text-[--color-primary] transition-colors rounded-xl hover:bg-slate-50">
                        <svg class="w-5 h-5 text-slate-500 group-hover:text-[--color-primary] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Мій список
                        <span x-show="items.length > 0" x-transition x-text="items.length" style="display: none;" class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white"></span>
                    </button>
                </nav>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center gap-4" x-data="{ open: false }">
                    <button @click="isDrawerOpen = true" class="relative p-1 text-[--color-text-muted] hover:text-[--color-text]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span x-show="items.length > 0" x-transition x-text="items.length" style="display: none;" class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white shadow-sm ring-2 ring-white"></span>
                    </button>
                    
                    <button @click="open = !open" class="text-[--color-text-muted] hover:text-[--color-text]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <!-- Mobile Menu (Alpine) -->
                    <div x-show="open" @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute top-16 right-4 w-48 py-2 bg-white rounded-2xl shadow-xl border border-[--color-border] z-50">
                        <a href="{{ route('catalog') }}" class="block px-4 py-2 text-sm hover:bg-[--color-surface]">Каталог</a>
                        <a href="{{ route('info') }}" class="block px-4 py-2 text-sm hover:bg-[--color-surface]">FAQ</a>
                        <div class="border-t border-[--color-border] my-1"></div>
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
                        <span class="text-xl font-bold tracking-tight">kved<span class="text-[--color-primary]">.biz.ua</span></span>
                    </div>
                    <p class="text-[--color-text-muted] max-w-sm mb-6">
                        Професійний сервіс переходу з класифікатора КВЕД-2010 на NACE 2.1-UA. 
                        Зрозуміло, швидко та безкоштовно для українського бізнесу.
                    </p>
                </div>
                <div class="md:col-start-4 md:text-right">
                    <h4 class="font-bold text-sm uppercase tracking-wider mb-6 text-[--color-text-hint]">Навігація</h4>
                    <ul class="space-y-4 text-sm">
                        <li><a href="{{ route('home') }}" class="text-[--color-text-muted] hover:text-[--color-primary]">Головна</a></li>
                        <li><a href="{{ route('catalog') }}" class="text-[--color-text-muted] hover:text-[--color-primary]">Каталог</a></li>
                        <li><a href="{{ route('info') }}" class="text-[--color-text-muted] hover:text-[--color-primary]">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-[--color-border] pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-[--color-text-hint]">
                    &copy; {{ date('Y') }} kved.biz.ua. Всі права захищені.
                </p>
                <div class="flex gap-6">
                    <!-- Placeholder icons or social links -->
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts

    <!-- Side Drawer Component -->
    <div x-show="isDrawerOpen" 
         class="fixed inset-0 z-[100] flex justify-end" 
         style="display: none;">
         
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
             x-show="isDrawerOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="isDrawerOpen = false"></div>

        <!-- Drawer Panel -->
        <div class="relative w-full max-w-md bg-white h-full shadow-2xl flex flex-col transition-transform"
             x-show="isDrawerOpen"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             @click.stop="">
             
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">Мій список</h2>
                </div>
                <button @click="isDrawerOpen = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <template x-if="items.length === 0">
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto mb-4 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <p class="text-slate-500 font-medium">Ваш список порожній</p>
                        <p class="text-sm text-slate-400 mt-1">Додайте КВЕДи з каталогу</p>
                    </div>
                </template>
                
                <div class="space-y-4">
                    <template x-for="item in items" :key="item.code">
                        <div class="group relative p-4 bg-white border border-slate-200 rounded-2xl hover:shadow-md hover:border-blue-200 transition-all">
                            <div class="flex items-start gap-4">
                                <!-- Radio for Main KVED -->
                                <div class="pt-1 flex-shrink-0">
                                    <label class="relative flex cursor-pointer items-center rounded-full p-1" :title="'Зробити ' + item.code + ' основним'">
                                        <input type="radio" name="mainKved" class="before:content[''] peer relative h-5 w-5 cursor-pointer appearance-none rounded-full border border-slate-300 text-blue-600 transition-all before:absolute before:top-2/4 before:left-2/4 before:block before:h-12 before:w-12 before:-translate-y-2/4 before:-translate-x-2/4 before:rounded-full before:bg-blue-500 before:opacity-0 before:transition-opacity checked:border-blue-600 checked:before:bg-blue-600 hover:before:opacity-10"
                                            :checked="mainCode === item.code"
                                            @change="setMain(item.code)" />
                                        <div class="pointer-events-none absolute top-2/4 left-2/4 -translate-y-2/4 -translate-x-2/4 text-blue-600 opacity-0 transition-opacity peer-checked:opacity-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" viewBox="0 0 16 16" fill="currentColor">
                                                <circle data-name="ellipse" cx="8" cy="8" r="8"></circle>
                                            </svg>
                                        </div>
                                    </label>
                                </div>
                                <div class="flex-1 pr-6">
                                    <div class="font-mono font-bold text-slate-900" x-text="item.code"></div>
                                    <div class="text-sm text-slate-600 mt-1 leading-snug" x-text="item.title"></div>
                                    <template x-if="mainCode === item.code">
                                        <div class="mt-2 text-xs font-bold text-blue-600 inline-block bg-blue-50 px-2 py-0.5 rounded-md">Основний вид діяльності</div>
                                    </template>
                                </div>
                                <button @click="remove(item.code)" class="absolute top-4 right-4 text-slate-300 hover:text-red-500 transition-colors" title="Видалити">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="p-6 border-t border-slate-100 bg-white" x-show="items.length > 0">
                <div class="flex flex-col gap-3 mb-6">
                    <button @click="printList()" class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold rounded-xl border border-slate-200 transition-colors">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Надрукувати список
                    </button>
                    <button @click="copyList()" class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold rounded-xl border border-slate-200 transition-colors">
                        <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Скопіювати список
                    </button>
                    <button @click="clear()" class="w-full text-center py-2 text-sm font-medium text-red-500 hover:text-red-600 transition-colors">
                        Очистити весь список
                    </button>
                </div>
            </div>
            
            <!-- Marketing CTA -->
            <div class="bg-blue-50/80 p-6 border-t border-blue-100">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm mb-1">Потрібна допомога зі змінами для ФОП?</h3>
                        <p class="text-xs text-slate-600 mb-3">Звертайтеся до нашого партнера за професійною консультацією.</p>
                        <a href="https://t.me/jurkommers" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-bold text-white bg-[#2AABEE] hover:bg-[#2298D6] py-2 px-4 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.888-.662 3.483-1.524 5.805-2.529 6.967-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                            Написати в Telegram
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function kvedFavorites() {
            return {
                isDrawerOpen: false,
                items: [],
                mainCode: null,
                
                init() {
                    const saved = localStorage.getItem('kved_favorites');
                    if (saved) {
                        try {
                            this.items = JSON.parse(saved);
                        } catch(e) { console.error('Error parsing favorites', e); }
                    }
                    const savedMain = localStorage.getItem('kved_main_code');
                    if (savedMain) {
                        this.mainCode = savedMain;
                    }

                    this.$watch('items', val => {
                        localStorage.setItem('kved_favorites', JSON.stringify(val));
                        // If mainCode is no longer in items, remove it
                        if (this.mainCode && !val.find(i => i.code === this.mainCode)) {
                            this.mainCode = null;
                        }
                    });
                    
                    this.$watch('mainCode', val => {
                        if (val) {
                            localStorage.setItem('kved_main_code', val);
                        } else {
                            localStorage.removeItem('kved_main_code');
                        }
                    });
                },
                
                add(code, title) {
                    if (!this.items.find(i => i.code === code)) {
                        this.items.push({ code, title });
                        // If it's the first item, set it as main automatically
                        if (this.items.length === 1) {
                            this.mainCode = code;
                        }
                    }
                    this.isDrawerOpen = true;
                },
                
                remove(code) {
                    this.items = this.items.filter(i => i.code !== code);
                },
                
                setMain(code) {
                    this.mainCode = code;
                },
                
                clear() {
                    if(confirm('Ви впевнені, що хочете очистити весь список?')) {
                        this.items = [];
                        this.mainCode = null;
                    }
                },
                
                printList() {
                    let text = "Мій список КВЕДів:\n\n";
                    this.items.forEach(item => {
                        text += `- ${item.code} ${item.title}` + (this.mainCode === item.code ? ' (Основний)' : '') + "\n";
                    });
                    
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write('<pre style="font-family: sans-serif; font-size: 14px; white-space: pre-wrap;">' + text + '</pre>');
                    printWindow.document.close();
                    printWindow.print();
                },
                
                copyList() {
                    let text = "Мій список КВЕДів:\n";
                    this.items.forEach(item => {
                        text += `- ${item.code} ${item.title}` + (this.mainCode === item.code ? ' (Основний)' : '') + "\n";
                    });
                    
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(text).then(() => {
                            alert('Список скопійовано в буфер обміну!');
                        }).catch(console.error);
                    } else {
                        // Fallback
                        const el = document.createElement('textarea');
                        el.value = text;
                        document.body.appendChild(el);
                        el.select();
                        document.execCommand('copy');
                        document.body.removeChild(el);
                        alert('Список скопійовано в буфер обміну!');
                    }
                }
            }
        }
    </script>
</body>
</html>
