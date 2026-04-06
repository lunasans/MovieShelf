<!DOCTYPE html>
<html lang="de" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MovieShelf – Dein digitales Filmregal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Outfit:wght@100..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --accent: #e11d48;
            --accent-glow: rgba(225, 29, 72, 0.4);
            --platinum-bg: #F9F9FB;
            --platinum-text: #050505;
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--platinum-bg); 
            color: var(--platinum-text);
            overflow-x: hidden;
        }
        h1, h2, h3, h4 { font-family: 'Inter', sans-serif; }
        
        .glass-ultra {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(100px);
            -webkit-backdrop-filter: blur(100px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 50px 150px -30px rgba(0,0,0,0.06);
            filter: url(#platinum-grain);
        }

        .mesh-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
            background: var(--platinum-bg);
            filter: url(#platinum-grain);
        }
        
        .mesh-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(140px);
            opacity: 0.1;
        }

        .animate-reveal {
            opacity: 0;
            transform: translateY(60px);
            filter: blur(20px);
            transition: all 1.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .animate-reveal.active {
            opacity: 1;
            transform: translateY(0);
            filter: blur(0);
        }
        
        /* MATERIAL GRAIN FILTER */
        svg#grain-filter {
            display: none;
        }
    </style>
</head>
<body class="antialiased selection:bg-rose-600 selection:text-white">

    <!-- GLOBAL GRAIN DEFINITION -->
    <svg id="grain-filter">
        <filter id="platinum-grain">
            <feTurbulence type="fractalNoise" baseFrequency="0.8" numOctaves="4" stitchTiles="stitch" />
            <feColorMatrix type="saturate" values="0" />
            <feComponentTransfer>
                <feFuncA type="linear" slope="0.05" />
            </feComponentTransfer>
            <feBlend in="SourceGraphic" operator="overlay" />
        </filter>
    </svg>

    <div class="mesh-bg">
        <div class="mesh-circle bg-rose-600 w-[1000px] h-[1000px] -top-[30%] -left-[10%] animate-pulse"></div>
        <div class="mesh-circle bg-indigo-600 w-[800px] h-[800px] bottom-0 -right-[5%] opacity-5"></div>
    </div>
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-10 py-10" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">
        <div class="max-w-[1800px] mx-auto rounded-[3.5rem] px-12 py-6 flex items-center justify-between transition-all duration-1000"
             :class="scrolled ? 'glass-ultra shadow-3xl py-5' : 'bg-transparent'">
            <div class="flex items-center gap-6">
                <img src="/img/logo/logo_small.png" alt="Logo" class="h-14 bg-black/5 p-2 rounded-2xl">
                <span class="text-4xl font-black tracking-tighter italic text-[#050505] uppercase">MOVIE<span class="text-rose-600">SHELF</span></span>
            </div>
            <div class="hidden md:flex items-center gap-16 text-[11px] font-black uppercase tracking-[0.5em] text-gray-500">
                <a href="#features" class="hover:text-black transition-colors">Features</a>
                <a href="#stats" class="hover:text-black transition-colors">Insights</a>
                <a href="/login" class="bg-[#050505] text-white px-12 py-5 rounded-full font-black hover:bg-rose-600 transition-all active:scale-90 shadow-2xl">
                    LAUNCH ACCESS
                </a>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="py-48 border-t border-black/5 relative overflow-hidden bg-white/40 glass-ultra">
        <div class="max-w-[1800px] mx-auto px-10 grid md:grid-cols-4 gap-32 relative z-10">
            <div class="space-y-10">
                <div class="flex items-center gap-5">
                    <img src="/img/logo/logo_small.png" alt="Logo" class="h-10 bg-black/5 p-2 rounded-xl">
                    <span class="font-black tracking-tighter italic uppercase text-3xl text-[#050505]">MovieShelf</span>
                </div>
                <p class="text-gray-400 text-lg font-semibold leading-relaxed">
                    Elevating movie collections to a cinematic cloud experience. <br>
                    Pure Platinum Edition v2.10.2
                </p>
            </div>
            <div class="md:col-span-2"></div>
            <div class="flex flex-col items-end gap-12">
                <div class="flex gap-8">
                    <a href="#" class="w-16 h-16 bg-black/5 rounded-[2rem] flex items-center justify-center text-gray-400 hover:bg-rose-600 hover:text-white transition-all"><i class="bi bi-github text-3xl"></i></a>
                    <a href="#" class="w-16 h-16 bg-black/5 rounded-[2rem] flex items-center justify-center text-gray-400 hover:bg-rose-600 hover:text-white transition-all"><i class="bi bi-twitter-x text-3xl"></i></a>
                </div>
                <div class="text-gray-400 text-[11px] font-black uppercase tracking-[0.8em]">
                    © 2026 RENÉ NEUHAUS • PLATINUM PROTOCOL
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
