<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" data-theme="{{ session('theme', 'default') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MovieShelf Admin') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#020617] text-gray-200">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 glass-strong border-r border-white/5 flex flex-col z-50">
            <div class="p-6">
                <a href="{{ route('dashboard') }}" class="flex items-center group">
                    <x-application-logo class="h-10 w-auto drop-shadow-md group-hover:scale-105 transition-transform duration-500" />
                </a>
            </div>

            <nav class="flex-1 px-4 space-y-2 mt-4">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span class="font-bold text-sm">Dashboard</span>
                </a>
                
                <div class="pt-4 pb-2 px-4">
                    <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Katalog</span>
                </div>

                <a href="{{ route('admin.movies.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.movies.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                    <i class="bi bi-collection-play"></i>
                    <span class="font-bold text-sm">Filme</span>
                </a>

                <a href="{{ route('admin.tmdb.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.tmdb.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                    <i class="bi bi-cloud-download"></i>
                    <span class="font-bold text-sm">TMDb Import</span>
                </a>

                <a href="{{ route('admin.actors.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.actors.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                    <i class="bi bi-people"></i>
                    <span class="font-bold text-sm">Schauspieler</span>
                </a>

                <div class="pt-4 pb-2 px-4">
                    <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">System</span>
                </div>

                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-white/5' }}">
                    <i class="bi bi-gear"></i>
                    <span class="font-bold text-sm">Einstellungen</span>
                </a>
            </nav>

            <div class="p-4 border-t border-white/5">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-400/10 transition-all">
                        <i class="bi bi-box-arrow-left"></i>
                        <span class="font-bold text-sm">Abmelden</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <header class="h-16 glass border-b border-white/5 flex items-center justify-between px-8 sticky top-0 z-40">
                <h1 class="text-lg font-bold text-white flex items-center gap-2">
                    @yield('header_title', 'Administration')
                </h1>
                
                <div class="flex items-center gap-4">
                    <div class="flex flex-col items-end">
                        <span class="text-sm font-bold text-white">{{ Auth::user()->name }}</span>
                        <span class="text-[10px] text-gray-500 uppercase font-black tracking-widest">Administrator</span>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-800 border border-white/10 flex items-center justify-center">
                         <i class="bi bi-person-fill text-gray-400"></i>
                    </div>
                </div>
            </header>

            <div class="p-8">
                @if (session('success'))
                    <div class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3 text-emerald-400 animate-in fade-in slide-in-from-top-4 duration-500">
                        <i class="bi bi-check-circle-fill"></i>
                        <span class="text-sm font-bold">{{ session('success') }}</span>
                    </div>
                @endif

                @if (isset($header))
                    <header class="mb-8">
                        {{ $header }}
                    </header>
                @endif

                {{ $slot }}

                <x-footer />
            </div>
        </main>
    </div>

    <x-theme-switcher />
    @stack('scripts')
</body>
</html>
