@extends('layouts.saas')

@section('content')
<style>
    :root {
        --platinum-bg: #F9F9FB;
        --platinum-border: rgba(255, 255, 255, 0.6);
        --platinum-accent: #FF0032;
        --platinum-text: #050505;
        --charcoal-mute: #444444;
    }
    body { background-color: var(--platinum-bg); color: var(--platinum-text); }
    .bento-card {
        background: rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(40px);
        -webkit-backdrop-filter: blur(40px);
        border: 1px solid var(--platinum-border);
        box-shadow: 0 40px 100px -20px rgba(0,0,0,0.05);
    }
    .glass-blade {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(0,0,0,0.05);
        border-bottom: 2px solid rgba(0,0,0,0.1);
        transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .glass-blade:focus-within {
        background: rgba(255, 255, 255, 0.6);
        border-bottom-color: var(--platinum-accent);
        transform: translateY(-2px);
        box-shadow: 0 40px 80px -15px rgba(0,0,0,0.08);
    }
    .monument-text {
        letter-spacing: -0.05em;
        line-height: 0.85;
        background: linear-gradient(to bottom, #050505 40%, #666666 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .bloom-light {
        position: fixed;
        top: -10%; left: 0;
        width: 100%;
        height: 50%;
        background: radial-gradient(circle at 50% 0%, rgba(255, 0, 50, 0.03) 0%, transparent 70%);
        pointer-events: none;
        z-index: 0;
    }
    .animate-reveal {
        animation: reveal 1.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes reveal {
        from { opacity: 0; transform: translateY(60px) scale(0.97); filter: blur(15px); }
        to { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
    }
    [x-cloak] { display: none !important; }
    
    .platinum-input {
        background: rgba(0, 0, 0, 0.02);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.4s ease;
    }
    .platinum-input:focus {
        background: #ffffff;
        border-color: var(--platinum-accent);
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    }
</style>

<div class="bloom-light"></div>

<!-- Hero Section -->
<section class="relative pt-48 pb-32 px-10 min-h-screen flex items-center overflow-hidden z-10">
    <div class="max-w-[1600px] mx-auto grid lg:grid-cols-12 gap-24 items-center relative z-10 w-full">
        
        <!-- Left Column: Monumental Content -->
        <div class="lg:col-span-9 space-y-32 animate-reveal">
            <div class="inline-flex items-center gap-6 px-8 py-3 border border-black/5 bg-white shadow-sm rounded-full text-[10px] font-black tracking-[1em] uppercase text-gray-400">
                <span class="w-2.5 h-2.5 bg-rose-600 rounded-full animate-pulse"></span>
                Platinum Protocol v2.10
            </div>
            
            <h1 class="text-9xl md:text-[14rem] font-black uppercase italic monument-text tracking-tighter leading-[0.8]">
                BRIGHT <br>
                MONUMENT <br>
                <span class="italic font-light opacity-30">PLATINUM.</span>
            </h1>
            
            <p class="text-3xl text-[#666666] max-w-3xl leading-relaxed font-medium tracking-tight border-l-4 border-black/5 pl-12">
                Ein leuchtendes Denkmal für deine Privatsammlung. <br>
                Reinheit in Design. Monumentale Skalierung. <br>
                Exklusiv für den anspruchsvollen Sammler.
            </p>

            <!-- PLATINUM COMMANDER -->
            <div x-data="{ 
                subdomain: '{{ old('subdomain') }}', 
                available: {{ old('subdomain') ? 'true' : 'null' }}, 
                checking: false,
                async checkAvailability() {
                    if (this.subdomain.length < 3) {
                        this.available = null;
                        return;
                    }
                    this.checking = true;
                    try {
                        const response = await fetch('{{ route('api.check.subdomain') }}?name=' + this.subdomain);
                        const data = await response.json();
                        this.available = data.available;
                        this.subdomain = data.slug;
                    } catch (e) {
                        this.available = null;
                    } finally {
                        this.checking = false;
                    }
                }
            }" class="space-y-16 pt-16">
                
                <form action="{{ route('tenant.register') }}" method="POST" class="relative">
                    @csrf
                    
                    <!-- THE GLASS BLADE INPUT -->
                    <div class="relative group max-w-7xl">
                        <div class="glass-blade flex items-center p-6 md:p-12 rounded-[2rem] md:rounded-[3rem]">
                            <div class="flex items-center gap-8 text-gray-300 font-black text-3xl italic select-none mr-12">
                                <span class="hidden md:inline opacity-30 tracking-[0.5em]">PLATINUM://</span>
                            </div>
                            
                            <input type="text" 
                                    id="subdomain"
                                    name="subdomain" 
                                    x-model="subdomain" 
                                    @input.debounce.500ms="checkAvailability()"
                                    placeholder="IDENTITÄT WÄHLEN" 
                                    required 
                                    autocomplete="off"
                                    class="bg-transparent border-none focus:ring-0 text-[#050505] font-black w-full placeholder:text-gray-200 tracking-[-0.05em] text-5xl md:text-[10rem] uppercase italic p-0 ring-0 outline-none mt-4">
                            
                            <div class="flex items-center gap-10 ml-8">
                                <span class="text-gray-200 font-bold text-2xl md:text-5xl hidden md:inline select-none tracking-tighter">.MOVIESHELF.INFO</span>
                                <div class="flex items-center justify-center">
                                    <template x-if="checking">
                                        <div class="w-3 h-16 bg-rose-600 animate-pulse rounded-full"></div>
                                    </template>
                                    <template x-if="!checking && available === true">
                                        <i class="bi bi-check-lg text-emerald-500 text-8xl animate-reveal"></i>
                                    </template>
                                    <template x-if="!checking && available === false">
                                        <i class="bi bi-x-lg text-rose-600 text-8xl animate-reveal"></i>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                        <div x-show="available === false" x-cloak class="mt-12 text-rose-600 font-black text-sm uppercase tracking-[1em] animate-reveal flex items-center gap-6 pl-12">
                            <i class="bi bi-shield-x text-2xl"></i> Identität bereits vergeben
                        </div>
                    </div>

                    <!-- BENTO CONFIGURATION - BEYOND LIGHT -->
                    <div x-show="available === true" x-cloak x-transition:enter="transition ease-out duration-1000" x-transition:enter-start="opacity-0 translate-y-48 blur-3xl" x-transition:enter-end="opacity-100 translate-y-0 blur-0" class="mt-40 grid grid-cols-1 md:grid-cols-6 gap-10 max-w-[1600px]">
                        
                        <!-- Account Details -->
                        <div class="md:col-span-4 bento-card p-20 space-y-16 rounded-[3rem]">
                            <div class="text-[12px] text-gray-400 font-black tracking-[1em] uppercase flex items-center gap-8">
                                <div class="w-16 h-[1px] bg-rose-600/40"></div>
                                Verify Identity
                            </div>
                            <div class="grid md:grid-cols-2 gap-16">
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black uppercase tracking-[0.5em] text-gray-400 ml-6">Full Name</label>
                                    <input type="text" name="name" placeholder="MAX MUSTERMANN" required autocomplete="name" class="w-full platinum-input p-12 text-3xl text-[#050505] font-black uppercase italic rounded-[2rem] outline-none transition-all">
                                </div>
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black uppercase tracking-[0.5em] text-gray-400 ml-6">Mail Address</label>
                                    <input type="email" name="email" placeholder="MAX@CINEMA.INFO" required autocomplete="email" class="w-full platinum-input p-12 text-3xl text-[#050505] font-black uppercase italic rounded-[2rem] outline-none transition-all">
                                </div>
                            </div>
                            <div class="space-y-4">
                                <label class="text-[10px] font-black uppercase tracking-[0.5em] text-gray-400 ml-6">System Codename</label>
                                <input type="text" name="username" placeholder="SAMMLER_01" required autocomplete="username" class="w-full platinum-input p-12 text-3xl text-[#050505] font-black uppercase italic rounded-[2rem] outline-none transition-all">
                            </div>
                        </div>

                        <!-- Security Block -->
                        <div class="md:col-span-2 bento-card p-20 flex flex-col justify-between rounded-[3rem]">
                            <div class="text-[12px] text-gray-400 font-black tracking-[1em] uppercase mb-16 flex items-center gap-8">
                                <div class="w-16 h-[1px] bg-rose-600/40"></div>
                                Access
                            </div>
                            <div class="space-y-12">
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black uppercase tracking-[0.5em] text-gray-400 ml-6">Password</label>
                                    <input type="password" name="password" placeholder="••••••••" required autocomplete="new-password" class="w-full platinum-input p-12 text-3xl text-[#050505] font-black uppercase italic rounded-[2rem] outline-none transition-all">
                                </div>
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black uppercase tracking-[0.5em] text-gray-400 ml-6">Verify</label>
                                    <input type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password" class="w-full platinum-input p-12 text-3xl text-[#050505] font-black uppercase italic rounded-[2rem] outline-none transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- INITIALIZE ACTION -->
                        <div class="md:col-span-6 pt-16">
                            <button type="submit" class="w-full bg-[#050505] text-white py-16 font-black uppercase italic text-6xl tracking-tighter hover:bg-[#FF0032] transition-all duration-700 shadow-2xl rounded-[3rem]">
                                INITIALIZE INSTANCE
                            </button>
                            <div class="flex justify-center gap-24 mt-16 text-[11px] text-gray-400 font-black tracking-[1.2em] uppercase italic">
                                <span>Pure Platinum</span>
                                <span>•</span>
                                <span>Monument Core</span>
                                <span>•</span>
                                <span>Secure</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column (Showcase Floating) -->
        <div class="lg:col-span-3 hidden lg:block group">
            <div class="relative z-10 bento-card p-6 rounded-[4rem] shadow-[0_120px_180px_-40px_rgba(0,0,0,0.1)] transform rotate-3 group-hover:rotate-0 transition-all duration-1000 border-white/80">
                <img src="{{ asset('img/screenshots/hero.png') }}" alt="MovieShelf Dashboard" class="w-full h-auto grayscale opacity-20 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-1000">
            </div>
        </div>
    </div>
</section>

<!-- Insights Section -->
<section class="py-80 px-10 relative z-10">
    <div class="max-w-[1600px] mx-auto grid grid-cols-1 md:grid-cols-12 gap-12">
        
        <!-- Big Card 1 -->
        <div class="md:col-span-8 bento-card p-32 flex flex-col justify-end space-y-16 min-h-[800px] group overflow-hidden relative rounded-[5rem]">
            <img src="{{ asset('img/screenshots/stats.png') }}" class="absolute top-0 right-0 w-2/3 h-auto opacity-5 transform translate-x-20 -translate-y-20 group-hover:opacity-30 transition-all duration-1000 pointer-events-none">
            
            <div class="relative z-10 space-y-12">
                <h2 class="text-8xl font-black italic uppercase monument-text leading-none">Insights. <br>Pure Klarheit.</h2>
                <p class="text-3xl text-gray-500 max-w-2xl font-medium tracking-tight border-l-4 border-rose-600 pl-16">Präzision in jeder Statistik. Behalte den Überblick über dein filmisches Archiv mit monumentaler Einfachheit.</p>
            </div>
        </div>

        <!-- Small Card (Retina) -->
        <div class="md:col-span-4 bento-card p-24 flex flex-col space-y-16 group rounded-[5rem]">
            <div class="w-32 h-32 bg-[#050505] text-white flex items-center justify-center rounded-[2.5rem] shadow-2xl">
                <i class="bi bi-grid-3x3-gap text-6xl"></i>
            </div>
            <h2 class="text-6xl font-black italic uppercase monument-text leading-none">Retina <br>Gallery Layout.</h2>
            <p class="text-gray-500 font-medium text-2xl leading-relaxed tracking-tight">Purer Fokus auf das Cover. Keine Ablenkung. Nur dein Film, in seiner reinsten Form.</p>
        </div>

        <!-- The Final Stand -->
        <div class="md:col-span-12 py-80 text-center space-y-32">
            <div class="w-[300px] h-1.5 bg-gradient-to-r from-transparent via-rose-600/30 to-transparent mx-auto"></div>
            <h2 class="text-9xl md:text-[18rem] font-black italic uppercase monument-text leading-none tracking-[-0.08em]">Start Your <br>Monument.</h2>
            
            <div class="pt-20">
                <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="inline-flex items-center gap-16 p-3 border border-black/5 bg-white group hover:shadow-3xl transition-all rounded-full">
                    <span class="bg-[#050505] text-white px-24 py-12 font-black uppercase italic text-4xl group-hover:bg-[#FF0032] transition-all rounded-full">IDENTITÄT SICHERN</span>
                    <i class="bi bi-arrow-up-right text-black text-5xl mr-16 group-hover:translate-x-6 group-hover:-translate-y-6 transition-all"></i>
                </button>
            </div>
            
            <div class="pt-32 text-[12px] font-black uppercase tracking-[2em] text-gray-200">
                Platinum Engine v2.10.1 • Authorized Access Only
            </div>
        </div>
    </div>
</section>

@endsection
