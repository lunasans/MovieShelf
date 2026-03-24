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
    @vite(['resources/css/app.css', 'resources/js/app.js']) <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="font-sans antialiased bg-[#020617] text-gray-200" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex bg-[#020617] overflow-x-hidden relative">
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
            class="w-64 bg-[#020617] md:bg-[#020617]/90 md:backdrop-blur-xl border-r border-white/5 flex flex-col shrink-0 h-screen transition-all duration-300 ease-in-out fixed left-0 top-0 z-50">
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
                        class="text-[10px] font-bold text-white uppercase tracking-widest">Übersicht</span> </div> <a
                    href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                    <i class="bi bi-speedometer2"></i> <span class="font-bold text-sm">Dashboard</span> </a>
                @if (Route::has('admin.stats.index'))
                    <a href="{{ route('admin.stats.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.stats.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                        <i class="bi bi-graph-up"></i> <span class="font-bold text-sm">Statistiken</span> </a>
                    @endif <!-- Mediathek -->
                    <div class="pt-6 pb-2 px-4 opacity-40"> <span
                            class="text-[10px] font-bold text-white uppercase tracking-widest">Mediathek</span> </div>
                    <a href="{{ route('admin.movies.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.movies.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                        <i class="bi bi-collection-play"></i> <span class="font-bold text-sm">Filme</span> </a> <a
                        href="{{ route('admin.actors.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.actors.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                        <i class="bi bi-people"></i> <span class="font-bold text-sm">Schauspieler</span> </a>
                    <!-- Datenaustausch -->
                    <div class="pt-6 pb-2 px-4 opacity-40"> <span
                            class="text-[10px] font-bold text-white uppercase tracking-widest">Datenaustausch</span>
                    </div> <a href="{{ route('admin.tmdb.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.tmdb.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                        <i class="bi bi-cloud-download"></i> <span class="font-bold text-sm">TMDb Import</span> </a> <a
                        href="{{ route('admin.import.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.import.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                        <i class="bi bi-file-earmark-arrow-up"></i> <span class="font-bold text-sm">XML Import</span>
                    </a> <!-- System -->
                    <div class="pt-6 pb-2 px-4 opacity-40"> <span
                            class="text-[10px] font-bold text-white uppercase tracking-widest">System</span> </div> <a
                        href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.users.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                        <i class="bi bi-person-gear"></i> <span class="font-bold text-sm">Benutzer</span> </a> <a
                        href="{{ route('admin.update.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.update.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                        <i class="bi bi-arrow-repeat"></i> <span class="font-bold text-sm">System Update</span> </a>
                    @if (\App\Models\Setting::get('migration_enabled', '1') == '1')
                        <a href="{{ route('admin.migration.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.migration.index') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                            <i class="bi bi-database-up"></i> <span class="font-bold text-sm">Daten Migration</span>
                        </a>
                        @endif <a href="{{ route('admin.settings.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                            <i class="bi bi-gear"></i> <span class="font-bold text-sm">Einstellungen</span> </a>
            </nav>
            <div class="p-4 border-t border-white/5 shrink-0">
                <form method="POST" action="{{ route('logout') }}"> @csrf <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-400/10 transition-all">
                        <i class="bi bi-box-arrow-left"></i> <span class="font-bold text-sm">Abmelden</span> </button>
                </form>
            </div>
        </aside> <!-- Main Content -->
        <main :class="sidebarOpen ? 'translate-x-64 md:translate-x-0' : 'translate-x-0'"
            class="flex-1 flex flex-col min-w-0 transition-transform duration-300 ease-in-out">
            <header
                class="h-16 glass border-b border-white/5 flex items-center justify-between px-4 md:px-8 z-40 shrink-0 sticky top-0">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="md:hidden text-gray-400 hover:text-white transition-colors">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <h1 class="text-base md:text-lg font-bold text-white flex items-center gap-2 truncate"> @yield('header_title', 'Administration') </h1>
                </div>
                <div class="flex items-center gap-2 md:gap-4 shrink-0">
                    <div class="flex flex-col items-end hidden sm:flex"> <span
                            class="text-sm font-bold text-white">{{ Auth::user()->name }}</span> <span
                            class="text-[10px] text-gray-500 uppercase font-black tracking-widest">Administrator</span>
                    </div>
                    <div
                        class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-800 border border-white/10 flex items-center justify-center">
                        <i class="bi bi-person-fill text-gray-400 text-sm md:text-base"></i> </div>
                </div>
            </header>
            <div class="p-4 md:p-8">
                @if (session('success'))
                    <div
                        class="mb-6 md:mb-8 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3 text-emerald-400 animate-in fade-in slide-in-from-top-4 duration-500">
                        <i class="bi bi-check-circle-fill"></i> <span
                            class="text-sm font-bold">{{ session('success') }}</span> </div>
                    @endif @if (session('error'))
                        <div
                            class="mb-6 md:mb-8 p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl flex items-center gap-3 text-rose-400 animate-in fade-in slide-in-from-top-4 duration-500">
                            <i class="bi bi-exclamation-circle-fill"></i> <span
                                class="text-sm font-bold">{{ session('error') }}</span> </div>
                        @endif @if (isset($header))
                            <header class="mb-6 md:mb-8"> {{ $header }} </header>
                        @endif {{ $slot }}
            </div>
            <div class="mt-auto border-t border-white/5 bg-black/20 backdrop-blur-sm shrink-0"> <x-footer
                    :compact="true" /> </div>
        </main>
    </div> <x-theme-switcher /> @stack('scripts')
</body>

</html>
