@php
    $isAuthPage = request()->routeIs('login', 'two-factor.login');
    $isStreaming = $isAuthPage || ((request()->routeIs('dashboard', 'movies.show', 'actors.show', 'actors.index', 'movies.trailers', 'profile.edit', 'statistics', 'impressum')) && (optional(auth()->user())->layout ?? \App\Models\Setting::get('default_guest_layout', 'classic')) === 'streaming');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      class="{{ $isStreaming ? 'dark' : '' }}"
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

        {{-- Impersonation Banner --}}
        @if(session('impersonated_by'))
        <div class="fixed top-0 inset-x-0 z-[9999] flex items-center justify-between gap-4 px-6 py-2.5 bg-indigo-600 text-white text-xs font-black uppercase tracking-widest shadow-lg">
            <div class="flex items-center gap-2">
                <i class="bi bi-person-badge-fill"></i>
                Support-Modus · Eingeloggt als Tenant-Admin · Cadmin: {{ session('impersonated_by') }}
            </div>
            <a href="{{ route('impersonate.exit') }}"
               class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/20 hover:bg-white/30 transition-colors">
                <i class="bi bi-box-arrow-right"></i> Beenden
            </a>
        </div>
        <div class="h-10"></div>
        @endif

        {{-- Global Announcement Banner --}}
        @if(!empty($globalAnnouncement['active']))
        @php
            $annType = $globalAnnouncement['type'] ?? 'info';
            $annColors = match($annType) {
                'warning'  => 'bg-amber-500 text-black',
                'critical' => 'bg-rose-600 text-white',
                default    => 'bg-indigo-500 text-white',
            };
            $annIcon = match($annType) {
                'warning'  => 'bi-exclamation-triangle-fill',
                'critical' => 'bi-exclamation-octagon-fill',
                default    => 'bi-info-circle-fill',
            };
            $annKey = 'ann_' . md5($globalAnnouncement['text'] ?? '');
        @endphp
        <div x-data="{ show: localStorage.getItem('{{ $annKey }}') !== '1' }"
             x-show="show"
             x-transition
             class="{{ $annColors }} relative z-50 flex items-center justify-between gap-4 px-6 py-2.5 text-xs font-bold shadow-lg">
            <div class="flex items-center gap-2">
                <i class="bi {{ $annIcon }}"></i>
                <span>{{ $globalAnnouncement['text'] }}</span>
            </div>
            <button @click="show = false; localStorage.setItem('{{ $annKey }}', '1')"
                    class="opacity-60 hover:opacity-100 transition-opacity">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        @endif

        <div class="{{ $isStreaming ? 'relative z-10' : 'px-4 pb-12 sm:px-6 lg:px-8 relative z-10' }}">
            @include('layouts.navigation')

            <!-- Page Content -->
            @php
                $hasHero = request()->routeIs('dashboard', 'movies.show', 'actors.show');
                $mainClasses = $isStreaming 
                    ? ($hasHero ? 'mt-0' : 'pt-32') 
                    : 'mt-8';
            @endphp
            <main class="{{ $mainClasses }}">
                {{ $slot }}
            </main>

            @if(Route::has('dashboard'))
                <x-footer :is-streaming="$isStreaming" />
            @else
                <x-saas-footer />
            @endif
        </div>

        @if(!$isStreaming)
            <x-theme-switcher />
        @endif

        @stack('scripts')

        @if(\App\Models\Setting::get('cookie_banner_enabled', '1') == '1')
            @include('tenant.partials.cookie-banner')
        @endif
        @stack('modals')
    </body>
</html>