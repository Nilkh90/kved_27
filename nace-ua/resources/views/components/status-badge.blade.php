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

