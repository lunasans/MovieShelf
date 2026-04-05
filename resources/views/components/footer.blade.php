@props([
    'compact' => false,
    'isStreaming' => false
])

@php
    $footerStats = $footerStats ?? [
        'total_films' => 0,
        'total_actors' => 0,
        'total_genres' => 0,
        'daily_visits' => 0,
        'total_visits' => 0,
    ];
@endphp

<footer class="relative z-10 animate-in fade-in slide-in-from-bottom-4 duration-1000 
    {{ $isStreaming ? 'mt-32 pb-20 px-12 md:px-20 border-t border-white/5 bg-gradient-to-t from-[#0c0c0e] to-transparent' : ($compact ? 'py-10 px-12' : 'mt-20 pb-10 px-8') }}">
    
    <div class="{{ $isStreaming ? 'max-w-[1400px]' : ($compact ? 'w-full' : 'max-w-7xl') }} mx-auto">
        
        @if(!$compact)
        <div class="flex flex-col items-center gap-12 mb-12">
            <!-- Navigation Links -->
            <nav class="flex flex-wrap justify-center gap-4 md:gap-12">
                @php
                    $navClass = "text-[10px] font-black uppercase tracking-[0.2em] transition-all flex items-center gap-2 " . 
                               ($isStreaming ? "text-white/40 hover:text-rose-500" : "text-gray-500 hover:text-rose-500");
                @endphp

                @if(Route::has('dashboard'))
                <a href="{{ route('dashboard', ['impressum' => 1]) }}"
                   @click.prevent="if (window.location.pathname === '/' || window.location.pathname === '/dashboard') { $dispatch('impressum-open') } else { window.location.href = $el.href }"
                   class="{{ $navClass }}">
                    <i class="bi bi-info-circle"></i>
                    {{ __('Imprint') }}
                </a>
                @endif
                
                <a href="#" class="{{ $navClass }}">
                    <i class="bi bi-shield-lock"></i>
                    {{ __('Privacy') }}
                </a>

                @guest
                    <a href="{{ route('login') }}" class="{{ $navClass }}">
                        <i class="bi bi-person"></i>
                        Login
                    </a>
                @endguest
            </nav>

            <!-- Stats Section -->
            <div class="w-full grid grid-cols-2 md:grid-cols-4 gap-6">
                @php
                    $statBoxClass = $isStreaming 
                        ? "bg-white/5 backdrop-blur-3xl border border-white/10 hover:bg-white/10" 
                        : "glass hover:bg-white/[0.03]";
                @endphp

                <div class="{{ $statBoxClass }} py-6 px-8 rounded-[2rem] flex flex-col items-center justify-center gap-3 transition-all group">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform">
                        <i class="bi bi-collection text-xl"></i>
                    </div>
                    <div class="text-lg font-black text-white leading-none">{{ number_format($footerStats['total_films']) }}</div>
                    <div class="text-[10px] font-bold {{ $isStreaming ? 'text-white/30' : 'text-gray-500' }} uppercase tracking-widest">{{ __('Movies') }}</div>
                </div>

                <div class="{{ $statBoxClass }} py-6 px-8 rounded-[2rem] flex flex-col items-center justify-center gap-3 transition-all group">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform">
                        <i class="bi bi-people text-xl"></i>
                    </div>
                    <div class="text-lg font-black text-white leading-none">{{ number_format($footerStats['total_actors']) }}</div>
                    <div class="text-[10px] font-bold {{ $isStreaming ? 'text-white/30' : 'text-gray-500' }} uppercase tracking-widest">{{ __('Actors') }}</div>
                </div>

                <div class="{{ $statBoxClass }} py-6 px-8 rounded-[2rem] flex flex-col items-center justify-center gap-3 transition-all group">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform">
                        <i class="bi bi-tags text-xl"></i>
                    </div>
                    <div class="text-lg font-black text-white leading-none">{{ number_format($footerStats['total_genres']) }}</div>
                    <div class="text-[10px] font-bold {{ $isStreaming ? 'text-white/30' : 'text-gray-500' }} uppercase tracking-widest">Genres</div>
                </div>

                <div class="{{ $statBoxClass }} py-6 px-8 rounded-[2rem] flex flex-col items-center justify-center gap-3 transition-all group">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform">
                        <i class="bi bi-eye text-xl"></i>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="text-lg font-black text-white leading-none">{{ number_format($footerStats['daily_visits']) }}</div>
                        <div class="text-[9px] font-bold text-rose-500/60 uppercase tracking-widest mt-1">{{ __('Today') }}</div>
                    </div>
                    <div class="w-full h-px bg-white/5 my-1"></div>
                    <div class="flex items-center gap-3">
                        <div class="text-xs font-bold text-white/40 leading-none">{{ number_format($footerStats['total_visits']) }}</div>
                        <div class="text-[8px] font-black text-white/20 uppercase tracking-widest">{{ __('Total') }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Copyright & Attribution -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-8 md:gap-12 pt-8">
            <div class="flex items-center gap-8 order-2 md:order-1">
                <div class="group relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-rose-600 to-red-600 rounded-full blur opacity-20 group-hover:opacity-60 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative px-3 md:px-5 py-2 bg-black border border-white/10 rounded-full text-[9px] md:text-[10px] font-black text-white uppercase tracking-[0.3em] flex items-center gap-3">
                        <span class="text-rose-500">v</span>{{ config('app.version') }}
                    </div>
                </div>
                <a href="https://github.com/lunasans/dvdprofiler.liste" target="_blank" class="text-white/10 hover:text-white transition-all transform hover:scale-125">
                    <i class="bi bi-github text-xl md:text-2xl"></i>
                </a>
            </div>

            <div class="flex flex-col items-center md:items-start gap-2 order-1 md:order-2">
                <p class="text-[9px] md:text-[10px] font-black text-white/20 uppercase tracking-[0.4em] leading-relaxed italic text-center md:text-left">
                    &copy; {{ date('Y') }} René Neuhaus.<span class="hidden lg:inline ml-2">Alle Rechte vorbehalten.</span>
                </p>
                <p class="text-[8px] font-bold text-white/10 uppercase tracking-[0.3em] flex items-center gap-2">
                    Hand-crafted with <i class="bi bi-heart-fill text-rose-600 animate-pulse"></i> and Laravel.
                </p>
            </div>

            <div class="flex flex-col items-center md:items-end gap-1 order-3">
                <div class="text-right hidden sm:block mb-2">
                    <p class="text-[7px] font-black text-white/10 uppercase tracking-[0.4em] mb-1">Powered by</p>
                    <p class="text-[8px] text-white/20 font-bold uppercase tracking-[0.2em] max-w-[200px] leading-relaxed">
                        This product uses the TMDB API but is not endorsed or certified by TMDB.
                    </p>
                </div>
                <a href="https://www.themoviedb.org" target="_blank" class="opacity-10 hover:opacity-100 transition-all duration-700 hover:scale-110">
                    <img src="{{ asset('img/svg/tmdb_logo.svg') }}" 
                         alt="TMDB Logo" class="h-3 md:h-4 w-auto brightness-200 contrast-150">
                </a>
            </div>
        </div>
    </div>
</footer>