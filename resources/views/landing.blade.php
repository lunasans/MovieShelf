@extends('layouts.saas')

@section('content')
<style>
    :root {
        --platinum-bg: #F9F9FB;
        --platinum-border: rgba(255, 255, 255, 0.9);
        --platinum-accent: #FF0032;
        --platinum-text: #050505;
        --charcoal-mute: #888888;
    }
    body { background-color: var(--platinum-bg); color: var(--platinum-text); }
    
    .ultra-glass {
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(120px);
        -webkit-backdrop-filter: blur(120px);
        border: 1px solid var(--platinum-border);
        box-shadow: 0 100px 200px -50px rgba(0,0,0,0.1);
        filter: url(#platinum-grain);
        transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    .monument-text {
        letter-spacing: -0.07em;
        line-height: 0.75;
        background: linear-gradient(180deg, #050505 10%, #999999 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 30px 60px rgba(0,0,0,0.08));
    }
    
    .bg-parallax-text {
        position: absolute;
        font-size: 16rem;
        font-weight: 900;
        text-transform: uppercase;
        color: rgba(0,0,0,0.015);
        z-index: 0;
        pointer-events: none;
        white-space: nowrap;
        filter: blur(4px);
    }
    
    .ultra-slot {
        background: rgba(0, 0, 0, 0.04);
        border-radius: 5rem;
        box-shadow: inset 0 15px 40px rgba(0,0,0,0.06);
        transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }
    
    .ultra-slot:focus-within {
        background: #ffffff;
        box-shadow: inset 0 0 0 3px var(--platinum-accent), 0 80px 160px -30px rgba(0,0,0,0.15);
        transform: scale(1.03) translateY(-10px);
    }

    .platinum-input {
        background: transparent;
        border: none;
        outline: none;
    }

    .reveal-delay-1 { animation: reveal 2.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .reveal-delay-2 { animation: reveal 2.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    .reveal-delay-3 { animation: reveal 3.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    
    @keyframes reveal {
        from { opacity: 0; transform: translateY(120px) scale(0.93); filter: blur(40px); }
        to { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
    }
</style>

<!-- Parallax Background Architecture -->
<div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
    <div class="bg-parallax-text -top-20 -left-60 opacity-30" style="transform: translateY(calc(var(--scroll, 0) * -0.3px))">PLATINUM</div>
    <div class="bg-parallax-text top-1/2 -right-80 opacity-20 italic" style="transform: translateY(calc(var(--scroll, 0) * -0.6px))">ULTIMATE</div>
</div>

<!-- Hero Section: The VIP Apex -->
<section class="relative pt-80 pb-64 px-16 min-h-screen flex items-center z-10 overflow-hidden">
    <div class="max-w-[1900px] mx-auto w-full relative z-10 text-center">
        
        <div class="space-y-48 reveal-delay-1">
            <div class="inline-flex items-center gap-10 px-12 py-5 border border-black/5 bg-white/60 backdrop-blur-2xl rounded-full text-[13px] font-black tracking-[1.5em] uppercase text-gray-500 shadow-2xl" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                <span class="w-4 h-4 bg-rose-600 rounded-full animate-pulse shadow-xl shadow-rose-600/60"></span>
                ULTRA PREMIUM • ELITE PROTOCOL • V2.10.4
            </div>
            
            <h1 class="text-[6rem] md:text-[12rem] font-black uppercase italic monument-text tracking-[-0.09em] leading-[0.7] mb-32">
                PLATINUM <br>
                <span class="italic font-light opacity-15 tracking-[-0.02em]">APEX.</span>
            </h1>
            
            <div class="max-w-6xl mx-auto space-y-24">
                <p class="text-xl md:text-2xl text-gray-400 font-medium tracking-tighter leading-tight reveal-delay-2">
                    Jenseits von Exzellenz. Das absolute Maximum digitaler Sammlerkunst. <br>
                    Dein filmisches Monument wartet auf Initialisierung.
                </p>
                
                <!-- THE APEX COMMANDER -->
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
                }" class="pt-32 reveal-delay-3">
                    
                    <form action="{{ route('tenant.register') }}" method="POST" class="max-w-7xl mx-auto">
                        @csrf
                        
                        <div class="ultra-slot flex items-center p-8 md:p-12 relative" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                            <span class="text-gray-200 font-black text-2xl md:text-4xl italic select-none mr-12 opacity-30 tracking-[1em] md:tracking-[2em]">PROTO://</span>
                            
                            <input type="text" 
                                    id="subdomain"
                                    name="subdomain" 
                                    x-model="subdomain" 
                                    @input.debounce.1000ms="checkAvailability()"
                                    placeholder="WÄHLE DEINEN TITEL" 
                                    required 
                                    autocomplete="off"
                                    class="w-full text-[#050505] font-black text-4xl md:text-[8rem] uppercase italic platinum-input tracking-tighter p-0 focus:ring-0">
                            
                            <div class="flex items-center gap-16 ml-12">
                                <span class="text-gray-100 font-bold text-3xl md:text-5xl hidden lg:inline select-none tracking-[-0.1em]">.MSF.INFO</span>
                                <template x-if="checking">
                                    <div class="w-6 h-32 bg-rose-600 animate-pulse rounded-full shadow-[0_0_50px_rgba(225,29,72,0.5)]"></div>
                                </template>
                                <template x-if="!checking && available === true">
                                    <i class="bi bi-star-fill text-emerald-500 text-6xl md:text-[8rem] animate-reveal"></i>
                                </template>
                                <template x-if="!checking && available === false">
                                    <i class="bi bi-shield-lock-fill text-rose-600 text-6xl md:text-[8rem] animate-reveal"></i>
                                </template>
                            </div>
                        </div>

                        <!-- APEX BENTO - THE ULTIMATE REVEAL -->
                        <div x-show="available === true" x-cloak x-transition:enter="transition ease-out duration-2000" x-transition:enter-start="opacity-0 translate-y-96 scale-90 blur-[100px]" x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-0" class="mt-64 grid grid-cols-1 md:grid-cols-12 gap-16 text-left">
                            
                            <!-- Master Identity -->
                            <div class="md:col-span-8 ultra-glass p-32 rounded-[5rem] space-y-24">
                                <div class="text-[16px] text-gray-500 font-black tracking-[2em] uppercase border-b border-black/5 pb-10">MASTER IDENTITY</div>
                                <div class="grid md:grid-cols-2 gap-20">
                                    <div class="space-y-8">
                                        <label class="text-[13px] font-black uppercase tracking-[1em] text-gray-400">Full Name</label>
                                        <input type="text" name="name" placeholder="MAX MUSTERMANN" required class="w-full bg-black/5 p-12 text-xl md:text-2xl text-[#050505] font-black uppercase italic rounded-[2rem] outline-none border border-transparent focus:border-rose-600/30 transition-all">
                                    </div>
                                    <div class="space-y-8">
                                        <label class="text-[13px] font-black uppercase tracking-[1em] text-gray-400">Secure Mail</label>
                                        <input type="email" name="email" placeholder="VIP@CINEMA.INFO" required class="w-full bg-black/5 p-12 text-xl md:text-2xl text-[#050505] font-black uppercase italic rounded-[2rem] outline-none border border-transparent focus:border-rose-600/30 transition-all">
                                    </div>
                                </div>
                            </div>

                            <!-- Authority Lock -->
                            <div class="md:col-span-4 ultra-glass p-32 rounded-[5rem] space-y-24">
                                <div class="text-[16px] text-gray-500 font-black tracking-[2em] uppercase border-b border-black/5 pb-10">LOCK</div>
                                <div class="space-y-12">
                                    <div class="space-y-6">
                                        <label class="text-[13px] font-black uppercase tracking-[1em] text-gray-400">Apex Key</label>
                                        <input type="password" name="password" placeholder="••••••••" required class="w-full bg-black/5 p-12 text-xl md:text-2xl text-[#050505] font-black uppercase italic rounded-[2rem] outline-none border border-transparent focus:border-rose-600/30 transition-all">
                                    </div>
                                </div>
                            </div>

                            <!-- INITIALIZE THE APEX -->
                            <div class="md:col-span-12 pt-20">
                                <button type="submit" class="w-full bg-[#050505] text-white py-16 md:py-20 font-black uppercase italic text-4xl md:text-6xl tracking-[-0.05em] hover:bg-[#FF0032] transition-all duration-1200 shadow-[0_150px_300px_-50px_rgba(0,0,0,0.3)] rounded-[3rem] md:rounded-[4rem] transform hover:-translate-y-8" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                                    INITIALIZE APEX
                                </button>
                                <div class="flex justify-center gap-48 mt-24 text-[16px] text-gray-400 font-black tracking-[3em] uppercase italic">
                                    <span>Infinite Power</span>
                                    <span>•</span>
                                    <span>Apex Core</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Immersive Ultimate Gallery -->
<section class="py-96 px-20 relative z-10" id="features">
    <div class="max-w-[1900px] mx-auto space-y-64">
        
        <div class="text-center space-y-16 animate-reveal">
            <h2 class="text-[6rem] md:text-[10rem] font-black italic uppercase monument-text tracking-tighter">The Gallery.</h2>
            <div class="w-[600px] h-3 bg-[#FF0032] mx-auto rounded-full shadow-[0_0_100px_rgba(225,29,72,0.8)]"></div>
        </div>
        
        <div class="md:col-span-12 ultra-glass min-h-[1000px] rounded-[8rem] relative overflow-hidden group shadow-4xl" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
            <img src="{{ asset('img/screenshots/hero.png') }}" class="absolute inset-0 w-full h-full object-cover transform scale-110 group-hover:scale-100 transition-all duration-3000 opacity-20 group-hover:opacity-100 filter contrast-125">
            <div class="absolute inset-0 bg-gradient-to-t from-white via-white/50 to-transparent"></div>
            <div class="absolute bottom-48 left-48 space-y-12 animate-reveal">
                <h3 class="text-4xl md:text-6xl font-black uppercase italic text-[#000] tracking-[-0.05em]">4K Retina Performance.</h3>
                <p class="text-xl md:text-2xl text-gray-500 font-medium max-w-5xl leading-tight">Keine Kompromisse. Nur pure Schärfe und Geschwindigkeit für deine exklusive Sammlung.</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-24 pb-80">
            @foreach([
                ['icon' => 'stack', 'title' => 'Infinite<br>Storage', 'text' => 'Skalierung bis an den Rand des Universums.', 'color' => 'bg-black'],
                ['icon' => 'shield-check', 'title' => 'VIP<br>Privacy', 'text' => 'Absolute Kontrolle. Deine Daten sind dein Monument.', 'color' => 'bg-rose-600'],
                ['icon' => 'cpu-fill', 'title' => 'Apex<br>Engine', 'text' => 'Zero-Latency Core für blitzschnelle Reaktionen.', 'color' => 'bg-indigo-600']
            ] as $item)
            <div class="ultra-glass p-32 rounded-[5rem] flex flex-col space-y-16 hover:-translate-y-12 transition-all duration-1000 group" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                <div class="w-40 h-40 {{ $item['color'] }} text-white flex items-center justify-center rounded-[3rem] shadow-4xl group-hover:rotate-12 transition-all duration-1000">
                    <i class="bi bi-{{ $item['icon'] }} text-7xl"></i>
                </div>
                <h4 class="text-3xl md:text-4xl font-black uppercase italic monument-text tracking-[-0.08em] leading-[0.8]">{!! $item['title'] !!}.</h4>
                <p class="text-gray-500 font-medium text-base md:text-lg tracking-tighter leading-relaxed">{{ $item['text'] }}</p>
            </div>
            @endforeach
        </div>

        <!-- THE ULTIMATE ASCENSION -->
        <div class="text-center space-y-64 py-96 relative overflow-hidden">
            <h2 class="text-[8rem] md:text-[14rem] font-black italic uppercase monument-text leading-none tracking-[-0.1em] opacity-90 reveal-delay-2">ASCEND.</h2>
            
            <div class="pt-48">
                <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
                        class="inline-flex items-center gap-32 p-6 border border-black/5 bg-white/60 backdrop-blur-3xl group hover:shadow-[0_150px_300px_rgba(0,0,0,0.25)] transition-all duration-1000 rounded-[5rem]"
                        @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                    <span class="bg-[#050505] text-white px-24 md:px-32 py-10 md:py-16 font-black uppercase italic text-3xl md:text-4xl group-hover:bg-[#FF0032] transition-all duration-1000 rounded-[3rem]">JOIN THE ELITE</span>
                    <i class="bi bi-arrow-up-right text-black text-4xl md:text-6xl mr-16 md:mr-32 group-hover:translate-x-8 group-hover:-translate-y-8 transition-all duration-1200"></i>
                </button>
            </div>
            
            <div class="pt-64 text-[18px] font-black uppercase tracking-[4em] text-gray-300">
                ULTRA PREMIUM • ELITE PROTOCOL • V2.10.4
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
