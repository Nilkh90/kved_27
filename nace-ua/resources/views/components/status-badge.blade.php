@props([
    'type',
    'actionRequired' => false,
    'size' => 'md', // 'sm' | 'md' | 'lg'
])

@php
    $config = match ($type) {
        '1_TO_1' => [
            'label' => 'Автоматичний перехід',
            'icon'  => '✓',
            'bg'    => '#DCFCE7',
            'text'  => '#15803D',
            'border'=> '#86EFAC',
        ],
        '1_TO_N' => [
            'label' => 'Потрібен вибір напрямку',
            'icon'  => '⚡',
            'bg'    => '#FEF3C7',
            'text'  => '#B45309',
            'border'=> '#FCD34D',
        ],
        'N_TO_1' => [
            'label' => "Коди об'єднані",
            'icon'  => '⊕',
            'bg'    => '#E0F2FE',
            'text'  => '#0369A1',
            'border'=> '#7DD3FC',
        ],
        default => [
            'label' => 'Невідомий статус',
            'icon'  => '?',
            'bg'    => '#F1F5F9',
            'text'  => '#64748B',
            'border'=> '#CBD5E1',
        ],
    };

    if ($actionRequired) {
        $config = [
            'label' => 'Потрібна перереєстрація',
            'icon'  => '!',
            'bg'    => '#FEE2E2',
            'text'  => '#B91C1C',
            'border'=> '#FCA5A5',
        ];
    }

    $sizeClasses = match($size) {
        'sm'  => 'px-2 py-0.5 text-xs rounded-md gap-1',
        'lg'  => 'px-4 py-1.5 text-sm rounded-xl gap-2 font-semibold',
        default => 'px-2.5 py-1 text-xs rounded-lg gap-1.5',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium $sizeClasses"]) }}
      style="background-color:{{ $config['bg'] }}; color:{{ $config['text'] }}; border: 1px solid {{ $config['border'] }};">
    <span aria-hidden="true">{{ $config['icon'] }}</span>
    {{ $config['label'] }}
</span>
