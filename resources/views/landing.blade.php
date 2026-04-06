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
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
    }
    
    .monument-text {
        letter-spacing: -0.02em;
        line-height: 1.2;
        color: #222222;
    }
    
    .bg-parallax-text {
        display: none;
    }
    
    .ultra-slot {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .ultra-slot:focus-within {
        border-color: var(--apex-accent);
        box-shadow: 0 0 0 4px rgba(204, 75, 6, 0.1);
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


<!-- Hero Section: The VIP Apex -->
<section class="relative pt-80 pb-64 px-16 min-h-screen flex items-center z-10 overflow-hidden">
    <div class="max-w-[1900px] mx-auto w-full relative z-10 text-center">
        
            <h1 class="text-4xl md:text-6xl font-extrabold text-[#222222] tracking-tight mb-8">
                MovieShelf Cloud
            </h1>
            <h2 class="text-2xl md:text-4xl font-bold text-gray-400 mb-20">
                Dein digitales Filmregal
            </h2>
            
            <div class="max-w-6xl mx-auto space-y-24">
                <p class="text-lg text-gray-500 font-medium max-w-2xl mx-auto leading-relaxed reveal-delay-2">
                    Organisiere, verwalte und teile deine Filmschätze mit einer modernen Oberfläche, die für Sammler entwickelt wurde.
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
                        
                        <div class="ultra-slot flex items-center p-4 md:p-6 relative border border-gray-200" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                            <span class="text-gray-300 font-bold text-sm md:text-base select-none mr-4 tracking-wider">https://</span>
                            
                            <input type="text" 
                                    id="subdomain"
                                    name="subdomain" 
                                    x-model="subdomain" 
                                    @input.debounce.1000ms="checkAvailability()"
                                    placeholder="your-name" 
                                    required 
                                    autocomplete="off"
                                    class="w-full text-[#222222] font-bold text-xl md:text-2xl placeholder-gray-200 platinum-input p-0 focus:ring-0 border-none">
                            
                            <div class="flex items-center gap-6 ml-4">
                                <span class="text-gray-400 font-bold text-sm md:text-lg hidden lg:inline select-none">.movieshelf.info</span>
                                <template x-if="checking">
                                    <div class="w-6 h-32 bg-rose-600 animate-pulse rounded-full shadow-[0_0_50px_rgba(225,29,72,0.5)]"></div>
                                </template>
                                <template x-if="!checking && available === true">
                                    <i class="bi bi-star-fill text-emerald-500 text-3xl md:text-4xl animate-reveal"></i>
                                </template>
                                <template x-if="!checking && available === false">
                                    <i class="bi bi-shield-lock-fill text-rose-600 text-3xl md:text-4xl animate-reveal"></i>
                                </template>
                            </div>
                        </div>

                        <!-- APEX BENTO - THE ULTIMATE REVEAL -->
                        <div x-show="available === true" x-cloak x-transition:enter="transition ease-out duration-2000" x-transition:enter-start="opacity-0 translate-y-96 scale-90 blur-[100px]" x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-0" class="mt-12 grid grid-cols-1 md:grid-cols-12 gap-16 text-left">
                            
                            <!-- Master Identity -->
                            <div class="md:col-span-8 ultra-glass p-16 md:p-24 rounded-[3rem] space-y-16">
                                <div class="text-[12px] text-gray-400 font-bold uppercase tracking-widest border-b border-gray-100 pb-4 mb-12">MASTER IDENTITY</div>
                                <div class="grid md:grid-cols-2 gap-12">
                                    <div class="space-y-4">
                                        <label class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Vorname Nachname</label>
                                        <input type="text" name="name" placeholder="Max Mustermann" required class="w-full bg-gray-50/50 border border-gray-100 p-3 md:p-4 text-base md:text-lg text-[#222222] font-medium rounded-lg outline-none focus:border-orange-600/30 focus:ring-4 focus:ring-orange-600/5 transition-all">
                                    </div>
                                    <div class="space-y-4">
                                        <label class="text-[11px] font-bold uppercase tracking-wider text-gray-400">E-Mail</label>
                                        <input type="email" name="email" placeholder="info@movieshelf.info" required class="w-full bg-gray-50/50 border border-gray-100 p-3 md:p-4 text-base md:text-lg text-[#222222] font-medium rounded-lg outline-none focus:border-orange-600/30 focus:ring-4 focus:ring-orange-600/5 transition-all">
                                    </div>
                                </div>
                            </div>

                            <!-- Authority Lock -->
                            <div class="md:col-span-4 ultra-glass p-16 md:p-24 rounded-lg space-y-12">
                                <div class="text-[12px] text-gray-400 font-bold uppercase tracking-widest border-b border-gray-100 pb-4 mb-12">LOCK</div>
                                <div class="space-y-8">
                                    <div class="space-y-4">
                                        <label class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Passwort</label>
                                        <input type="password" name="password" placeholder="••••••••" required class="w-full bg-gray-50/50 border border-gray-100 p-3 md:p-4 text-base md:text-lg text-[#222222] font-medium rounded-lg outline-none focus:border-orange-600/30 focus:ring-4 focus:ring-orange-600/5 transition-all">
                                    </div>
                                </div>
                            </div>

                            <!-- INITIALIZE THE APEX -->
                            <div class="md:col-span-12 pt-20">
                                <button type="submit" class="w-full bg-[#CC4B06] text-white py-4 md:py-6 font-bold uppercase text-[11px] tracking-widest hover:bg-[#A33C05] transition-all duration-300 rounded-[8px] shadow-lg shadow-orange-600/20 transform hover:-translate-y-1" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                                    INITIALIZE CLOUD
                                </button>
                                <div class="flex justify-center gap-16 mt-12 text-[10px] font-bold uppercase tracking-widest text-gray-300">
                                    <span>Cloud Performance</span>
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
            <h2 class="text-3xl md:text-5xl font-extrabold text-[#222222] tracking-tight">The Experience.</h2>
            <div class="w-24 h-1 bg-[#CC4B06] mx-auto rounded-full mt-6"></div>
        </div>
        
        <div class="md:col-span-12 ultra-glass min-h-[1000px] rounded-[8rem] relative overflow-hidden group shadow-4xl" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
            <img src="{{ asset('img/screenshots/hero.png') }}" class="absolute inset-0 w-full h-full object-cover transform scale-110 group-hover:scale-100 transition-all duration-3000 opacity-20 group-hover:opacity-100 filter contrast-125">
            <div class="absolute inset-0 bg-gradient-to-t from-white via-white/50 to-transparent"></div>
            <div class="absolute bottom-48 left-48 space-y-12 animate-reveal">
                <h3 class="text-xl md:text-2xl font-bold text-[#222222] mb-4"></h3>
                <p class="text-gray-500 font-medium max-w-lg mx-auto"></p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-24 pb-80">
            @foreach([
                ['icon' => 'stack', 'title' => 'Infinite<br>Storage', 'text' => '.', 'color' => 'bg-black'],
                ['icon' => 'shield-check', 'title' => 'VIP<br>Privacy', 'text' => '', 'color' => 'bg-rose-600'],
                ['icon' => 'cpu-fill', 'title' => 'Apex<br>Engine', 'text' => '', 'color' => 'bg-indigo-600']
            ] as $item)
            <div class="ultra-glass p-32 rounded-[5rem] flex flex-col space-y-16 hover:-translate-y-12 transition-all duration-1000 group" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                <div class="w-40 h-40 {{ $item['color'] }} text-white flex items-center justify-center rounded-[3rem] shadow-4xl group-hover:rotate-12 transition-all duration-1000">
                    <i class="bi bi-{{ $item['icon'] }} text-7xl"></i>
                </div>
                <h4 class="text-xl md:text-2xl font-black uppercase italic monument-text tracking-[-0.05em] leading-tight">{!! $item['title'] !!}.</h4>
                <p class="text-gray-500 font-medium text-sm md:text-base tracking-tighter leading-relaxed">{{ $item['text'] }}</p>
            </div>
            @endforeach
        </div>

        <!-- CTA Section -->
        <div class="text-center space-y-12 py-32">
            <h2 class="text-4xl md:text-6xl font-extrabold text-[#222222] tracking-tight">Ready to start?</h2>
            
            <div class="pt-12">
                <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
                        class="inline-flex items-center gap-6 px-10 py-4 bg-[#CC4B06] text-white group hover:shadow-xl transition-all duration-300 rounded-[8px]"
                        @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                    <span class="font-bold uppercase text-[11px] tracking-widest">JOIN THE CLOUD</span>
                    <i class="bi bi-arrow-right text-lg group-hover:translate-x-2 transition-all"></i>
                </button>
            </div>
            
            <div class="pt-16 text-[10px] font-bold uppercase tracking-[0.4em] text-gray-300">
                MovieShelf Cloud • V2.10.4
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
