@php
    $layoutMode = auth()->user()->layout ?? 'classic';
@endphp

<div class="{{ $layoutMode === 'streaming' ? 'streaming-stats-view min-h-screen pt-32 pb-20 px-4 md:px-12 lg:px-24 relative' : 'space-y-8 p-8 overflow-y-auto no-scrollbar h-full' }}">
    
    @if($layoutMode === 'streaming')
        {{-- Background Elements --}}
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-[#0c0c0e]"></div>
            <div class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-blue-600/10 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[50%] h-[50%] bg-purple-600/10 rounded-full blur-[120px]"></div>
        </div>

        {{-- Header Section --}}
        <div class="relative z-10 mb-16 animate-in slide-in-from-left duration-700">
            <button @click="isStatsView = false" class="group inline-flex items-center gap-4 text-white/60 hover:text-white transition-all mb-8">
                <div class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center bg-white/5 backdrop-blur-xl group-hover:border-white/30 group-hover:scale-110 transition-all">
                    <i class="bi bi-arrow-left text-xl"></i>
                </div>
                <span class="font-black uppercase tracking-widest text-sm italic">{{ __('Back to Library') }}</span>
            </button>

            <h1 class="text-6xl md:text-8xl font-black text-white tracking-tighter mb-4 drop-shadow-2xl uppercase">
                {{ __('Statistics') }}
            </h1>
            <p class="text-white/40 text-lg font-bold max-w-2xl">
                {{ __('Detaillierte Analyse Ihrer :count Filme umfassenden Sammlung', ['count' => number_format($totalFilms)]) }}
            </p>
        </div>
    @else
        <!-- Classic Header -->
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="bi bi-bar-chart-fill text-xl"></i>
                </div>
                {{ __('Sammlungs-Statistiken') }}
            </h1>
            <p class="text-gray-400 font-medium">{{ __('Detaillierte Analyse Ihrer :count Filme umfassenden Sammlung', ['count' => number_format($totalFilms)]) }}</p>
        </div>
    @endif

    {{-- Metric Cards --}}
    <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 {{ $layoutMode === 'streaming' ? 'xl:grid-cols-5' : '2xl:grid-cols-5' }} gap-6 mb-12">
        {{-- Watched --}}
        <div class="{{ $layoutMode === 'streaming' ? 'glass-streaming p-8 rounded-[2.5rem]' : 'glass p-6 rounded-3xl' }} flex items-center gap-5 group hover:bg-blue-500/5 transition-all border border-blue-500/20 animate-in zoom-in duration-500 delay-100">
            <div class="w-14 h-14 rounded-2xl bg-blue-500/20 flex items-center justify-center text-blue-400 text-2xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="bi bi-eye-fill"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-white">{{ number_format($watchedFilms) }}</div>
                <div class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">{{ $watchedPercentage }}% {{ __('Gesehen') }}</div>
            </div>
        </div>

        {{-- Total Films --}}
        <div class="{{ $layoutMode === 'streaming' ? 'glass-streaming p-8 rounded-[2.5rem]' : 'glass p-6 rounded-3xl' }} flex items-center gap-5 group hover:bg-white/[0.03] transition-all border border-white/5 animate-in zoom-in duration-500 delay-200">
            <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center text-white/60 text-2xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="bi bi-collection-play"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-white">{{ number_format($totalFilms) }}</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">{{ __('Filme') }}</div>
            </div>
        </div>

        {{-- Runtime --}}
        <div class="{{ $layoutMode === 'streaming' ? 'glass-streaming p-8 rounded-[2.5rem]' : 'glass p-6 rounded-3xl' }} flex items-center gap-5 group hover:bg-emerald-500/5 transition-all border border-white/5 animate-in zoom-in duration-500 delay-300">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 text-2xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="bi bi-clock"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-white">{{ $days }} {{ __('Tage') }}</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">{{ __('Laufzeit') }}</div>
            </div>
        </div>

        {{-- Avg Year --}}
        <div class="{{ $layoutMode === 'streaming' ? 'glass-streaming p-8 rounded-[2.5rem]' : 'glass p-6 rounded-3xl' }} flex items-center gap-5 group hover:bg-purple-500/5 transition-all border border-white/5 animate-in zoom-in duration-500 delay-400">
            <div class="w-14 h-14 rounded-2xl bg-purple-500/10 flex items-center justify-center text-purple-400 text-2xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-white">{{ $yearStats->avg_year ?? date('Y') }}</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">{{ __('⌀ Jahr') }}</div>
            </div>
        </div>

        {{-- Genres Count --}}
        <div class="{{ $layoutMode === 'streaming' ? 'glass-streaming p-8 rounded-[2.5rem]' : 'glass p-6 rounded-3xl' }} flex items-center gap-5 group hover:bg-rose-500/5 transition-all border border-white/5 animate-in zoom-in duration-500 delay-500">
            <div class="w-14 h-14 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-400 text-2xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="bi bi-stars"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-white">{{ count($genres) }}</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">{{ __('Genres') }}</div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="relative z-10 grid grid-cols-1 {{ $layoutMode === 'streaming' ? 'lg:grid-cols-2' : '' }} gap-8 mb-12">
        {{-- Timeline Chart --}}
        <div class="{{ $layoutMode === 'streaming' ? 'glass-streaming p-10 rounded-[3rem]' : 'glass p-8 rounded-[2rem]' }} flex flex-col gap-6 border border-white/5 animate-in slide-in-from-bottom duration-700">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-4">
                    <div class="w-2 h-4 bg-blue-500 rounded-full"></div>
                    {{ __('Filme pro Jahr') }}
                </h3>
            </div>
            <div class="h-80">
                <canvas id="timelineChart"
                    data-chart-type="line"
                    data-labels='{!! json_encode(array_keys($yearDistribution->toArray())) !!}'
                    data-values='{!! json_encode(array_values($yearDistribution->toArray())) !!}'>
                </canvas>
            </div>
        </div>

        {{-- Genre Distribution --}}
        <div class="{{ $layoutMode === 'streaming' ? 'glass-streaming p-10 rounded-[3rem]' : 'glass p-8 rounded-[2rem]' }} flex flex-col gap-6 border border-white/5 animate-in slide-in-from-bottom duration-700 delay-100">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-4">
                    <div class="w-2 h-4 bg-purple-500 rounded-full"></div>
                    {{ __('Top 10 Genres') }}
                </h3>
            </div>
            <div class="h-80">
                <canvas id="genreChart"
                    data-chart-type="bar"
                    data-labels='{!! json_encode($genres->pluck('genre')) !!}'
                    data-values='{!! json_encode($genres->pluck('count')) !!}'>
                </canvas>
            </div>
        </div>

        {{-- Collection Type --}}
        <div class="{{ $layoutMode === 'streaming' ? 'glass-streaming p-10 rounded-[3rem]' : 'glass p-8 rounded-[2rem]' }} flex flex-col gap-6 border border-white/5 animate-in slide-in-from-bottom duration-700 delay-200">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-4">
                    <div class="w-2 h-4 bg-emerald-500 rounded-full"></div>
                    {{ __('Medientypen') }}
                </h3>
            </div>
            <div class="h-80">
                <canvas id="collectionChart"
                    data-chart-type="doughnut"
                    data-labels='{!! json_encode($collections->pluck('collection_type')) !!}'
                    data-values='{!! json_encode($collections->pluck('count')) !!}'>
                </canvas>
            </div>
        </div>

        {{-- Ratings Distribution --}}
        <div class="{{ $layoutMode === 'streaming' ? 'glass-streaming p-10 rounded-[3rem]' : 'glass p-8 rounded-[2rem]' }} flex flex-col gap-6 border border-white/5 animate-in slide-in-from-bottom duration-700 delay-300">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-4">
                    <div class="w-2 h-4 bg-rose-500 rounded-full"></div>
                    {{ __('Altersfreigaben') }}
                </h3>
            </div>
            <div class="h-80">
                <canvas id="ratingChart"
                    data-chart-type="polarArea"
                    data-labels='{!! json_encode($ratings->pluck('rating_age')->map(fn($age) => "FSK $age")) !!}'
                    data-values='{!! json_encode($ratings->pluck('count')) !!}'>
                </canvas>
            </div>
        </div>
    </div>

    {{-- Tables Section --}}
    <div class="relative z-10 grid grid-cols-1 {{ $layoutMode === 'streaming' ? 'lg:grid-cols-2' : '' }} gap-8">
        {{-- Decades --}}
        <div class="{{ $layoutMode === 'streaming' ? 'glass-streaming p-10 rounded-[3rem]' : 'glass p-8 rounded-[2rem]' }} border border-white/5 animate-in slide-in-from-bottom duration-700 delay-400">
            <h3 class="text-sm font-black text-white uppercase tracking-widest mb-8 flex items-center gap-4">
                <div class="w-2 h-4 bg-orange-400 rounded-full"></div>
                {{ __('Filme nach Dekaden') }}
            </h3>
            <div class="space-y-4">
                @foreach($decades as $decade)
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.05] hover:border-white/10 transition-all group">
                        <span class="text-base font-bold text-white group-hover:text-blue-400 transition-colors">{{ $decade->decade }}er</span>
                        <div class="flex items-center gap-6">
                            <span class="text-xs text-gray-500 font-bold uppercase tracking-widest">{{ number_format($decade->count) }} {{ __('Filme') }}</span>
                            <span class="px-3 py-1 bg-white/5 rounded-lg text-[10px] text-gray-400 font-black tracking-widest uppercase">⌀ {{ $decade->avg_runtime }} {{ __('Min') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Metadata --}}
        <div class="{{ $layoutMode === 'streaming' ? 'glass-streaming p-10 rounded-[3rem]' : 'glass p-8 rounded-[2rem]' }} border border-white/5 animate-in slide-in-from-bottom duration-700 delay-500">
            <h3 class="text-sm font-black text-white uppercase tracking-widest mb-8 flex items-center gap-4">
                <div class="w-2 h-4 bg-blue-400 rounded-full"></div>
                {{ __('Sammlungs-Metadaten') }}
            </h3>
            <div class="grid grid-cols-1 gap-6">
                <div class="flex flex-col gap-2 p-6 rounded-[2rem] bg-white/[0.02] border border-white/5">
                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ __('Ältester Film') }}</span>
                    <div class="text-2xl font-black text-white tracking-tight uppercase italic">{{ $yearStats->oldest_year ?? 'N/A' }}</div>
                </div>
                <div class="flex flex-col gap-2 p-6 rounded-[2rem] bg-white/[0.02] border border-white/5">
                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ __('Neuester Film') }}</span>
                    <div class="text-2xl font-black text-white tracking-tight uppercase italic">{{ $yearStats->newest_year ?? 'N/A' }}</div>
                </div>
                <div class="flex flex-col gap-2 p-6 rounded-[2rem] bg-white/[0.02] border border-white/5">
                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ __('Durchschnittliche Laufzeit') }}</span>
                    <div class="text-2xl font-black text-white tracking-tight uppercase italic">{{ $avgRuntime }} {{ __('Minuten') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($layoutMode === 'streaming')
<style>
    .glass-streaming {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(40px);
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.5),
            inset 0 1px 1px 0 rgba(255, 255, 255, 0.05);
    }
    .streaming-stats-view canvas {
        filter: drop-shadow(0 0 20px rgba(59, 130, 246, 0.1));
    }
</style>
@endif