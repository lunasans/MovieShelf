@extends('layouts.saas')

@section('content')
<style>
    :root {
        --void-black: #050505;
        --void-gray: #111111;
        --void-border: rgba(255, 255, 255, 0.08);
        --void-accent: #FF0032;
    }
    body { background-color: var(--void-black); color: white; }
    .bento-card {
        background: rgba(255, 255, 255, 0.02);
        backdrop-filter: blur(40px);
        -webkit-backdrop-filter: blur(40px);
        border: 1px solid var(--void-border);
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    }
    .blade-input {
        background: transparent;
        border-bottom: 2px solid var(--void-border);
        transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .blade-input:focus-within {
        border-bottom-color: var(--void-accent);
    }
    .monument-text {
        letter-spacing: -0.05em;
        line-height: 0.85;
        background: linear-gradient(to bottom, #ffffff 40%, #999999 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .glow-leak {
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 50% 0%, rgba(255, 0, 50, 0.08) 0%, transparent 70%);
        pointer-events: none;
        z-index: 0;
    }
    .animate-reveal {
        animation: reveal 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes reveal {
        from { opacity: 0; transform: translateY(30px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    [x-cloak] { display: none !important; }
</style>

<div class="glow-leak"></div>

<!-- Hero Section -->
<section class="relative pt-32 pb-20 px-6 min-h-screen flex items-center overflow-hidden z-10">
    <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-24 items-center relative z-10">
        
        <!-- Left Column: Monumental Content -->
        <div class="space-y-20 animate-reveal">
            <div class="inline-flex items-center gap-3 px-4 py-1.5 border border-white/5 bg-white/5 rounded-md text-[10px] font-black tracking-[0.6em] uppercase text-gray-500">
                <span class="w-1.5 h-1.5 bg-rose-600 rounded-full shadow-[0_0_10px_#ff0032]"></span>
                System Core v2.10
            </div>
            
            <h1 class="text-8xl md:text-[10rem] font-black uppercase italic monument-text tracking-tighter">
                MONUMENT <br>
                OF YOUR <br>
                <span class="italic font-light opacity-50">CINEMA.</span>
            </h1>
            
            <p class="text-xl text-gray-500 max-w-lg leading-relaxed font-medium tracking-tight">
                Ein digitales Denkmal für deine Filmsammlung. <br>
                Präzises Hosting. Monumentales Design. <br>
                Exklusiv für Sammler.
            </p>

            <!-- VOID COMMANDER -->
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
            }" class="space-y-16 pt-10">
                
                <form action="{{ route('tenant.register') }}" method="POST" class="relative">
                    @csrf
                    
                    <!-- THE BLADE INPUT -->
                    <div class="relative group max-w-4xl">
                        <div class="blade-input flex items-end pb-6 transition-all group-focus-within:pb-12">
                            <input type="text" 
                                    id="subdomain"
                                    name="subdomain" 
                                    x-model="subdomain" 
                                    @input.debounce.500ms="checkAvailability()"
                                    placeholder="IDENTITÄT WÄHLEN" 
                                    required 
                                    autocomplete="off"
                                    class="bg-transparent border-none focus:ring-0 text-white font-black w-full placeholder:text-gray-900 tracking-[-0.05em] text-4xl md:text-8xl uppercase italic p-0 ring-0 mt-2 outline-none">
                            
                            <div class="flex items-center gap-6 ml-4 pb-4">
                                <span class="text-gray-900 font-bold text-xl md:text-3xl hidden md:inline select-none">.MOVIESHELF.INFO</span>
                                <div class="flex items-center justify-center">
                                    <template x-if="checking">
                                        <div class="w-1.5 h-10 bg-rose-600 animate-pulse"></div>
                                    </template>
                                    <template x-if="!checking && available === true">
                                        <i class="bi bi-check-lg text-emerald-500 text-5xl animate-reveal"></i>
                                    </template>
                                    <template x-if="!checking && available === false">
                                        <i class="bi bi-x-lg text-rose-600 text-5xl animate-reveal"></i>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                        <div x-show="available === false" x-cloak class="mt-6 text-rose-600 font-black text-[12px] uppercase tracking-[0.6em] animate-reveal">
                            Besetzt • Identität bereits vergeben
                        </div>
                    </div>

                    <!-- BENTO CONFIGURATION -->
                    <div x-show="available === true" x-cloak x-transition:enter="transition ease-out duration-1000" x-transition:enter-start="opacity-0 translate-y-20 blur-xl" x-transition:enter-end="opacity-100 translate-y-0 blur-0" class="mt-32 grid grid-cols-1 md:grid-cols-6 gap-6 max-w-5xl">
                        
                        <div class="md:col-span-4 bento-card p-16 space-y-12 rounded-xl">
                            <div class="text-[11px] text-gray-500 font-black tracking-[0.6em] uppercase flex items-center gap-4">
                                <div class="w-1.5 h-1.5 bg-rose-600 rounded-full"></div>
                                Identity Verification
                            </div>
                            <div class="grid md:grid-cols-2 gap-10">
                                <input type="text" name="name" placeholder="FULL NAME" required autocomplete="name" class="bg-white/5 border border-white/5 p-8 text-xl text-white font-black uppercase italic focus:border-white/20 focus:ring-0 transition-all outline-none">
                                <input type="email" name="email" placeholder="EMAIL ADDRESS" required autocomplete="email" class="bg-white/5 border border-white/5 p-8 text-xl text-white font-black uppercase italic focus:border-white/20 focus:ring-0 transition-all outline-none">
                            </div>
                            <input type="text" name="username" placeholder="CODENAME (USERNAME)" required autocomplete="username" class="w-full bg-white/5 border border-white/5 p-8 text-xl text-white font-black uppercase italic focus:border-white/20 focus:ring-0 transition-all outline-none">
                        </div>

                        <div class="md:col-span-2 bento-card p-16 flex flex-col justify-between rounded-xl">
                            <div class="text-[11px] text-gray-500 font-black tracking-[0.6em] uppercase mb-12 flex items-center gap-4">
                                <div class="w-1.5 h-1.5 bg-rose-600 rounded-full"></div>
                                Access Key
                            </div>
                            <div class="space-y-6">
                                <input type="password" name="password" placeholder="PASSWORD" required autocomplete="new-password" class="w-full bg-white/5 border border-white/5 p-8 text-xl text-white font-black uppercase italic focus:border-white/20 focus:ring-0 transition-all outline-none">
                                <input type="password" name="password_confirmation" placeholder="RE-VERIFY" required autocomplete="new-password" class="w-full bg-white/5 border border-white/5 p-8 text-xl text-white font-black uppercase italic focus:border-white/20 focus:ring-0 transition-all outline-none">
                            </div>
                        </div>

                        <div class="md:col-span-6">
                            <button type="submit" class="w-full bg-white text-black py-12 font-black uppercase italic text-5xl tracking-tighter hover:bg-[#FF0032] hover:text-white transition-all duration-700 shadow-2xl">
                                INITIALIZE ENGINE
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Product Showcase -->
        <div class="relative hidden lg:block group">
            <div class="absolute -inset-20 bg-rose-900/10 blur-[150px] rounded-full pointer-events-none group-hover:opacity-20 transition-all duration-1000"></div>
            
            <div class="relative z-10 bento-card p-4 rounded-xl shadow-[0_100px_150px_-50px_rgba(0,0,0,1)] transform -rotate-1 group-hover:rotate-0 transition-all duration-1000 border-white/5">
                <div class="h-10 flex items-center px-6 gap-2 border-b border-white/5 bg-white/5">
                    <div class="w-2.5 h-2.5 rounded-full bg-white/10"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-white/10"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-white/10"></div>
                </div>
                <img src="{{ asset('img/screenshots/hero.png') }}" alt="MovieShelf Dashboard" class="w-full h-auto grayscale opacity-40 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-700">
            </div>
        </div>
    </div>
</section>

<!-- Bento Grid Showcase -->
<section class="py-60 px-6 relative z-10">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- Box 1: Analytics -->
        <div class="md:col-span-8 bento-card p-20 flex flex-col justify-end space-y-12 min-h-[600px] group overflow-hidden relative">
            <img src="{{ asset('img/screenshots/stats.png') }}" class="absolute top-0 right-0 w-2/3 h-auto grayscale opacity-20 transform translate-x-20 -translate-y-20 group-hover:grayscale-0 group-hover:opacity-40 transition-all duration-1000 pointer-events-none">
            
            <div class="relative z-10 space-y-6">
                <h2 class="text-6xl font-black italic uppercase monument-text">Insights. <br>In Echtzeit.</h2>
                <p class="text-xl text-gray-500 max-w-md font-medium tracking-tight border-l-2 border-rose-600 pl-8">Erhalte chirurgische Präzision über deine Sammlung. Medientypen, Genre-Analytics und Meilensteine.</p>
            </div>
        </div>

        <!-- Box 2: Retina Grid -->
        <div class="md:col-span-4 bento-card p-16 flex flex-col space-y-12 group bg-gradient-to-br from-rose-600/5 to-transparent">
            <div class="w-20 h-20 bg-white/5 border border-white/10 flex items-center justify-center rounded-xl">
                <i class="bi bi-grid-3x3-gap text-rose-600 text-4xl"></i>
            </div>
            <h2 class="text-4xl font-black italic uppercase monument-text">Retina <br>Cinematic Grid.</h2>
            <p class="text-gray-500 font-medium leading-relaxed tracking-tight">Nur pures, visuelles Kino-Feeling für deine gesamte Bibliothek. Keine Listen. Keine Tabellen. Nur Fokus.</p>
        </div>

        <!-- Final Push -->
        <div class="md:col-span-12 py-48 text-center space-y-16">
            <div class="w-px h-32 bg-gradient-to-b from-rose-600 to-transparent mx-auto"></div>
            <h2 class="text-7xl md:text-[10rem] font-black italic uppercase monument-text leading-none">Start Your <br>Monument.</h2>
            
            <div class="pt-12">
                <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="inline-flex items-center gap-10 p-1 border border-white/10 group">
                    <span class="bg-white text-black px-16 py-8 font-black uppercase italic text-3xl group-hover:bg-[#FF0032] group-hover:text-white transition-all">IDENTITY SICHERN</span>
                    <i class="bi bi-arrow-up-right text-white text-3xl mr-12 group-hover:translate-x-3 group-hover:-translate-y-3 transition-all"></i>
                </button>
            </div>
            
            <div class="pt-12 text-[11px] font-black uppercase tracking-[1em] text-gray-800">
                System Core v2.10.1 • Available Now
            </div>
        </div>
    </div>
</section>

@endsection
