<section class="py-4">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold" style="color:#0F1923">Найпопулярніші зміни</h2>
        <a href="{{ route('catalog') }}" class="text-sm font-medium flex items-center gap-1 hover:underline" style="color:#1A5FBE">
            Переглянути всі
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    @if (count($items) === 0)
        <div class="rounded-2xl border py-12 text-center" style="border-color:#E2E8F2">
            <p class="text-sm" style="color:#94A3B8">Поки що немає статистики змін.</p>
        </div>
    @else
        <div class="rounded-2xl border overflow-hidden shadow-sm" style="border-color:#E2E8F2">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:#F8F9FC; border-bottom:1px solid #E2E8F2">
                        <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wider" style="color:#94A3B8">Старий код</th>
                        <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wider" style="color:#94A3B8">Новий код</th>
                        <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wider" style="color:#94A3B8">Статус</th>
                        <th class="text-right px-5 py-3 text-xs font-bold uppercase tracking-wider" style="color:#94A3B8">Перегляди</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach ($items as $item)
                        <tr class="border-b cursor-pointer hover:bg-blue-50 transition-colors group" style="border-color:#F1F5F9"
                            onclick="window.location='{{ route('catalog') }}'">
                            <td class="px-5 py-3.5">
                                <span class="font-mono font-semibold text-sm" style="color:#0F1923">
                                    {{ $item['kved_code'] ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono font-semibold text-sm" style="color:#16A34A">
                                    {{ $item['nace_code'] ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <x-status-badge
                                    :type="$item['transition_type']"
                                    :action-required="$item['action_required']"
                                    size="sm"
                                />
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-medium" style="color:#5A6A7F">
                                    {{ $item['view_count'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
