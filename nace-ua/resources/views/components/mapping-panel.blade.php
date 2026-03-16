@props([
    'oldCode' => null,
    'newCode' => null,
    'mapping' => null, // App\Models\TransitionMapping|null
])

<section class="mt-8 rounded-xl border border-slate-200 bg-white/80 p-4 shadow-sm shadow-slate-100">
    <header class="mb-4 flex items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Відповідність КВЕД → NACE 2.1-UA</h3>
            @if ($mapping?->transition_comment)
                <p class="mt-1 text-xs text-slate-600">
                    {{ $mapping->transition_comment }}
                </p>
            @endif
        </div>

        @if ($mapping)
            <x-status-badge
                :type="$mapping->transition_type"
                :action-required="$mapping->action_required"
            />
        @endif
    </header>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-lg border border-slate-100 bg-slate-50/80 p-3">
            <h4 class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                Було (КВЕД-2010)
            </h4>

            @if ($oldCode)
                <p class="text-sm font-mono text-slate-900">{{ $oldCode->code }}</p>
                <p class="mt-0.5 text-sm text-slate-700">{{ $oldCode->title }}</p>
            @else
                <p class="text-sm text-slate-500">Код буде додано пізніше.</p>
            @endif
        </div>

        <div class="rounded-lg border border-emerald-50 bg-emerald-50/80 p-3">
            <h4 class="mb-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                Стало (NACE 2.1-UA)
            </h4>

            @if ($newCode)
                <p class="text-sm font-mono text-slate-900">{{ $newCode->code }}</p>
                <p class="mt-0.5 text-sm text-slate-700">{{ $newCode->title }}</p>
            @else
                <p class="text-sm text-slate-500">Код ще не визначений.</p>
            @endif
        </div>
    </div>
</section>


