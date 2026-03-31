@props([
    'compact' => false,
    'isStreaming' => false
])

<footer class="relative z-10 animate-in fade-in slide-in-from-bottom-4 duration-1000 
    {{ $isStreaming ? 'mt-32 pb-20 px-12 md:px-20 border-t border-white/5 bg-gradient-to-t from-[#0c0c0e] to-transparent' : ($compact ? 'mt-10 pb-6 px-8' : 'mt-20 pb-10 px-8') }}">
    
    <div class="{{ $isStreaming ? 'max-w-[1400px]' : 'max-w-7xl' }} mx-auto flex flex-col items-center {{ ($compact || $isStreaming) ? 'gap-8' : 'gap-12' }}">
        
        <!-- Navigation Links -->
        <nav class="flex flex-wrap justify-center gap-4 md:gap-12">
            @php
                $navClass = "text-[10px] font-black uppercase tracking-[0.2em] transition-all flex items-center gap-2 " . 
                           ($isStreaming ? "text-white/40 hover:text-blue-400" : "text-gray-500 hover:text-blue-400");
            @endphp

            <a href="{{ route('dashboard', ['impressum' => 1]) }}"
               @click.prevent="if (window.location.pathname === '/' || window.location.pathname === '/dashboard') { $dispatch('impressum-open') } else { window.location.href = $el.href }"
               class="{{ $navClass }}">
                <i class="bi bi-info-circle"></i>
                {{ __('Imprint') }}
            </a>
            
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

        @if(!$compact)
        <!-- Stats Section -->
        <div class="w-full grid grid-cols-2 md:grid-cols-4 gap-6">
            @php
                $statBoxClass = $isStreaming 
                    ? "bg-white/5 backdrop-blur-3xl border border-white/10 hover:bg-white/10" 
                    : "glass hover:bg-white/[0.03]";
            @endphp

            <div class="{{ $statBoxClass }} py-6 px-8 rounded-[2rem] flex flex-col items-center justify-center gap-3 transition-all group">
                <div class="w-10 h-10 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 group-hover:scale-110 transition-transform">
                    <i class="bi bi-collection text-xl"></i>
                </div>
                <div class="text-lg font-black text-white leading-none">{{ number_format($footerStats['total_films']) }}</div>
                <div class="text-[10px] font-bold {{ $isStreaming ? 'text-white/30' : 'text-gray-500' }} uppercase tracking-widest">{{ __('Movies') }}</div>
            </div>

            <div class="{{ $statBoxClass }} py-6 px-8 rounded-[2rem] flex flex-col items-center justify-center gap-3 transition-all group">
                <div class="w-10 h-10 rounded-2xl bg-purple-500/10 flex items-center justify-center text-purple-400 group-hover:scale-110 transition-transform">
                    <i class="bi bi-people text-xl"></i>
                </div>
                <div class="text-lg font-black text-white leading-none">{{ number_format($footerStats['total_actors']) }}</div>
                <div class="text-[10px] font-bold {{ $isStreaming ? 'text-white/30' : 'text-gray-500' }} uppercase tracking-widest">{{ __('Actors') }}</div>
            </div>

            <div class="{{ $statBoxClass }} py-6 px-8 rounded-[2rem] flex flex-col items-center justify-center gap-3 transition-all group">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
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
        @endif

        <!-- Copyright & Attribution -->
        <div class="flex {{ $compact ? 'flex-row justify-between w-full border-t border-white/5 pt-6 items-center' : 'flex-col items-center gap-8' }} text-center">
            <div class="flex items-center gap-6">
                <div class="px-4 py-1.5 bg-blue-600/10 border border-blue-500/20 rounded-full text-[10px] font-black text-blue-500 uppercase tracking-[0.3em]">
                    v{{ config('app.version') }}
                </div>
                <a href="https://github.com/lunasans/dvdprofiler.liste" target="_blank" class="text-white/20 hover:text-white transition-all transform hover:scale-110">
                    <i class="bi bi-github text-2xl"></i>
                </a>
            </div>

            <p class="text-[10px] font-black {{ $isStreaming ? 'text-white/20' : 'text-gray-600' }} uppercase tracking-[0.3em] leading-relaxed italic">
                &copy; {{ date('Y') }} René Neuhaus.<span class="{{ $compact ? 'hidden md:inline' : '' }}"> {{ __('All rights reserved.') }}</span><br class="{{ $compact ? 'hidden' : '' }}">
                <span class="{{ $compact ? 'hidden md:inline' : '' }}">{{ __('Hand-crafted with') }} <i class="bi bi-heart-fill text-rose-600 mx-2 animate-pulse"></i> {{ __('and') }} Laravel.</span>
            </p>

            <div class="max-w-md flex {{ $compact ? 'flex-row' : 'flex-col' }} items-center {{ $compact ? 'gap-6' : 'gap-3' }}">
                <span class="text-[8px] font-black {{ $isStreaming ? 'text-white/10' : 'text-gray-600' }} uppercase tracking-[0.4em] {{ $compact ? 'hidden' : '' }}">Powered by</span>
                <a href="https://www.themoviedb.org" target="_blank" class="opacity-30 hover:opacity-100 transition-opacity duration-700">
                    <img src="{{ asset('img/svg/tmdb_logo.svg') }}" 
                         alt="TMDB Logo" class="h-5 w-auto grayscale brightness-200">
                </a>
                <p class="text-[9px] {{ $isStreaming ? 'text-white/20' : 'text-gray-400' }} font-bold uppercase tracking-widest {{ $compact ? 'text-right max-w-[120px]' : 'text-center max-w-[320px] mt-1' }}">
                    {{ $compact ? 'TMDB API' : 'This product uses the TMDB API but is not endorsed or certified by TMDB.' }}
                </p>
            </div>
        </div>
    </div>
</footer>