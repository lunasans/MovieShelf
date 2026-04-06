@extends('layouts.saas')

@section('content')
<style>
    .glass-dark {
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .shadow-glow {
        box-shadow: 0 0 30px rgba(225, 29, 72, 0.3);
    }
    .animate-gradient {
        background-size: 200% 200%;
        animation: gradient-move 4s ease infinite;
    }
    @keyframes gradient-move {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    [x-cloak] { display: none !important; }
</style>

<!-- Hero Section -->
<section class="relative pt-32 pb-20 px-6 min-h-screen flex items-center overflow-hidden">
    <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center relative z-10">
        
        <!-- Flash Messages -->
        <div class="col-span-full mb-10 translate-y-8 animate-reveal">
            @if (session('success'))
                <div class="glass p-8 border-emerald-500/50 text-emerald-400 rounded-[2.5rem] flex items-center gap-6 shadow-[0_20px_50px_rgba(16,185,129,0.1)]">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 flex items-center justify-center text-2xl">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-[10px] font-black uppercase tracking-[0.4em] opacity-50 mb-1">System Message</div>
                        <div class="text-lg font-bold tracking-tight uppercase italic leading-tight">{{ session('success') }}</div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="glass p-8 border-rose-500/50 text-rose-400 rounded-[2.5rem] flex items-center gap-6 shadow-[0_20px_50px_rgba(225,29,72,0.1)]">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/20 flex items-center justify-center text-2xl">
                        <i class="bi bi-exclamation-octagon"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-[10px] font-black uppercase tracking-[0.4em] opacity-50 mb-1">Attention Required</div>
                        <div class="text-lg font-bold tracking-tight uppercase italic leading-tight">{{ session('error') }}</div>
                    </div>
                </div>
            @endif

            @if (session('info'))
                <div class="glass p-8 border-indigo-500/50 text-indigo-400 rounded-[2.5rem] flex items-center gap-6 shadow-[0_20px_50px_rgba(99,102,241,0.1)]">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-2xl">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-[10px] font-black uppercase tracking-[0.4em] opacity-50 mb-1">Information</div>
                        <div class="text-lg font-bold tracking-tight uppercase italic leading-tight">{{ session('info') }}</div>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Left Column: High-Impact Content -->
        <div class="space-y-12 animate-reveal">
            <div class="inline-flex items-center gap-4 px-6 py-2.5 glass-dark rounded-full text-rose-500 text-[12px] font-black tracking-[0.5em] uppercase border-rose-500/30">
                <span class="w-2.5 h-2.5 bg-rose-600 rounded-full animate-pulse shadow-[0_0_20px_#e11d48]"></span>
                Next-Gen Cloud Engine v2.0
            </div>
            
            <h1 class="text-7xl md:text-9xl font-black tracking-tighter leading-[0.8] italic uppercase text-white">
                Dein Herz. <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-600 to-rose-400 drop-shadow-[0_15px_30px_rgba(225,29,72,0.4)]">Deine Filme.</span><br>
                Deine Freiheit.
            </h1>
            
            <p class="text-2xl text-gray-400/80 max-w-xl leading-relaxed font-bold tracking-tight">
                Erlebe die exklusivste Form deiner Filmsammlung. <br>
                Eigene Datenbank. Cinematic Branding. <br>
                Absolut <span class="text-white italic">privat.</span>
            </p>


            <!-- --- CINEMATIC COMMAND CENTER --- -->
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
            }" class="space-y-12">
                
                <form action="{{ route('tenant.register') }}" method="POST" class="space-y-12 relative">
                    @csrf
                    
                    @if ($errors->any())
                        <div class="p-8 glass-dark border-rose-600/50 text-rose-500 rounded-[2.5rem] text-sm font-black uppercase tracking-widest leading-loose shadow-[0_0_50px_rgba(225,29,72,0.2)]">
                            @foreach ($errors->all() as $error)
                                <div class="flex items-center gap-4"><i class="bi bi-exclamation-triangle-fill"></i>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <!-- THE FLOATING COMMAND BAR -->
                    <div class="relative group">
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-rose-600 via-rose-400 to-rose-600 rounded-[3rem] blur-md opacity-20 group-focus-within:opacity-60 transition duration-1000 group-focus-within:duration-200"></div>
                        <div class="relative bg-black/40 backdrop-blur-3xl p-4 rounded-[3rem] shadow-2xl border border-white/10 transition-all duration-500">
                            <div class="flex items-center h-20 md:h-32 px-10">
                                <div class="flex items-center gap-6 text-gray-700 font-black text-2xl italic select-none mr-8">
                                    <span class="hidden md:inline opacity-10">HTTPS://</span>
                                    <div class="w-1.5 h-10 bg-white/5 rounded-full"></div>
                                    <i class="bi bi-shield-lock-fill text-emerald-500/40"></i>
                                </div>
                                
                                <div class="flex-1 relative">
                                    <input type="text" 
                                            id="subdomain"
                                            name="subdomain" 
                                            x-model="subdomain" 
                                            @input.debounce.500ms="checkAvailability()"
                                            placeholder="RESERVIERE-DEIN-SHELF" 
                                            required 
                                            autocomplete="off"
                                            class="bg-transparent border-none focus:ring-0 text-white font-black w-full placeholder:text-gray-900 tracking-tighter text-3xl md:text-7xl uppercase italic p-0 mb-2">
                                    
                                    <!-- PROGRESS BAR INDICATOR -->
                                    <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-rose-600 to-rose-400 transition-all duration-1000" :style="'width: ' + (subdomain.length > 0 ? (Math.min(subdomain.length / 15 * 100, 100)) : 0) + '%'"></div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-8 ml-8">
                                    <span class="text-gray-900 font-black text-2xl md:text-3xl hidden md:inline select-none">.MOVIESHELF.INFO</span>
                                    <div class="w-20 h-20 rounded-[2rem] bg-white/5 border border-white/10 flex items-center justify-center shadow-inner group-focus-within:border-rose-500/50 transition-all group-focus-within:bg-rose-500/5">
                                        <template x-if="checking">
                                            <i class="bi bi-arrow-repeat animate-spin text-rose-500 text-4xl"></i>
                                        </template>
                                        <template x-if="!checking && available === true">
                                            <i class="bi bi-check2 text-emerald-500 text-5xl animate-reveal shadow-glow"></i>
                                        </template>
                                        <template x-if="!checking && available === false">
                                            <i class="bi bi-x text-rose-600 text-5xl animate-reveal"></i>
                                        </template>
                                        <template x-if="available === null && !checking">
                                            <div class="w-4 h-4 bg-white/10 rounded-full animate-pulse"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- PREMIUM TAKEN MESSAGE -->
                        <div x-show="available === false" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="absolute -bottom-10 left-12 z-20">
                            <div class="bg-black/80 backdrop-blur-md border border-rose-600/30 text-rose-500 px-8 py-4 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.5)] flex items-center gap-4">
                                <span class="w-2 h-2 bg-rose-600 rounded-full animate-ping"></span>
                                <span class="text-[11px] font-black uppercase tracking-[0.5em] italic">Identität bereits vergeben</span>
                            </div>
                        </div>
                    </div>

                    <!-- --- THE PREMIUM CONFIGURATOR CARD --- -->
                    <div x-show="available === true" x-cloak x-transition:enter="transition ease-out duration-1000 delay-100" x-transition:enter-start="opacity-0 scale-95 -translate-y-20 blur-xl" x-transition:enter-end="opacity-100 scale-100 translate-y-0 blur-0" class="relative z-20">
                        <div class="absolute -inset-1 bg-gradient-to-b from-rose-600/20 to-transparent rounded-[4rem] blur-2xl"></div>
                        <div class="relative bg-black/60 backdrop-blur-3xl p-16 rounded-[4rem] border border-white/5 shadow-[0_100px_150px_-50px_rgba(0,0,0,1)] overflow-hidden">
                            <!-- Background Glows -->
                            <div class="absolute top-0 right-0 w-96 h-96 bg-rose-600/10 blur-[130px] rounded-full -translate-y-1/2 translate-x-1/2"></div>
                            <div class="absolute bottom-0 left-0 w-64 h-64 bg-rose-600/5 blur-[100px] rounded-full translate-y-1/2 -translate-x-1/2"></div>
                            
                            <div class="relative z-10 space-y-16">
                                <!-- Heading Block -->
                                <div class="flex items-center gap-10">
                                    <div class="w-24 h-24 bg-gradient-to-br from-rose-700 to-rose-600 rounded-3xl flex items-center justify-center text-white text-5xl shadow-[0_20px_40px_rgba(225,29,72,0.4)] border border-rose-500/50">
                                        <i class="bi bi-layers-half"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-5xl font-black uppercase italic tracking-tighter text-white leading-none">Setup abschließen</h3>
                                        <p class="text-rose-500/60 font-black text-sm tracking-[0.5em] uppercase mt-2">Personalize Your Cinematic Engine</p>
                                    </div>
                                </div>

                                <!-- Personal Identity Grid -->
                                <div class="grid md:grid-cols-2 gap-12">
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3 ml-6 mb-1">
                                            <i class="bi bi-person-circle text-rose-500/50"></i>
                                            <label for="reg_name" class="text-[11px] font-black text-gray-500 uppercase tracking-[0.6em]">Voller Name</label>
                                        </div>
                                        <input type="text" id="reg_name" name="name" placeholder="MAX MUSTERMANN" required autocomplete="name" class="w-full bg-white/5 border-white/10 rounded-3xl px-10 py-7 text-2xl text-white font-black uppercase italic focus:ring-0 focus:border-rose-500/50 focus:bg-white/10 placeholder:text-gray-900 transition-all duration-500">
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3 ml-6 mb-1">
                                            <i class="bi bi-envelope-at text-rose-500/50"></i>
                                            <label for="reg_email" class="text-[11px] font-black text-gray-500 uppercase tracking-[0.6em]">Privat Mail</label>
                                        </div>
                                        <input type="email" id="reg_email" name="email" placeholder="MAX@CINEMA.INFO" required autocomplete="email" class="w-full bg-white/5 border-white/10 rounded-3xl px-10 py-7 text-2xl text-white font-black uppercase italic focus:ring-0 focus:border-rose-500/50 focus:bg-white/10 placeholder:text-gray-900 transition-all duration-500">
                                    </div>
                                </div>
                                
                                <!-- Security Grid -->
                                <div class="grid md:grid-cols-3 gap-10 bg-white/5 p-10 rounded-[3rem] border border-white/5">
                                    <div class="space-y-4">
                                        <label for="reg_username" class="text-[10px] font-black text-gray-600 uppercase tracking-[0.5em] ml-4">Codename</label>
                                        <input type="text" id="reg_username" name="username" placeholder="SAMMLER-01" required autocomplete="username" class="w-full bg-black/40 border-white/10 rounded-2xl px-8 py-6 text-xl text-white font-black uppercase italic focus:ring-0 focus:border-rose-600/50 placeholder:text-gray-900 transition-all">
                                    </div>
                                    <div class="space-y-4">
                                        <label for="reg_password" class="text-[10px] font-black text-gray-600 uppercase tracking-[0.5em] ml-4">Security Key</label>
                                        <input type="password" id="reg_password" name="password" placeholder="••••••••" required autocomplete="new-password" class="w-full bg-black/40 border-white/10 rounded-2xl px-8 py-6 text-xl text-white font-black uppercase italic focus:ring-0 focus:border-rose-600/50 placeholder:text-gray-900 transition-all">
                                    </div>
                                    <div class="space-y-4">
                                        <label for="reg_password_confirmation" class="text-[10px] font-black text-gray-600 uppercase tracking-[0.5em] ml-4">Verify Key</label>
                                        <input type="password" id="reg_password_confirmation" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password" class="w-full bg-black/40 border-white/10 rounded-2xl px-8 py-6 text-xl text-white font-black uppercase italic focus:ring-0 focus:border-rose-600/50 placeholder:text-gray-900 transition-all">
                                    </div>
                                </div>

                                <!-- HIGH IMPACT LAUNCH BUTTON -->
                                <div class="pt-6">
                                    <button type="submit" class="group relative w-full overflow-hidden rounded-[2.5rem] p-px transition-all hover:scale-[1.01] active:scale-95 shadow-[0_50px_100px_-20px_rgba(225,29,72,0.5)]">
                                        <div class="absolute inset-0 animate-gradient bg-gradient-to-r from-rose-800 via-rose-500 to-rose-800 bg-[length:200%_auto]"></div>
                                        <div class="relative bg-rose-600 group-hover:bg-transparent transition-colors py-10 rounded-[2.5rem] flex items-center justify-center gap-6">
                                            <span class="text-white font-black uppercase italic text-4xl tracking-tighter">JETZT INSTANZ STARTEN</span>
                                            <i class="bi bi-chevron-right text-3xl group-hover:translate-x-3 transition-transform duration-500"></i>
                                        </div>
                                    </button>
                                    <p class="text-center mt-8 text-[11px] font-black uppercase tracking-[0.8em] text-gray-700 animate-pulse">
                                        Ready For Destination • Dedicated Engine v2.0
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                </form>
            </div>
        </div>

        <!-- Right Column: Product Showcase -->
        <div class="relative hidden lg:block group">
            <!-- Background Glow -->
            <div class="absolute -inset-20 bg-rose-600/30 blur-[150px] rounded-full pointer-events-none group-hover:bg-rose-600/40 transition-all duration-1000"></div>
            
            <!-- MacOS Style Browser Window -->
            <div class="relative z-10 glass p-2 rounded-[2.5rem] shadow-[0_80px_150px_-30px_rgba(0,0,0,0.8)] border border-white/10 transform -rotate-1 group-hover:rotate-0 transition-all duration-1000">
                <!-- Window Header -->
                <div class="h-10 flex items-center px-6 gap-2 border-b border-white/5">
                    <div class="w-3 h-3 rounded-full bg-[#FF5F56]"></div>
                    <div class="w-3 h-3 rounded-full bg-[#FFBD2E]"></div>
                    <div class="w-3 h-3 rounded-full bg-[#27C93F]"></div>
                    <div class="ml-4 flex-1 glass rounded-md h-5 opacity-30"></div>
                </div>
                <img src="{{ asset('img/screenshots/hero.png') }}" alt="MovieShelf Dashboard" class="w-full h-auto">
                <!-- Window Footer / Status Bar (Static) -->
                <div class="h-8 flex items-center px-6 border-t border-white/5 bg-white/5 rounded-b-[2rem] justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_8px_#10b981]"></div>
                        <span class="text-[8px] font-black uppercase tracking-[0.2em] text-emerald-500/80">Cloud Native Engine</span>
                    </div>
                    <div class="text-[8px] font-black uppercase tracking-[0.2em] text-gray-600">
                        v.2.10.1 Stable
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Insights & Grid Showcase -->
<section class="py-40 px-6 relative" id="stats">
    <div class="max-w-7xl mx-auto space-y-48">
        
        <!-- Box 1: Analytics -->
        <div class="grid lg:grid-cols-2 gap-32 items-center">
            <div class="glass p-2 shadow-2xl rounded-[3rem] animate-reveal">
                <div class="h-10 flex items-center px-6 gap-2 border-b border-white/5">
                    <div class="w-2 h-2 rounded-full bg-white/20"></div>
                    <div class="w-2 h-2 rounded-full bg-white/20"></div>
                    <div class="w-2 h-2 rounded-full bg-white/20"></div>
                </div>
                <img src="{{ asset('img/screenshots/stats.png') }}" alt="Insights" class="rounded-b-[2.5rem] w-full h-auto">
            </div>
            <div class="space-y-8 animate-reveal">
                <h2 class="text-6xl font-black italic uppercase leading-tight tracking-tighter">
                    DEINE SAMMLUNG <br><span class="text-rose-600">IN ECHTZEIT.</span>
                </h2>
                <p class="text-xl text-gray-400 font-semibold leading-relaxed">
                    MovieShelf liefert dir tiefe Einblicke. <br>
                    Genre-Verteilung, Medientypen-Analytics <br>
                    und Sammler-Meilensteine auf einen Klick.
                </p>
                <div class="flex gap-8 text-[11px] font-black uppercase tracking-[0.4em] text-rose-500">
                    <div class="flex items-center gap-2"><i class="bi bi-activity"></i> LIVE API</div>
                    <div class="flex items-center gap-2"><i class="bi bi-pie-chart"></i> INSIGHTS</div>
                </div>
            </div>
        </div>

        <!-- Box 2: Cinema Grid -->
        <div class="grid lg:grid-cols-2 gap-32 items-center">
            <div class="space-y-8 animate-reveal lg:order-1 order-2">
                <h2 class="text-6xl font-black italic uppercase leading-tight tracking-tighter">
                    CINEMATIC <br><span class="text-rose-600">LAYOUT ENGINE.</span>
                </h2>
                <p class="text-xl text-gray-400 font-semibold leading-relaxed">
                    Keine Listen. Keine Tabellen. <br>
                    Nur pures, visuelles Kino-Feeling für <br>
                    deine gesamte Bibliothek.
                </p>
                <div class="flex gap-8 text-[11px] font-black uppercase tracking-[0.4em] text-rose-500">
                    <div class="flex items-center gap-2"><i class="bi bi-image"></i> RETINA GRID</div>
                    <div class="flex items-center gap-2"><i class="bi bi-phone"></i> RESPONSIVE</div>
                </div>
            </div>
            <div class="glass p-2 shadow-2xl rounded-[3rem] animate-reveal order-1 lg:order-2">
                <div class="h-10 flex items-center px-6 gap-2 border-b border-white/5">
                    <div class="w-2 h-2 rounded-full bg-white/20"></div>
                    <div class="w-2 h-2 rounded-full bg-white/20"></div>
                    <div class="w-2 h-2 rounded-full bg-white/20"></div>
                </div>
                <img src="{{ asset('img/screenshots/grid.png') }}" alt="Grid View" class="rounded-b-[2.5rem] w-full h-auto">
            </div>
        </div>

    </div>
</section>

<!-- Final Push -->
<section class="py-40 px-6">
    <div class="max-w-5xl mx-auto">
        <div class="glass p-20 md:p-32 rounded-[5rem] text-center space-y-12 border-rose-600/30 border-2 relative overflow-hidden group">
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-rose-600/10 blur-[120px] rounded-full group-hover:scale-150 transition-all duration-1000"></div>
            <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/10 blur-[120px] rounded-full group-hover:scale-150 transition-all duration-1000"></div>
            
            <h2 class="text-7xl md:text-9xl font-black italic uppercase leading-[0.8] tracking-tighter relative z-10">
                START YOUR <br><span class="text-rose-600">ENGINE.</span>
            </h2>
            <p class="text-xl text-gray-400 font-semibold max-w-xl mx-auto relative z-10">
                Werde Teil der exklusiven Sammler-Elite. <br>In unter 30 Sekunden online.
            </p>
            
            <div class="relative z-10 flex flex-col items-center gap-10">
                <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="bg-rose-600 hover:bg-rose-500 text-white px-16 py-7 rounded-3xl font-black uppercase italic text-3xl transition-all hover:scale-110 active:scale-95 shadow-[0_20px_50px_rgba(225,29,72,0.4)] tracking-tighter">
                    DOMAIN SICHERN
                </button>
                <div class="text-[10px] font-black uppercase tracking-[0.6em] text-gray-700">
                    No Credit Card Required • Instant Activation
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
