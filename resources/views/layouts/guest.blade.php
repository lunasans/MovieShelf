<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="icon" type="image/png" href="{{ asset('img/logo/logo_small.png') }}">
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#020617">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="MovieShelf">
        <link rel="apple-touch-icon" href="{{ asset('img/logo/logo_small.png') }}">

        <title>{{ \App\Models\Setting::get('site_title', config('app.name', 'MovieShelf')) }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> <!-- NOSONAR -->

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#020617] text-gray-200 overflow-hidden">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative">
            <!-- Background Decorative Elements -->
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
                <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-blue-600/20 blur-[120px] rounded-full"></div>
                <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] bg-purple-600/20 blur-[120px] rounded-full"></div>
            </div>

            <div class="mb-8 animate-in fade-in zoom-in duration-700">
                <a href="/">
                    <x-application-logo class="h-32 w-auto drop-shadow-[0_0_30px_rgba(37,99,235,0.3)]" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-8 py-10 glass-strong border border-white/10 shadow-2xl rounded-3xl animate-in fade-in slide-in-from-bottom-8 duration-700">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
