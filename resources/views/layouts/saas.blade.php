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
            --platinum-bg: #F9F9FB;
            --platinum-text: #050505;
            --apex-accent: #FF0032;
        }
        
        * { cursor: none !important; } /* Hide system cursor for Apex Gallery */

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--platinum-bg); 
            color: var(--platinum-text);
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4 { font-family: 'Inter', sans-serif; }
        
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
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(120px);
            -webkit-backdrop-filter: blur(120px);
            border: 1px solid rgba(255, 255, 255, 0.8);
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

    <!-- GLOBAL GRAIN DEFINITION -->
    <svg id="grain-filter">
        <filter id="platinum-grain">
            <feTurbulence type="fractalNoise" baseFrequency="0.65" numOctaves="3" stitchTiles="stitch" />
            <feColorMatrix type="saturate" values="0" />
            <feComponentTransfer>
                <feFuncA type="linear" slope="0.06" />
            </feComponentTransfer>
            <feBlend in="SourceGraphic" operator="overlay" />
        </filter>
    </svg>

    <div class="mesh-bg">
        <div class="mesh-circle bg-rose-600 w-[1200px] h-[1200px] -top-[40%] -left-[15%] animate-pulse"></div>
        <div class="mesh-circle bg-indigo-600 w-[1000px] h-[1000px] bottom-0 -right-[10%] opacity-8"></div>
    </div>
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-12 py-12" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">
        <div class="max-w-[1900px] mx-auto rounded-[4rem] px-16 py-8 flex items-center justify-between transition-all duration-1200"
             :class="scrolled ? 'glass-ultra shadow-4xl py-6 scale-[0.98]' : 'bg-transparent'">
            <div class="flex items-center gap-8" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                <img src="/img/logo/logo_small.png" alt="Logo" class="h-16 bg-black/5 p-3 rounded-2xl">
                <span class="text-5xl font-black tracking-tighter italic text-[#050505] uppercase">MOVIE<span class="text-rose-600">SHELF</span></span>
            </div>
            <div class="hidden md:flex items-center gap-20 text-[12px] font-black uppercase tracking-[0.6em] text-gray-500">
                <a href="#features" class="hover:text-black transition-colors" @mouseenter="isHovering = true" @mouseleave="isHovering = false">Features</a>
                <a href="#stats" class="hover:text-black transition-colors" @mouseenter="isHovering = true" @mouseleave="isHovering = false">Insights</a>
                <a href="/login" class="bg-[#050505] text-white px-16 py-6 rounded-full font-black hover:bg-rose-600 transition-all active:scale-90 shadow-2xl" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                    PLATINUM ACCESS
                </a>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="py-64 border-t border-black/5 relative overflow-hidden bg-white/50 glass-ultra">
        <div class="max-w-[1900px] mx-auto px-12 grid md:grid-cols-4 gap-40 relative z-10">
            <div class="space-y-12">
                <div class="flex items-center gap-6">
                    <img src="/img/logo/logo_small.png" alt="Logo" class="h-12 bg-black/5 p-3 rounded-2xl">
                    <span class="font-black tracking-tighter italic uppercase text-4xl text-[#050505]">MovieShelf</span>
                </div>
                <p class="text-gray-400 text-xl font-semibold leading-relaxed">
                    Elevating movie collections to a cinematic cloud experience. <br>
                    Platinum Apex: Ultimate Edition v2.10.3
                </p>
            </div>
            <div class="md:col-span-2"></div>
            <div class="flex flex-col items-end gap-16">
                <div class="flex gap-10">
                    <a href="#" class="w-20 h-20 bg-black/5 rounded-[2.5rem] flex items-center justify-center text-gray-400 hover:bg-rose-600 hover:text-white transition-all"><i class="bi bi-github text-4xl"></i></a>
                    <a href="#" class="w-20 h-20 bg-black/5 rounded-[2.5rem] flex items-center justify-center text-gray-400 hover:bg-rose-600 hover:text-white transition-all"><i class="bi bi-twitter-x text-4xl"></i></a>
                </div>
                <div class="text-gray-400 text-[12px] font-black uppercase tracking-[1em]">
                    © 2026 RENÉ NEUHAUS • APEX ULTIMATE PROTOCOL
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
