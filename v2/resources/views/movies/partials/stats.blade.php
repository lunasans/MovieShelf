<div class="space-y-8 p-8 overflow-y-auto no-scrollbar h-full">
    <!-- Header -->
    <div class="flex flex-col gap-2">
        <h1 class="text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="bi bi-bar-chart-fill text-xl"></i>
            </div>
            Sammlungs-Statistiken
        </h1>
        <p class="text-gray-400 font-medium">Detaillierte Analyse Ihrer {{ number_format($totalFilms) }} Filme umfassenden Sammlung</p>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 2xl:grid-cols-4 gap-6">
        <div class="glass p-6 rounded-3xl flex items-center gap-5 group hover:bg-white/[0.03] transition-all">
            <div class="w-14 h-14 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 text-2xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="bi bi-collection-play"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-white">{{ number_format($totalFilms) }}</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">Filme</div>
            </div>
        </div>

        <div class="glass p-6 rounded-3xl flex items-center gap-5 group hover:bg-white/[0.03] transition-all">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 text-2xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="bi bi-clock"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-white">{{ $days }} Tage</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">Laufzeit</div>
            </div>
        </div>

        <div class="glass p-6 rounded-3xl flex items-center gap-5 group hover:bg-white/[0.03] transition-all">
            <div class="w-14 h-14 rounded-2xl bg-purple-500/10 flex items-center justify-center text-purple-400 text-2xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-white">{{ $yearStats->avg_year ?? date('Y') }}</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">⌀ Jahr</div>
            </div>
        </div>

        <div class="glass p-6 rounded-3xl flex items-center gap-5 group hover:bg-white/[0.03] transition-all">
            <div class="w-14 h-14 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-400 text-2xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="bi bi-stars"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-white">{{ count($genres) }}</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">Genres</div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 gap-8">
        <!-- Timeline Chart -->
        <div class="glass p-8 rounded-[2rem] flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="bi bi-graph-up text-blue-400"></i>
                    Filme pro Jahr
                </h3>
            </div>
            <div class="h-64">
                <canvas id="timelineChart" 
                    data-chart-type="line"
                    data-labels='{!! json_encode(array_keys($yearDistribution->toArray())) !!}'
                    data-values='{!! json_encode(array_values($yearDistribution->toArray())) !!}'>
                </canvas>
            </div>
        </div>

        <!-- Genre Distribution -->
        <div class="glass p-8 rounded-[2rem] flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="bi bi-pie-chart text-purple-400"></i>
                    Top 10 Genres
                </h3>
            </div>
            <div class="h-64">
                <canvas id="genreChart"
                    data-chart-type="bar"
                    data-labels='{!! json_encode($genres->pluck('genre')) !!}'
                    data-values='{!! json_encode($genres->pluck('count')) !!}'>
                </canvas>
            </div>
        </div>

        <!-- Collection Type -->
        <div class="glass p-8 rounded-[2rem] flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="bi bi-disc text-emerald-400"></i>
                    Medientypen
                </h3>
            </div>
            <div class="h-64">
                <canvas id="collectionChart"
                    data-chart-type="doughnut"
                    data-labels='{!! json_encode($collections->pluck('collection_type')) !!}'
                    data-values='{!! json_encode($collections->pluck('count')) !!}'>
                </canvas>
            </div>
        </div>

        <!-- Ratings Distribution -->
        <div class="glass p-8 rounded-[2rem] flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="bi bi-shield-check text-rose-400"></i>
                    Altersfreigaben
                </h3>
            </div>
            <div class="h-64">
                <canvas id="ratingChart"
                    data-chart-type="polarArea"
                    data-labels='{!! json_encode($ratings->pluck('rating_age')->map(fn($age) => "FSK $age")) !!}'
                    data-values='{!! json_encode($ratings->pluck('count')) !!}'>
                </canvas>
            </div>
        </div>
    </div>

    <!-- Additional Tables -->
    <div class="grid grid-cols-1 gap-8">
        <div class="glass p-8 rounded-[2rem]">
            <h3 class="text-sm font-black text-white uppercase tracking-widest mb-6 flex items-center gap-2">
                <i class="bi bi-calendar-range text-orange-400"></i>
                Filme nach Dekaden
            </h3>
            <div class="space-y-3">
                @foreach($decades as $decade)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.05] transition-all group">
                        <span class="text-sm font-bold text-white group-hover:text-blue-400 transition-colors">{{ $decade->decade }}er</span>
                        <div class="flex items-center gap-4">
                            <span class="text-xs text-gray-500 font-bold uppercase tracking-tighter">{{ number_format($decade->count) }} Filme</span>
                            <span class="px-2 py-0.5 bg-white/5 rounded text-[10px] text-gray-400 font-black tracking-widest uppercase">⌀ {{ $decade->avg_runtime }} Min</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="glass p-8 rounded-[2rem]">
            <h3 class="text-sm font-black text-white uppercase tracking-widest mb-6 flex items-center gap-2">
                <i class="bi bi-info-circle text-blue-400"></i>
                Sammlungs-Metadaten
            </h3>
            <div class="space-y-4">
                <div class="flex flex-col gap-2 p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Ältester Film</span>
                    <div class="text-lg font-black text-white tracking-tight">{{ $yearStats->oldest_year ?? 'N/A' }}</div>
                </div>
                <div class="flex flex-col gap-2 p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Neuester Film</span>
                    <div class="text-lg font-black text-white tracking-tight">{{ $yearStats->newest_year ?? 'N/A' }}</div>
                </div>
                <div class="flex flex-col gap-2 p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Durchschnittliche Laufzeit</span>
                    <div class="text-lg font-black text-white tracking-tight">{{ $avgRuntime }} Minuten</div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
