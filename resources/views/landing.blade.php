@extends('layouts.saas')

@section('content')
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
        <div class="space-y-10 animate-reveal">
            <div class="inline-flex items-center gap-3 px-5 py-2 glass rounded-full text-rose-500 text-[10px] font-black tracking-[0.4em] uppercase border-rose-500/20">
                <span class="w-2 h-2 bg-rose-600 rounded-full animate-pulse shadow-[0_0_15px_#e11d48]"></span>
                Next-Gen Cloud Engine
            </div>
            
            <h1 class="text-6xl md:text-8xl font-black tracking-tighter leading-[0.85] italic uppercase italic">
                Dein Herz. <br>
                <span class="text-rose-600 drop-shadow-[0_0_15px_rgba(225,29,72,0.3)]">Deine Filme.</span><br>
                Deine Cloud.
            </h1>
            
            <p class="text-xl text-gray-400 max-w-lg leading-relaxed font-semibold">
                Sichere dir dein persönliches digitales Filmregal. <br>
                Dedizierte Datenbank. Blitzschnelles Hosting. <br>
                Reinrassiges Cinematic-Design.
            </p>

            <!-- Browser-Styled Subdomain Input -->
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
            }" class="space-y-8">
                
                <form action="{{ route('tenant.register') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    @if ($errors->any())
                        <div class="p-6 glass border-rose-500/50 text-rose-500 rounded-[2rem] text-xs font-black uppercase tracking-widest leading-loose">
                            @foreach ($errors->all() as $error)
                                <div class="flex items-center gap-3"><i class="bi bi-exclamation-triangle-fill"></i>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Visual Browser Bar -->
                    <div class="glass p-2 rounded-[2.5rem] shadow-[0_50px_100px_-20px_rgba(225,29,72,0.15)] border-white/10 group focus-within:ring-2 focus-within:ring-rose-500/50 transition-all">
                        <div class="flex items-center px-6 h-16 md:h-20">
                             <div class="flex items-center gap-4 text-gray-700 font-black text-xl italic select-none mr-4">
                                <span class="hidden md:inline">https://</span>
                                <i class="bi bi-shield-lock-fill text-emerald-500/50"></i>
                             </div>
                             
                             <input type="text" 
                                    name="subdomain" 
                                    x-model="subdomain" 
                                    @input.debounce.500ms="checkAvailability()"
                                    placeholder="dein-name" 
                                    required 
                                    class="bg-transparent border-none focus:ring-0 text-white font-black w-full placeholder:text-gray-800 tracking-tighter text-2xl md:text-4xl uppercase italic p-0">
                             
                             <div class="flex items-center gap-4">
                                <template x-if="checking">
                                    <i class="bi bi-arrow-repeat animate-spin text-rose-500 text-2xl"></i>
                                </template>
                                <template x-if="!checking && available === true">
                                    <i class="bi bi-check-circle-fill text-emerald-500 text-2xl animate-bounce"></i>
                                </template>
                                <template x-if="!checking && available === false">
                                    <i class="bi bi-x-circle-fill text-rose-600 text-2xl"></i>
                                </template>
                                <span class="text-gray-700 font-black text-lg md:text-xl hidden md:inline select-none">.{{ parse_url(config('app.url'), PHP_URL_HOST) }}</span>
                             </div>
                        </div>
                    </div>

                    <!-- Revealed Expanded Form -->
                    <div x-show="available === true" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0" class="glass p-10 rounded-[3rem] space-y-8 border-rose-500/20">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-[0.4em] ml-2">Vollständiger Name</label>
                                <input type="text" name="name" placeholder="MAX MUSTERMANN" required class="w-full bg-white/5 border-white/10 rounded-2xl px-6 py-4 text-white font-black uppercase italic focus:ring-rose-500/50 placeholder:text-gray-800">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-[0.4em] ml-2">E-Mail Adresse</label>
                                <input type="email" name="email" placeholder="MAX@MAIL.DE" required class="w-full bg-white/5 border-white/10 rounded-2xl px-6 py-4 text-white font-black uppercase italic focus:ring-rose-500/50 placeholder:text-gray-800">
                            </div>
                        </div>
                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-[0.4em] ml-2">Username</label>
                                <input type="text" name="username" placeholder="SAMMLER7" required class="w-full bg-white/5 border-white/10 rounded-2xl px-6 py-4 text-white font-black uppercase italic focus:ring-rose-500/50 placeholder:text-gray-800">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-[0.4em] ml-2">Passwort</label>
                                <input type="password" name="password" placeholder="••••••••" required class="w-full bg-white/5 border-white/10 rounded-2xl px-6 py-4 text-white font-black uppercase italic focus:ring-rose-500/50 placeholder:text-gray-800">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-[0.4em] ml-2">Wiederholen</label>
                                <input type="password" name="password_confirmation" placeholder="••••••••" required class="w-full bg-white/5 border-white/10 rounded-2xl px-6 py-4 text-white font-black uppercase italic focus:ring-rose-500/50 placeholder:text-gray-800">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white py-6 rounded-2xl font-black uppercase italic text-2xl transition-all hover:scale-[1.02] active:scale-95 shadow-2xl shadow-rose-900/60 tracking-tighter">
                            JETZT INSTANZ AKTIVIEREN
                        </button>
                    </div>

                    <p x-show="available === false" x-cloak class="text-rose-500 text-[10px] font-black uppercase tracking-[0.5em] text-center">
                        <i class="bi bi-x-circle-fill mr-2"></i> Dieser Name ist leider schon vergeben.
                    </p>
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
