<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'NACE 2.1-UA')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @livewireStyles
</head>
<body class="bg-surface text-text">
    <header>
        <h1>NACE 2.1-UA</h1>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} NACE 2.1-UA</p>
    </footer>

    @livewireScripts
</body>
</html>

