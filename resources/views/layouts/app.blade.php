<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ session('theme', \App\Models\Setting::get('theme', 'default')) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\Setting::get('site_title', config('app.name', 'MovieShelf')) }}</title>

        <!-- Fonts: Inter and JetBrains Mono from 1.5.0 -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-white min-h-screen" style="background: var(--gradient-bg); background-attachment: fixed;">
        <div class="px-4 pb-12 sm:px-6 lg:px-8">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main class="mt-8">
                {{ $slot }}
            </main>

            <x-footer />
        <x-theme-switcher />
        @stack('scripts')
    </body>
</html>
