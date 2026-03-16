@props([
    'title',
    'description' => null,
    'canonical' => null,
    'jsonLd' => null,
])

<title>{{ $title }}</title>

<meta name="description" content="{{ $description }}">
@if ($canonical)
    <link rel="canonical" href="{{ $canonical }}">
@endif

@if ($jsonLd)
    <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif

