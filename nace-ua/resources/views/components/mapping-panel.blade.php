@props([
    'oldCode' => null,
    'newCode' => null,
    'mapping' => null,
])

<section class="mt-8 rounded-2xl border overflow-hidden shadow-sm" style="border-color:#E2E8F2">
    {{-- Header --}}
    <div class="px-6 py-4 flex items-center justify-between gap-4" style="background-color:#F8F9FC; border-bottom:1px solid #E2E8F2">
        <h3 class="text-sm font-bold uppercase tracking-wider" style="color:#5A6A7F">
            Відповідність КВЕД → NACE 2.1-UA
        </h3>
        @if ($mapping)
            <x-status-badge
                :type="$mapping->transition_type"
                :action-required="$mapping->action_required"
                size="md"
            />
        @endif
    </div>

    {{-- Two-column body --}}
    <div class="grid md:grid-cols-2 divide-y md:divide-y-0 md:divide-x" style="divide-color:#E2E8F2">
        {{-- Left: Old Code (KVED) --}}
        <div class="p-6 bg-white">
            <div class="text-xs font-bold uppercase tracking-widest mb-3" style="color:#94A3B8">Було (КВЕД-2010)</div>
            @if ($oldCode)
                <div class="text-3xl font-mono font-bold mb-1" style="color:#0F1923">{{ $oldCode->code }}</div>
                <p class="text-sm font-medium" style="color:#5A6A7F">{{ $oldCode->title }}</p>
            @else
                <p class="text-sm" style="color:#94A3B8">Код не визначений.</p>
            @endif
        </div>

        {{-- Right: New Code (NACE) --}}
        <div class="p-6" style="background-color:#F0FDF4">
            <div class="text-xs font-bold uppercase tracking-widest mb-3" style="color:#16A34A">Стало (NACE 2.1-UA)</div>
            @if ($newCode)
                <div class="text-3xl font-mono font-bold mb-1" style="color:#0F1923">{{ $newCode->code }}</div>
                <p class="text-sm font-medium" style="color:#5A6A7F">{{ $newCode->title }}</p>
            @else
                <p class="text-sm" style="color:#94A3B8">Код ще не визначений.</p>
            @endif
        </div>
    </div>

    {{-- Transition Comment --}}
    @if ($mapping?->transition_comment)
        <div class="px-6 py-4" style="background-color:#EEF4FF; border-top:1px solid #E2E8F2">
            <p class="text-sm" style="color:#1A5FBE">
                <span class="font-semibold">Коментар до переходу:</span>
                {{ $mapping->transition_comment }}
            </p>
        </div>
    @endif

    {{-- Action Required Banner --}}
    @if($mapping?->action_required)
        <div class="px-6 py-4 flex items-start gap-3" style="background-color:#FEE2E2; border-top:1px solid #FCA5A5">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" style="color:#B91C1C">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div>
                <p class="text-sm font-bold" style="color:#B91C1C">Потрібна перереєстрація</p>
                <p class="text-sm mt-0.5" style="color:#991B1B">
                    Для цього коду необхідно звернутися до державного реєстратора та подати заяву про зміну виду діяльності.
                </p>
            </div>
        </div>
    @endif
</section>
