<section {{ $attributes->merge(['class' => 'py-16']) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl px-8 py-14 text-center" style="background: linear-gradient(135deg, #1A5FBE 0%, #0F4494 100%)">
            <h2 class="text-3xl font-bold text-white mb-3">
                Готуєтесь до переходу на NACE 2.1-UA?
            </h2>
            <p class="text-lg mb-8 max-w-xl mx-auto" style="color:#BAD6FC">
                Перевірте свої коди зараз — це займе менше хвилини, а результат буде одразу.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('catalog') }}"
                   class="inline-flex items-center justify-center px-8 py-3.5 rounded-xl text-sm font-bold text-slate-900 shadow-lg transition-all hover:scale-[1.02] active:scale-[0.98]"
                   style="background-color:#4ADE80">
                    Перейти до каталогу
                </a>
                <a href="{{ route('info') }}"
                   class="inline-flex items-center justify-center px-8 py-3.5 rounded-xl text-sm font-bold text-white border border-white/20 backdrop-blur-sm transition-all hover:bg-white/10">
                    Читати методологію
                </a>
            </div>
        </div>
    </div>
</section>
