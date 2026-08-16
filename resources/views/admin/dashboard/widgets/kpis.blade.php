@php
    $runtimeHours = intdiv($stats['totalRuntime'], 60);
    $runtimeDays  = intdiv($runtimeHours, 24);
    $runtimeRest  = $runtimeHours % 24;
    $runtimeLabel = $runtimeDays > 0 ? "{$runtimeDays}d {$runtimeRest}h" : "{$runtimeHours}h";
@endphp

<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
    <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-rose-500/20 transition-all group">
        <i class="bi bi-collection-play-fill text-rose-400 text-lg"></i>
        <div class="text-3xl font-black text-white mt-2">{{ number_format($stats['totalMovies']) }}</div>
        <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Filme</div>
    </div>
    <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-rose-500/20 transition-all group">
        <i class="bi bi-person-hearts text-rose-400 text-lg"></i>
        <div class="text-3xl font-black text-white mt-2">{{ number_format($stats['totalActors']) }}</div>
        <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Schauspieler</div>
    </div>
    <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-amber-500/20 transition-all group">
        <i class="bi bi-people-fill text-amber-400 text-lg"></i>
        <div class="text-3xl font-black text-white mt-2">{{ number_format($stats['totalUsers']) }}</div>
        <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Benutzer</div>
    </div>
    <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-indigo-500/20 transition-all group">
        <i class="bi bi-clock-history text-indigo-400 text-lg"></i>
        <div class="text-3xl font-black text-white mt-2">{{ $runtimeLabel }}</div>
        <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Laufzeit</div>
    </div>
    <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-emerald-500/20 transition-all group">
        <i class="bi bi-graph-up-arrow text-emerald-400 text-lg"></i>
        <div class="text-3xl font-black text-white mt-2">{{ number_format($stats['visitsToday']) }}</div>
        <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Besuche heute</div>
    </div>
    <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-emerald-500/20 transition-all group">
        <i class="bi bi-bar-chart-fill text-emerald-400 text-lg"></i>
        <div class="text-3xl font-black text-white mt-2">{{ number_format($stats['visitsTotal']) }}</div>
        <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Besuche gesamt</div>
    </div>
</div>
