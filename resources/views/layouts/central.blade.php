<!DOCTYPE html>
<html lang="de" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MovieShelf – Dein digitales Filmregal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/landing.css'])
    <style>
        :root {
            --platinum-bg: #FFFFFF;
            --platinum-text: #222222;
            --apex-accent: #CC4B06;
        }
        
        * { cursor: none !important; } /* Hide system cursor for Apex Gallery */

        body { 
            font-family: 'Manrope', sans-serif; 
            background-color: var(--platinum-bg); 
            color: var(--platinum-text);
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4 { font-family: 'Manrope', sans-serif; }
        
        /* APEX CURSOR */
        #apex-cursor {
            position: fixed;
            top: 0; left: 0;
            width: 40px; height: 40px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.15s cubic-bezier(0.16, 1, 0.3, 1), width 0.3s ease, height 0.3s ease, background 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #apex-cursor-dot {
            width: 4px; height: 4px;
            background: var(--apex-accent);
            border-radius: 50%;
        }

        .cursor-hover #apex-cursor {
            width: 80px; height: 80px;
            background: rgba(0,0,0,0.03);
            border-color: var(--apex-accent);
        }

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
<body class="antialiased selection:bg-rose-600 selection:text-white" 
      x-data="{ mouseX: 0, mouseY: 0, isHovering: false }"
      @mousemove="mouseX = $event.clientX; mouseY = $event.clientY">

    <!-- APEX CURSOR ELEMENT -->
    <div id="apex-cursor" 
         :style="`transform: translate3d(${mouseX - 20}px, ${mouseY - 20}px, 0)`"
         :class="{ 'cursor-hover': isHovering }">
        <div id="apex-cursor-dot"></div>
    </div>

    <!-- Global Mesh Gradient (Subtle) -->
    <div class="mesh-bg">
        <div class="mesh-circle bg-rose-600/5 w-[1200px] h-[1200px] -top-[40%] -left-[15%] animate-pulse"></div>
        <div class="mesh-circle bg-indigo-600/5 w-[1000px] h-[1000px] bottom-0 -right-[10%] opacity-8"></div>
    </div>
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-8 py-6">
        <div class="max-w-[1400px] mx-auto rounded-[8px] px-8 py-4 flex items-center justify-between transition-all duration-1200 glass-ultra shadow-sm">
            <div class="flex items-center gap-4" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                <img src="/img/logo/logo_small.png" alt="Logo" class="h-10 bg-black/5 p-2 rounded-lg">
                <span class="text-2xl font-black tracking-tight text-[#222222]">MOVIE<span class="text-orange-600">SHELF</span></span>
            </div>
            <div class="hidden md:flex items-center gap-12 text-[13px] font-bold tracking-tight text-gray-500">
                <a href="#features" class="hover:text-black transition-colors" @mouseenter="isHovering = true" @mouseleave="isHovering = false">Features</a>
                <a href="/login" class="bg-[#CC4B06] text-white px-8 py-3 rounded-[8px] font-bold text-[11px] hover:bg-[#A33C05] transition-all active:scale-95" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                    GET STARTED
                </a>
            </div>
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

    <footer class="py-32 border-t border-gray-100 bg-white">
        <div class="max-w-[1400px] mx-auto px-8 grid md:grid-cols-4 gap-20">
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <img src="/img/logo/logo_small.png" alt="Logo" class="h-10 p-1">
                    <span class="font-extrabold text-2xl text-[#222222]">MovieShelf</span>
                </div>
                <p class="text-gray-500 text-sm font-medium leading-relaxed">
                    Elevating movie collections to a cinematic cloud experience. <br>
                    Platinum Apex: Ultimate Edition v2.14.0
                </p>
            </div>
            <div class="md:col-span-2"></div>
            <div class="flex flex-col items-end gap-10">
                <div class="flex gap-6">
                    <a href="#" class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400 hover:bg-[#CC4B06] hover:text-white transition-all"><i class="bi bi-github text-xl"></i></a>
                    <a href="#" class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400 hover:bg-[#CC4B06] hover:text-white transition-all"><i class="bi bi-twitter-x text-xl"></i></a>
                </div>
                <div class="flex flex-col items-end gap-2 text-gray-400 text-[10px] font-bold uppercase tracking-[0.5em] text-right mt-4">
                    <span>© 2026 RENÉ NEUHAUS</span>
                    <div class="flex items-center gap-4 mt-2">
                        <a href="{{ route('privacy') }}" class="hover:text-[#CC4B06] transition-colors">DATENSCHUTZ</a>
                        <span>•</span>
                        <a href="{{ route('account-deletion') }}" class="hover:text-[#CC4B06] transition-colors">KONTO LÖSCHEN</a>
                    </div>
                </div>
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
