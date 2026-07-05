<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ISTV Vilcanota' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body data-authenticated>
    @include('layouts.partials.sidebar')

    <div id="main">
        @include('layouts.partials.header', ['title' => $title ?? 'Panel Principal', 'subtitle' => $subtitle ?? null])
        <div id="content">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
