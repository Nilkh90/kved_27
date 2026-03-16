<section {{ $attributes->merge(['class' => 'mt-8 rounded-2xl border border-slate-200 bg-slate-900 px-5 py-6 text-slate-50']) }}>
    <div class="flex flex-col items-start gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold">Готуєтесь до переходу на NACE 2.1-UA?</h2>
            <p class="mt-1 text-sm text-slate-200">
                Спробуйте протестову версію: знайдіть свій КВЕД, подивіться нові коди та оцініть обсяг змін.
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('catalog') }}"
               class="inline-flex items-center justify-center rounded-full bg-emerald-400 px-4 py-2 text-sm font-medium text-slate-900 shadow-sm hover:bg-emerald-300">
                Відкрити каталог
            </a>
            <a href="{{ route('info') }}"
               class="inline-flex items-center justify-center rounded-full border border-slate-500 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-800">
                Дізнатись методологію
            </a>
        </div>
    </div>
</section>

