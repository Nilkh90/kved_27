@props([
    'type',
    'actionRequired' => false,
])

@php
    $config = match ($type) {
        '1_TO_1' => ['color' => 'green', 'label' => 'Автоматичний перехід'],
        '1_TO_N' => ['color' => 'amber', 'label' => 'Потрібен вибір напрямку'],
        'N_TO_1' => ['color' => 'blue', 'label' => 'Коди об’єднані'],
        default => ['color' => 'gray', 'label' => 'Невідомий статус'],
    };

    if ($actionRequired) {
        $config = ['color' => 'red', 'label' => 'Потрібна перереєстрація'];
    }

    $classes = [
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
        match ($config['color']) {
            'green' => 'bg-emerald-100 text-emerald-800',
            'amber' => 'bg-amber-100 text-amber-800',
            'blue' => 'bg-sky-100 text-sky-800',
            'red' => 'bg-rose-100 text-rose-800',
            default => 'bg-slate-100 text-slate-700',
        },
    ];
@endphp

<span {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
    {{ $config['label'] }}
</span>

@props(['type', 'actionRequired' => false])

@php
$config = match($type) {
    '1_TO_1' => ['color' => 'green',  'label' => 'Автоматичний перехід'],
    '1_TO_N' => ['color' => 'amber',  'label' => 'Потрібен вибір напрямку'],
    'N_TO_1' => ['color' => 'blue',   'label' => 'Коди об\'єднано'],
    default  => ['color' => 'gray',   'label' => 'Невідомо'],
};
if ($actionRequired) {
    $config = ['color' => 'red', 'label' => 'Потрібна перереєстрація'];
}
@endphp

<span class="badge badge-{{ $config['color'] }}">{{ $config['label'] }}</span>

