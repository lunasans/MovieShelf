@extends('layouts.saas')

@section('content')
<style>
    :root {
        --platinum-bg: #F9F9FB;
        --platinum-border: rgba(255, 255, 255, 0.8);
        --platinum-accent: #FF0032;
        --platinum-text: #050505;
        --charcoal-mute: #666666;
    }
    body { background-color: var(--platinum-bg); color: var(--platinum-text); }
    
    .ultra-glass {
        background: rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(100px);
        -webkit-backdrop-filter: blur(100px);
        border: 1px solid var(--platinum-border);
        box-shadow: 0 80px 150px -40px rgba(0,0,0,0.08);
        filter: url(#platinum-grain);
    }
    
    .monument-text {
        letter-spacing: -0.06em;
        line-height: 0.8;
        background: linear-gradient(180deg, #050505 0%, #888888 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 20px 40px rgba(0,0,0,0.05));
    }
    
    .bg-parallax-text {
        position: absolute;
        font-size: 28rem;
        font-weight: 900;
        text-transform: uppercase;
        color: rgba(0,0,0,0.02);
        z-index: 0;
        pointer-events: none;
        white-space: nowrap;
        filter: blur(2px);
    }
    
    .ultra-slot {
        background: rgba(0, 0, 0, 0.03);
        border-radius: 4rem;
        box-shadow: inset 0 10px 30px rgba(0,0,0,0.05);
        transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }
    
    .ultra-slot:focus-within {
        background: #ffffff;
        box-shadow: inset 0 0 0 2px var(--platinum-accent), 0 60px 120px -20px rgba(0,0,0,0.1);
        transform: scale(1.02);
    }

    .platinum-input {
        background: transparent;
        border: none;
        outline: none;
    }

    .reveal-delay-1 { animation: reveal 1.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .reveal-delay-2 { animation: reveal 2.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    
    @keyframes reveal {
        from { opacity: 0; transform: translateY(100px) scale(0.95); filter: blur(30px); }
        to { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
    }
</style>

<!-- Parallax Background Architecture -->
<div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
    <div class="bg-parallax-text -top-10 -left-20 opacity-20" style="transform: translateY(calc(var(--scroll, 0) * -0.2px))">PLATINUM</div>
    <div class="bg-parallax-text top-1/2 -right-40 opacity-10 italic" style="transform: translateY(calc(var(--scroll, 0) * -0.5px))">ULTRA</div>
</div>

<!-- Hero Section: The Horizon -->
<section class="relative pt-64 pb-48 px-12 min-h-screen flex items-center z-10 overflow-hidden">
    <div class="max-w-[1800px] mx-auto w-full relative z-10 text-center">
        
        <div class="space-y-40 reveal-delay-1">
            <div class="inline-flex items-center gap-8 px-10 py-4 border border-black/5 bg-white/50 backdrop-blur-md rounded-full text-[11px] font-black tracking-[1.25em] uppercase text-gray-500">
                <span class="w-3 h-3 bg-rose-600 rounded-full animate-pulse shadow-lg shadow-rose-600/50"></span>
                Ultra Protocol v2.10.2 / Infinite Horizon
            </div>
            
            <h1 class="text-[12rem] md:text-[22rem] font-black uppercase italic monument-text tracking-[-0.08em] leading-none mb-20">
                MONUMENT <br>
                <span class="italic font-light opacity-20">ULTRA.</span>
            </h1>
            
            <div class="max-w-4xl mx-auto space-y-16">
                <p class="text-4xl text-gray-500 font-medium tracking-tight leading-relaxed">
                    Die Architektur der Exzellenz. Reinheit in ihrer extremsten Form. <br>
                    Dein filmisches Erbe verdient ein Denkmal ohne Grenzen.
                </p>
                
                <!-- THE ULTRA MONOLITH SLOT -->
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
                }" class="pt-20">
                    
                    <form action="{{ route('tenant.register') }}" method="POST" class="max-w-6xl mx-auto">
                        @csrf
                        
                        <div class="ultra-slot flex items-center p-10 md:p-14 relative">
                            <span class="text-gray-300 font-black text-4xl italic select-none mr-10 opacity-40">PLATINUM://</span>
                            
                            <input type="text" 
                                    id="subdomain"
                                    name="subdomain" 
                                    x-model="subdomain" 
                                    @input.debounce.800ms="checkAvailability()"
                                    placeholder="IDENTITÄT WÄHLEN" 
                                    required 
                                    autocomplete="off"
                                    class="w-full text-[#050505] font-black text-5xl md:text-[11rem] uppercase italic platinum-input tracking-tighter p-0 focus:ring-0">
                            
                            <div class="flex items-center gap-10 ml-10">
                                <span class="text-gray-200 font-bold text-4xl hidden md:inline select-none tracking-tighter">.MOVIESHELF.INFO</span>
                                <template x-if="checking">
                                    <div class="w-4 h-24 bg-rose-600 animate-pulse rounded-full"></div>
                                </template>
                                <template x-if="!checking && available === true">
                                    <i class="bi bi-patch-check-fill text-emerald-500 text-9xl animate-reveal"></i>
                                </template>
                                <template x-if="!checking && available === false">
                                    <i class="bi bi-shield-slash-fill text-rose-600 text-9xl animate-reveal"></i>
                                </template>
                            </div>
                        </div>

                        <!-- ULTRA BENTO REVEAL -->
                        <div x-show="available === true" x-cloak x-transition:enter="transition ease-out duration-1500" x-transition:enter-start="opacity-0 translate-y-64 scale-90 blur-3xl" x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-0" class="mt-48 grid grid-cols-1 md:grid-cols-12 gap-12 text-left">
                            
                            <!-- Identity Block -->
                            <div class="md:col-span-8 ultra-glass p-24 rounded-[4rem] space-y-20">
                                <div class="text-[14px] text-gray-500 font-black tracking-[1.5em] uppercase border-b border-black/5 pb-10">CORE IDENTITY</div>
                                <div class="grid md:grid-cols-2 gap-16">
                                    <div class="space-y-6">
                                        <label class="text-[11px] font-black uppercase tracking-[0.8em] text-gray-400">Owner Name</label>
                                        <input type="text" name="name" placeholder="MAX MUSTERMANN" required class="w-full bg-black/5 p-12 text-4xl text-[#050505] font-black uppercase italic rounded-[2.5rem] outline-none">
                                    </div>
                                    <div class="space-y-6">
                                        <label class="text-[11px] font-black uppercase tracking-[0.8em] text-gray-400">Protocol Mail</label>
                                        <input type="email" name="email" placeholder="MAX@CINEMA.INFO" required class="w-full bg-black/5 p-12 text-4xl text-[#050505] font-black uppercase italic rounded-[2.5rem] outline-none">
                                    </div>
                                </div>
                            </div>

                            <!-- Authority Block -->
                            <div class="md:col-span-4 ultra-glass p-24 rounded-[4rem] space-y-20">
                                <div class="text-[14px] text-gray-500 font-black tracking-[1.5em] uppercase border-b border-black/5 pb-10">AUTHORITY</div>
                                <div class="space-y-8">
                                    <div class="space-y-4">
                                        <label class="text-[11px] font-black uppercase tracking-[0.8em] text-gray-400">Access Key</label>
                                        <input type="password" name="password" placeholder="••••••••" required class="w-full bg-black/5 p-12 text-4xl text-[#050505] font-black uppercase italic rounded-[2.5rem] outline-none">
                                    </div>
                                </div>
                            </div>

                            <!-- INITIALIZE MONUMENT -->
                            <div class="md:col-span-12 pt-16">
                                <button type="submit" class="w-full bg-[#050505] text-white py-20 font-black uppercase italic text-7xl tracking-[-0.05em] hover:bg-[#FF0032] transition-all duration-1000 shadow-[0_100px_200px_-50px_rgba(0,0,0,0.2)] rounded-[4rem] transform hover:-translate-y-4">
                                    INITIALIZE MONUMENT
                                </button>
                                <div class="flex justify-center gap-32 mt-20 text-[13px] text-gray-400 font-black tracking-[2em] uppercase italic">
                                    <span>Infinite Power</span>
                                    <span>•</span>
                                    <span>Platinum Core</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Full Width Immersive Showcase -->
<section class="py-96 px-12 relative z-10">
    <div class="max-w-[1900px] mx-auto space-y-48">
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-16 items-center">
            <div class="md:col-span-12 text-center space-y-12 mb-20 animate-reveal">
                <h2 class="text-[10rem] md:text-[18rem] font-black italic uppercase monument-text tracking-tight mb-10">The Gallery.</h2>
                <div class="w-[400px] h-2 bg-[#FF0032] mx-auto rounded-full"></div>
            </div>
            
            <div class="md:col-span-12 ultra-glass min-h-[900px] rounded-[6rem] relative overflow-hidden group">
                <img src="{{ asset('img/screenshots/hero.png') }}" class="absolute inset-0 w-full h-full object-cover transform scale-105 group-hover:scale-100 transition-all duration-2000 opacity-20 group-hover:opacity-100">
                <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
                <div class="absolute bottom-32 left-32 space-y-8 animate-reveal">
                    <h3 class="text-6xl font-black uppercase italic text-[#000]">Retina Engine Performance.</h3>
                    <p class="text-3xl text-gray-500 font-medium max-w-3xl">Purer Fokus. Absolute Präzision. Deine Sammlung in Platin gegossen.</p>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-16 pb-60">
            <div class="ultra-glass p-24 rounded-[4rem] flex flex-col space-y-12 hover:-translate-y-6 transition-all duration-700">
                <div class="w-32 h-32 bg-[#050505] text-white flex items-center justify-center rounded-[2.5rem] shadow-2xl">
                    <i class="bi bi-stack text-6xl"></i>
                </div>
                <h4 class="text-5xl font-black uppercase italic monument-text tracking-tighter">Scalable <br>Architecture.</h4>
                <p class="text-gray-500 font-medium text-2xl tracking-tight">Vom ersten Film bis zur unendlichen Bibliothek. Monumentale Skalierung ohne Kompromisse.</p>
            </div>
            
            <div class="ultra-glass p-24 rounded-[4rem] flex flex-col space-y-12 hover:-translate-y-6 transition-all duration-700">
                <div class="w-32 h-32 bg-[#FF0032] text-white flex items-center justify-center rounded-[2.5rem] shadow-2xl">
                    <i class="bi bi-shield-lock-fill text-6xl"></i>
                </div>
                <h4 class="text-5xl font-black uppercase italic monument-text tracking-tighter">Maximum <br>Authority.</h4>
                <p class="text-gray-500 font-medium text-2xl tracking-tight">Absolute Kontrolle über deine Identität. Deine Instanz, deine Regeln, deine Privatsphäre.</p>
            </div>

            <div class="ultra-glass p-24 rounded-[4rem] flex flex-col space-y-12 hover:-translate-y-6 transition-all duration-700">
                <div class="w-32 h-32 bg-indigo-600 text-white flex items-center justify-center rounded-[2.5rem] shadow-2xl">
                    <i class="bi bi-lightning-charge-fill text-6xl"></i>
                </div>
                <h4 class="text-5xl font-black uppercase italic monument-text tracking-tighter">Infinite <br>Performance.</h4>
                <p class="text-gray-500 font-medium text-2xl tracking-tight">Zero Lag. Pure Geschwindigkeit. Ein Erlebnis, das sich so schnell anfühlt wie Licht.</p>
            </div>
        </div>

        <!-- THE FINAL ASCENSION -->
        <div class="text-center space-y-48 py-80 relative overflow-hidden">
            <h2 class="text-[12rem] md:text-[24rem] font-black italic uppercase monument-text leading-none tracking-[-0.08em] opacity-80">ASCEND. <br>MONUMENT.</h2>
            
            <div class="pt-32">
                <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="inline-flex items-center gap-20 p-5 border border-black/5 bg-white/50 backdrop-blur-3xl group hover:shadow-[0_100px_200px_rgba(0,0,0,0.15)] transition-all rounded-[3rem]">
                    <span class="bg-[#050505] text-white px-32 py-16 font-black uppercase italic text-6xl group-hover:bg-[#FF0032] transition-all rounded-[2.5rem]">SIGN THE PROTOCOL</span>
                    <i class="bi bi-arrow-up-right text-black text-7xl mr-20 group-hover:translate-x-10 group-hover:-translate-y-10 transition-all duration-700"></i>
                </button>
            </div>
            
            <div class="pt-48 text-[14px] font-black uppercase tracking-[3em] text-gray-300">
                ULTRA PLATINUM ENGINE • INFINITE HORIZON EDITION
            </div>
        </div>
    </div>
</section>

<script>
    window.addEventListener('scroll', () => {
        document.body.style.setProperty('--scroll', window.pageYOffset);
    });
</script>

@endsection
