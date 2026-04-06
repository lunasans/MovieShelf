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
        
        .glass {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05);
        }

        .mesh-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
            background: var(--platinum-bg);
        }
        
        .mesh-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.08;
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
        <div class="mesh-circle bg-rose-600 w-[800px] h-[800px] -top-80 -left-60 animate-pulse"></div>
        <div class="mesh-circle bg-indigo-600 w-[600px] h-[600px] bottom-0 -right-40 opacity-5"></div>
    </div>
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-6" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">
        <div class="max-w-7xl mx-auto rounded-[2.5rem] px-10 py-5 flex items-center justify-between transition-all duration-700"
             :class="scrolled ? 'glass shadow-2xl py-4' : 'bg-transparent'">
            <div class="flex items-center gap-5">
                <img src="/img/logo/logo_small.png" alt="Logo" class="h-12 bg-black/5 p-1 rounded-lg">
                <span class="text-3xl font-black tracking-tighter italic text-[#050505]">MOVIE<span class="text-rose-600">SHELF</span></span>
            </div>
            <div class="hidden md:flex items-center gap-12 text-[10px] font-black uppercase tracking-[0.4em] text-gray-500">
                <a href="#features" class="hover:text-black transition-colors">Features</a>
                <a href="#stats" class="hover:text-black transition-colors">Insights</a>
                <a href="/login" class="bg-[#050505] text-white px-10 py-4 rounded-full font-black hover:bg-rose-600 transition-all active:scale-95 shadow-xl">
                    LOGIN
                </a>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="py-32 border-t border-black/5 relative overflow-hidden bg-white/50">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-24 relative z-10">
            <div class="space-y-8">
                <div class="flex items-center gap-4">
                    <img src="/img/logo/logo_small.png" alt="Logo" class="h-8 bg-black/5 p-1 rounded-md">
                    <span class="font-black tracking-tighter italic uppercase text-2xl text-[#050505]">MovieShelf</span>
                </div>
                <p class="text-gray-400 text-sm font-semibold leading-relaxed">
                    Elevating movie collections to a cinematic cloud experience.
                </p>
            </div>
            <div class="md:col-span-2"></div>
            <div class="flex flex-col items-end gap-10">
                <div class="flex gap-6">
                    <a href="#" class="w-14 h-14 bg-black/5 rounded-2xl flex items-center justify-center text-gray-400 hover:bg-rose-600 hover:text-white transition-all"><i class="bi bi-github text-2xl"></i></a>
                    <a href="#" class="w-14 h-14 bg-black/5 rounded-2xl flex items-center justify-center text-gray-400 hover:bg-rose-600 hover:text-white transition-all"><i class="bi bi-twitter-x text-2xl"></i></a>
                </div>
                <div class="text-gray-400 text-[10px] font-black uppercase tracking-[0.6em]">
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
