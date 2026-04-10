<!DOCTYPE html>
<html lang="de" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MovieShelf – Dein digitales Filmregal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/landing.css', 'resources/js/app.js'])
    <style>
        :root {
            --platinum-bg: #FFFFFF;
            --platinum-text: #222222;
        }

        [x-cloak] { display: none !important; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--platinum-bg);
            color: var(--platinum-text);
            overflow-x: hidden;
        }

        h1, h2, h3, h4 { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .glass-ultra {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid #E5E7EB;
        }

        .mesh-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
            background: #FFFFFF;
        }
        
        .mesh-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(150px);
            opacity: 0.12;
        }

        .animate-reveal {
            opacity: 0;
            transform: translateY(80px);
            filter: blur(40px);
            transition: all 1.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .animate-reveal.active {
            opacity: 1;
            transform: translateY(0);
            filter: blur(0);
        }
        
        /* MATERIAL GRAIN FILTER */
        svg#grain-filter { display: none; }
    </style>
</head>
<body class="antialiased selection:bg-rose-600 selection:text-white">

    <!-- Global Mesh Gradient (Subtle) -->
    <div class="mesh-bg">
        <div class="mesh-circle bg-rose-600/5 w-[1200px] h-[1200px] -top-[40%] -left-[15%] animate-pulse"></div>
        <div class="mesh-circle bg-indigo-600/5 w-[1000px] h-[1000px] bottom-0 -right-[10%] opacity-8"></div>
    </div>
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-8 py-6" x-data="{ menuOpen: false }">
        <div class="max-w-[1400px] mx-auto rounded-[8px] px-8 py-4 flex items-center justify-between transition-all duration-1200 glass-ultra shadow-sm relative">
            <div class="flex items-center gap-4">
                <img src="/img/logo/logo_small.png" alt="Logo" class="h-10 bg-black/5 p-2 rounded-lg">
                <span class="text-2xl font-black tracking-tight text-[#222222]">MOVIE<span class="text-orange-600">SHELF</span></span>
            </div>
            {{-- Desktop nav --}}
            <div class="hidden md:flex items-center gap-10 text-[13px] font-bold tracking-tight text-gray-500">
                <a href="{{ route('landing') }}#features" class="hover:text-black transition-colors">Features</a>
                <button onclick="document.getElementById('subdomain')?.focus(); window.scrollTo({top:0,behavior:'smooth'})"
                        class="bg-[#CC4B06] text-white px-8 py-3 rounded-[8px] font-bold text-[11px] hover:bg-[#A33C05] transition-all active:scale-95">
                    REGISTRIEREN
                </button>
            </div>
            {{-- Mobile hamburger --}}
            <button class="md:hidden p-2 text-gray-500 hover:text-black transition-colors" @click="menuOpen = !menuOpen" aria-label="Menü öffnen">
                <i class="bi text-2xl" :class="menuOpen ? 'bi-x-lg' : 'bi-list'"></i>
            </button>
        </div>
        {{-- Mobile dropdown --}}
        <div x-show="menuOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             @click.outside="menuOpen = false"
             class="max-w-[1400px] mx-auto mt-2 glass-ultra rounded-xl shadow-lg p-4 flex flex-col gap-2 md:hidden">
            <a href="{{ route('landing') }}#features" @click="menuOpen = false"
               class="text-sm font-bold text-gray-600 hover:text-black transition-colors py-2 px-4 rounded-lg hover:bg-gray-50">
                Features
            </a>
            <button @click="menuOpen = false; document.getElementById('subdomain')?.focus(); window.scrollTo({top:0,behavior:'smooth'})"
                    class="bg-[#CC4B06] text-white py-3 rounded-lg font-bold text-sm hover:bg-[#A33C05] transition-all mt-1">
                Jetzt registrieren
            </button>
        </div>

        <!-- Global Flash Messages -->
        <div class="max-w-[1400px] mx-auto mt-4 px-4 overflow-hidden">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)" 
                     class="glass-ultra border-l-4 border-emerald-500 p-4 shadow-xl rounded-xl flex items-center justify-between text-emerald-800 font-bold text-sm tracking-tight animate-fade-in">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-check-circle-fill text-emerald-500"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" 
                     class="glass-ultra border-l-4 border-rose-500 p-4 shadow-xl rounded-xl flex items-center justify-between text-rose-800 font-bold text-sm tracking-tight animate-bounce-in">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-exclamation-triangle-fill text-rose-500"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif
        </div>
    </nav>

    @yield('content')

    <footer class="py-20 border-t border-gray-100 bg-white">
        <div class="max-w-[1400px] mx-auto px-8 grid md:grid-cols-4 gap-12">
            {{-- Brand --}}
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <img src="/img/logo/logo_small.png" alt="Logo" class="h-10 p-1">
                    <span class="font-extrabold text-2xl text-[#222222]">MovieShelf</span>
                </div>
                <p class="text-gray-500 text-sm font-medium leading-relaxed">
                    Deine Filmsammlung in der Cloud –<br>
                    modern, schnell und ohne Aufwand.
                </p>
                <span class="text-gray-300 text-xs font-bold tracking-widest uppercase">
                    MovieShelf Cloud · v{{ config('app.version', '2.14.0') }}
                </span>
            </div>
            {{-- Navigation --}}
            <div class="space-y-4">
                <h4 class="font-bold text-xs uppercase tracking-widest text-gray-400">Navigation</h4>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('landing') }}#features" class="text-sm text-gray-500 hover:text-[#CC4B06] transition-colors font-medium">Features</a>
                    @if(isset($faqs) && $faqs->count() > 0)
                    <a href="{{ route('landing') }}#faq" class="text-sm text-gray-500 hover:text-[#CC4B06] transition-colors font-medium">FAQ</a>
                    @endif
                    <a href="{{ route('landing') }}" class="text-sm text-gray-500 hover:text-[#CC4B06] transition-colors font-medium">Registrieren</a>
                </div>
            </div>
            {{-- Rechtliches --}}
            <div class="space-y-4">
                <h4 class="font-bold text-xs uppercase tracking-widest text-gray-400">Rechtliches</h4>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('privacy') }}" class="text-sm text-gray-500 hover:text-[#CC4B06] transition-colors font-medium">Datenschutz</a>
                    @if(\App\Models\Setting::get('saas_impressum_active', '0') == '1')
                    <a href="{{ route('saas.impressum') }}" class="text-sm text-gray-500 hover:text-[#CC4B06] transition-colors font-medium">Impressum</a>
                    @endif
                    <a href="{{ route('account-deletion') }}" class="text-sm text-gray-500 hover:text-[#CC4B06] transition-colors font-medium">Konto löschen</a>
                </div>
            </div>
            {{-- Social + Copyright --}}
            <div class="flex flex-col items-end justify-between gap-6">
                <div class="flex gap-4">
                    <a href="https://github.com/lunasans/MovieShelf" target="_blank" rel="noopener"
                       class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400 hover:bg-[#CC4B06] hover:text-white transition-all"
                       aria-label="GitHub">
                        <i class="bi bi-github text-xl"></i>
                    </a>
                </div>
                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-[0.3em] text-right leading-loose">
                    © 2026 RENÉ NEUHAUS
                </p>
            </div>
        </div>
    </footer>

    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.animate-reveal').forEach(el => observer.observe(el));
    </script>
</body>
</html>
