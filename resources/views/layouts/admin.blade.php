<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" data-theme="{{ session('theme', 'default') }}"
    style="background-color: #020617;">

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
    <title>{{ \App\Models\Setting::get('site_title', config('app.name', 'MovieShelf')) }}</title> <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet"> <!-- NOSONAR --> <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous"> <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes fadeInScale {
            0% { opacity: 0; transform: scale(0.98) translateY(10px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        .animate-page-entry {
            animation: fadeInScale 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        .glass-sidebar {
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(40px);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 20px 0 50px rgba(0, 0, 0, 0.3);
        }

        .sidebar-link-active {
            background: linear-gradient(90deg, rgba(225, 29, 72, 0.15) 0%, transparent 100%);
            color: #fb7185 !important;
            border-left: 3px solid #e11d48;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.03);
            transform: translateX(4px);
        }

        .header-glass {
            background: rgba(2, 6, 23, 0.6);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Fix for white dropdowns in dark theme */
        select {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: white !important;
        }
        
        select option {
            background-color: #0f172a !important;
            color: white !important;
            padding: 10px !important;
        }

        /* Scrollbar styles */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(225, 29, 72, 0.2);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(225, 29, 72, 0.4);
        }
    </style>
    @stack('styles')
</head>

<body class="font-sans antialiased text-white min-h-screen relative overflow-x-hidden selection:bg-rose-500/30" x-data="{ sidebarOpen: false }">
    @php $isImpersonating = session('impersonated_by'); @endphp

    {{-- Impersonation Banner --}}
    @if($isImpersonating)
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
    @endif

    <!-- Premium Cinematic background -->
    <div class="fixed inset-0 z-0 bg-[#020617] pointer-events-none overflow-hidden">
        <!-- Main Dark Gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-[#020617] via-[#0a0f1e] to-[#020617]"></div>
        
        <!-- Animated Blobs -->
        <div class="absolute top-[10%] -left-[10%] w-[50%] h-[50%] bg-rose-600/10 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-red-900/10 rounded-full blur-[100px] animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute top-[40%] left-[30%] w-[30%] h-[30%] bg-rose-500/5 rounded-full blur-[80px] animate-pulse" style="animation-delay: 4s;"></div>
        
        <!-- Subtle Grid Pattern (Optional but premium) -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 40px 40px;"></div>
        
        <!-- Vignette -->
        <div class="absolute inset-0 bg-radial-gradient from-transparent via-transparent to-black/40"></div>
    </div>

    <div class="min-h-screen flex overflow-x-hidden relative z-10">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden" 
             x-cloak></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="w-72 glass-sidebar flex flex-col shrink-0 transition-all duration-500 ease-in-out fixed left-0 z-50 {{ $isImpersonating ? 'top-10 h-[calc(100vh-2.5rem)]' : 'top-0 h-screen' }}">
            <div class="p-6 flex items-center justify-between"> 
                <a href="{{ route('dashboard') }}" class="flex items-center group"> 
                    <x-application-logo class="h-10 w-auto drop-shadow-md group-hover:scale-105 transition-transform duration-500" />
                </a> 
                <button @click="sidebarOpen = false" class="md:hidden text-gray-400 hover:text-white transition-colors">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
            <nav class="flex-1 px-4 space-y-2 mt-4 overflow-y-auto custom-scrollbar"> <!-- Übersicht -->
                <div class="pb-2 px-4 opacity-40"> <span
                        class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em]">Übersicht</span> </div> <a
                    href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active' : 'text-gray-400' }}">
                    <i class="bi bi-grid-1x2-fill"></i> <span class="font-bold text-sm">Dashboard</span> </a>
                @if (Route::has('admin.stats.index'))
                    <a href="{{ route('admin.stats.index') }}"
                        class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('admin.stats.*') ? 'sidebar-link-active' : 'text-gray-400' }}">
                        <i class="bi bi-bar-chart-fill"></i> <span class="font-bold text-sm">Statistiken</span> </a>
                    @endif <!-- Mediathek -->
                    <div class="pt-8 pb-3 px-6"> <span
                            class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em]">Mediathek</span> </div>
                    <a href="{{ route('admin.movies.index') }}"
                        class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('admin.movies.*') ? 'sidebar-link-active' : 'text-gray-400' }}">
                        <i class="bi bi-film"></i> <span class="font-bold text-sm">Filme</span> </a> <a
                        href="{{ route('admin.actors.index') }}"
                        class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('admin.actors.*') ? 'sidebar-link-active' : 'text-gray-400' }}">
                        <i class="bi bi-stars"></i> <span class="font-bold text-sm">Schauspieler</span> </a>
                    <!-- Datenaustausch -->
                    <div class="pt-8 pb-3 px-6"> <span
                            class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em]">Exchange</span>
                    </div> <a href="{{ route('admin.tmdb.index') }}"
                        class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('admin.tmdb.*') ? 'sidebar-link-active' : 'text-gray-400' }}">
                        <i class="bi bi-cloud-arrow-down-fill"></i> <span class="font-bold text-sm">TMDb Import</span> </a> <a
                        href="{{ route('admin.import.index') }}"
                        class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('admin.import.*') ? 'sidebar-link-active' : 'text-gray-400' }}">
                        <i class="bi bi-file-earmark-code-fill"></i> <span class="font-bold text-sm">XML Import</span>
                    </a> <a
                        href="{{ route('admin.import.backup.index') }}"
                        class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('import.backup.*') ? 'sidebar-link-active' : 'text-gray-400' }}">
                        <i class="bi bi-cloud-upload-fill"></i> <span class="font-bold text-sm">Backup Import</span>
                    </a> 
                    <a href="{{ route('cadmin.desktop.index') }}"
                        class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('cadmin.desktop.*') ? 'sidebar-link-active' : 'text-gray-400' }}">
                        <i class="bi bi-pc-display-horizontal"></i> <span class="font-bold text-sm">Desktop App</span>
                    </a> 
                    <!-- System -->
                    <div class="pt-8 pb-3 px-6"> <span
                            class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em]">System</span> </div> <a
                        href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : 'text-gray-400' }}">
                        <i class="bi bi-person-badge-fill"></i> <span class="font-bold text-sm">Benutzer</span>  </a>
                    <a href="{{ route('admin.bot.index') }}"
                        class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('admin.bot.*') ? 'sidebar-link-active' : 'text-gray-400' }}">
                        <i class="bi bi-robot"></i> <span class="font-bold text-sm">Actor Bot</span> </a>
                    @if (\App\Models\Setting::get('migration_enabled', '1') == '1')
                        <a href="{{ route('admin.migration.index') }}"
                            class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('admin.migration.index') ? 'sidebar-link-active' : 'text-gray-400' }}">
                            <i class="bi bi-database-fill-up"></i> <span class="font-bold text-sm">Daten Migration</span>
                        </a>
                        @endif
                        @if(Route::has('admin.settings.index'))
                        <a href="{{ route('admin.settings.index') }}"
                            class="flex items-center gap-3 px-6 py-3.5 rounded-xl transition-all sidebar-link {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active' : 'text-gray-400' }}">
                            <i class="bi bi-sliders"></i> <span class="font-bold text-sm">Einstellungen</span>
                        </a>
                        @endif
            </nav>
            <div class="p-4 border-t border-white/5 shrink-0">
                <form method="POST" action="{{ route('logout') }}"> @csrf <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-400/10 transition-all">
                        <i class="bi bi-box-arrow-left"></i> <span class="font-bold text-sm">Abmelden</span> </button>
                </form>
            </div>
        </aside> <!-- Main Content -->
        <main :class="sidebarOpen ? 'translate-x-72 md:translate-x-0' : 'translate-x-0'"
            class="flex-1 flex flex-col min-w-0 md:ml-72 transition-transform duration-500 ease-in-out">
            <header
                class="h-20 header-glass flex items-center justify-between px-6 md:px-12 z-40 shrink-0 sticky {{ $isImpersonating ? 'top-10' : 'top-0' }}">
                <div class="flex items-center gap-6">
                    <button @click="sidebarOpen = true" class="md:hidden text-gray-400 hover:text-white transition-colors">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <h1 class="text-xl md:text-3xl font-black text-white flex items-center gap-2 truncate tracking-tight"> @yield('header_title', 'Administration') </h1>
                </div>
                <div class="flex items-center gap-4 md:gap-6 shrink-0">
                    <div class="flex flex-col items-end hidden sm:flex"> <span
                            class="text-sm font-black text-white">{{ Auth::user()->name }}</span> <span
                            class="text-[10px] text-rose-400 uppercase font-black tracking-widest opacity-80">Administrator</span>
                    </div>
                    <div
                        class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-gradient-to-br from-rose-600 to-red-700 border border-white/10 flex items-center justify-center shadow-lg shadow-rose-500/20">
                        <i class="bi bi-person-fill text-white text-lg md:text-xl"></i> </div>
                </div>
            </header>
            <div class="p-6 md:p-12 animate-page-entry">
                @if (session('success'))
                    <div
                        class="mb-6 md:mb-8 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3 text-emerald-400 animate-in fade-in slide-in-from-top-4 duration-500">
                        <i class="bi bi-check-circle-fill"></i> <span
                            class="text-sm font-bold">{{ session('success') }}</span> </div>
                    @endif @if ($errors->any())
                        <div
                            class="mb-6 md:mb-8 p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl flex flex-col gap-2 text-rose-400 animate-in fade-in slide-in-from-top-4 duration-500">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-exclamation-triangle-fill"></i> <span
                                    class="text-sm font-bold">Bitte korrigiere die folgenden Fehler:</span>
                            </div>
                            <ul class="list-disc list-inside text-xs font-medium opacity-80 mt-2 pl-6">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif @if (isset($header))
                            <header class="mb-6 md:mb-8"> {{ $header }} </header>
                        @endif {{ $slot }}
            </div>
            <div class="mt-auto shrink-0">
                <x-footer :compact="true" />
            </div>
        </main>
    </div> @stack('scripts')
</body>

</html>
