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
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #050505; 
            color: white;
            overflow-x: hidden;
        }
        h1, h2, h3, h4 { font-family: 'Inter', sans-serif; }
        
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .mesh-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
            background: #050505;
        }
        
        .mesh-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.15;
        }

        .animate-reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .animate-reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="antialiased selection:bg-rose-600 selection:text-white">

    <div class="mesh-bg">
        <div class="mesh-circle bg-rose-600 w-[600px] h-[600px] -top-40 -left-40 animate-pulse"></div>
        <div class="mesh-circle bg-indigo-600 w-[500px] h-[500px] bottom-0 -right-20 opacity-10"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 contrast-150 brightness-150"></div>
    </div>
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-6" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">
        <div class="max-w-7xl mx-auto rounded-[2rem] px-8 py-4 flex items-center justify-between transition-all duration-500"
             :class="scrolled ? 'glass shadow-2xl py-3' : ''">
            <div class="flex items-center gap-4">
                <img src="/img/logo/logo_small.png" alt="Logo" class="h-10 drop-shadow-[0_0_15px_rgba(225,29,72,0.5)]">
                <span class="text-2xl font-black tracking-tighter italic">MOVIE<span class="text-rose-600">SHELF</span></span>
            </div>
            <div class="hidden md:flex items-center gap-10 text-[10px] font-black uppercase tracking-[0.3em] text-gray-500">
                <a href="#features" class="hover:text-white transition-colors">Features</a>
                <a href="#stats" class="hover:text-white transition-colors">Insights</a>
                <a href="/login" class="bg-white text-black px-8 py-3 rounded-full font-black hover:bg-rose-600 hover:text-white transition-all active:scale-95 shadow-xl">
                    LOGIN
                </a>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="py-20 border-t border-white/5 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-16 relative z-10">
            <div class="space-y-6">
                <div class="flex items-center gap-3">
                    <img src="/img/logo/logo_small.png" alt="Logo" class="h-6">
                    <span class="font-black tracking-tighter italic uppercase">MovieShelf</span>
                </div>
                <p class="text-gray-500 text-sm font-semibold leading-relaxed">
                    Elevating movie collections to a cinematic cloud experience.
                </p>
            </div>
            <div class="md:col-span-2"></div>
            <div class="flex flex-col items-end gap-6">
                <div class="flex gap-4">
                    <a href="#" class="w-12 h-12 glass rounded-2xl flex items-center justify-center hover:bg-rose-600 transition-all"><i class="bi bi-github"></i></a>
                    <a href="#" class="w-12 h-12 glass rounded-2xl flex items-center justify-center hover:bg-rose-600 transition-all"><i class="bi bi-twitter-x"></i></a>
                </div>
                <div class="text-gray-600 text-[10px] font-black uppercase tracking-[0.5em]">
                    © 2026 RENÉ NEUHAUS
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
