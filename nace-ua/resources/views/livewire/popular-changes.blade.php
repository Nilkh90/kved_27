<section class="mt-8">
    <header class="mb-3 flex items-center justify-between gap-4">
        <h3 class="text-sm font-semibold text-slate-900">
            Популярні зміни (топ-10 переглядів)
        </h3>
    </header>

    @if (count($items) === 0)
        <p class="text-sm text-slate-500">
            Поки що немає статистики змін.
        </p>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white/80">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-3 py-2">Тип</th>
                        <th class="px-3 py-2">Коментар</th>
                        <th class="px-3 py-2 text-right">Перегляди</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($items as $item)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-3 py-2 align-top">
                                <x-status-badge
                                    :type="$item['transition_type']"
                                    :action-required="$item['action_required']"
                                />
                            </td>
                            <td class="px-3 py-2 align-top text-slate-800">
                                {{ $item['comment'] ?? 'Тестовий маппінг' }}
                            </td>
                            <td class="px-3 py-2 align-top text-right text-slate-600">
                                {{ $item['view_count'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>


