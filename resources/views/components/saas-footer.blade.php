@props([
    'isLanding' => false
])

<footer class="relative z-10 mt-32 pb-20 px-8 border-t border-white/5">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-12 pt-12">
            <!-- SaaS Branding -->
            <div class="flex flex-col items-center lg:items-start gap-4">
                <div class="flex items-center gap-4 group">
                    <x-application-logo class="h-10 w-auto drop-shadow-md group-hover:scale-110 transition-all duration-500" />
                    <div>
                        <h2 class="text-xl font-black text-white uppercase tracking-tight leading-none group-hover:text-rose-500 transition-colors">
                            {{ \App\Models\Setting::get('site_title', 'MovieShelf') }} <span class="text-rose-600">Cloud</span>
                        </h2>
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-[0.2em] mt-2 italic">
                            Next-Gen Media Engine
                        </p>
                    </div>
                </div>
            </div>

            <!-- Minimal Links -->
            <nav class="flex flex-wrap justify-center gap-8 md:gap-12">
                @php
                    $navClass = "text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 hover:text-rose-500 transition-all flex items-center gap-2";
                @endphp

                <a href="#" class="{{ $navClass }}">
                    <i class="bi bi-info-circle"></i>
                    {{ __('Imprint') }}
                </a>
                
                @if(!request()->routeIs('login'))
                <a href="{{ route('login') }}" class="{{ $navClass }}">
                    <i class="bi bi-person"></i>
                    Admin Console
                </a>
                @endif
            </nav>

            <!-- Status / Version -->
            <div class="flex flex-col items-center lg:items-end gap-2">
                <div class="px-4 py-1.5 bg-white/5 border border-white/10 rounded-full text-[9px] font-black text-rose-500 uppercase tracking-[0.3em] flex items-center gap-2">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-rose-500"></span>
                    </span>
                    SaaS Engine v{{ config('app.saas_version') }}
                </div>
                <p class="text-[9px] font-bold text-white/10 uppercase tracking-[0.3em] mt-2">
                    &copy; {{ date('Y') }} René Neuhaus.
                </p>
            </div>
        </div>
    </div>
</footer>
