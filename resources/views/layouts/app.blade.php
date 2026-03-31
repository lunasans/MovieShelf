@php
    $isStreaming = (request()->routeIs('dashboard') || request()->routeIs('movies.show') || request()->routeIs('actors.show') || request()->routeIs('movies.trailers')) && (optional(auth()->user())->layout ?? 'classic') === 'streaming';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      data-theme="{{ $isStreaming ? 'dark' : session('theme', \App\Models\Setting::get('theme', 'default')) }}" 
      style="background-color: #0c0c0e;">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="referrer" content="no-referrer-when-downgrade">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="icon" type="image/png" href="{{ asset('img/logo/logo_small.png') }}">

        <title>{{ \App\Models\Setting::get('site_title', config('app.name', 'MovieShelf')) }}</title>

        <!-- Fonts: Outfit (Display) and Plus Jakarta Sans (Body) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased text-white min-h-screen relative" 
          style="background: var(--gradient-bg); background-attachment: fixed;"
          x-data="{
            bg1: '',
            bg2: '',
            activeBg: 1,
            init() {
                window.addEventListener('change-background', (e) => {
                    const url = e.detail;
                    if (this.activeBg === 1) {
                        this.bg2 = url;
                        this.activeBg = 2;
                    } else {
                        this.bg1 = url;
                        this.activeBg = 1;
                    }
                });
            }
          }">
        
        <!-- Dynamic Background Layers -->
        <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none" x-show="bg1 || bg2" x-transition.opacity.duration.1000ms>
            <!-- Layer 1 -->
            <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out" 
                 :class="activeBg === 1 ? 'opacity-40' : 'opacity-0'" 
                 :style="'background-image: url(' + bg1 + '); background-size: cover; background-position: center;'">
            </div>
            <!-- Layer 2 -->
            <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out" 
                 :class="activeBg === 2 ? 'opacity-40' : 'opacity-0'" 
                 :style="'background-image: url(' + bg2 + '); background-size: cover; background-position: center;'">
            </div>
            
            <!-- Glassmorphism Overlay -->
            <div class="absolute inset-0 bg-gray-950/40 backdrop-blur-[100px]"></div>
            
            <!-- Dark Vignette -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#0c0c0e] via-transparent to-[#0c0c0e]/50"></div>
        </div>

        <div class="{{ $isStreaming ? 'relative z-10' : 'px-4 pb-12 sm:px-6 lg:px-8 relative z-10' }}">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main class="{{ $isStreaming ? 'mt-0' : 'mt-8' }}">
                {{ $slot }}
            </main>

            <x-footer :is-streaming="$isStreaming" />
        </div>

        @if(!$isStreaming)
            <x-theme-switcher />
        @endif

        @stack('scripts')

        @if(\App\Models\Setting::get('cookie_banner_enabled', '1') == '1')
            @include('partials.cookie-banner')
        @endif
    </body>
</html>