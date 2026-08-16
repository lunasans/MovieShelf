<h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
    <i class="bi bi-lightning-charge-fill text-rose-400"></i> Aktivität
</h3>
<div class="space-y-5 relative">
    <div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-white/5"></div>
    @forelse($stats['recentActivity'] as $log)
        @php
            $details = json_decode($log->details, true);
            $actionInfo = match($log->action) {
                'MOVIE_IMPORT'    => ['label' => 'Importiert',   'icon' => 'bi-plus-circle-fill', 'color' => 'bg-emerald-500/20 text-emerald-400'],
                'MOVIE_UPDATE'    => ['label' => 'Bearbeitet',   'icon' => 'bi-pencil-fill',      'color' => 'bg-rose-500/20 text-rose-400'],
                'MOVIE_DELETE'    => ['label' => 'Gelöscht',     'icon' => 'bi-trash-fill',       'color' => 'bg-rose-500/20 text-rose-400'],
                'SETTINGS_UPDATE' => ['label' => 'Einstellungen','icon' => 'bi-sliders',          'color' => 'bg-indigo-500/20 text-indigo-400'],
                default           => ['label' => $log->action,   'icon' => 'bi-info-circle-fill', 'color' => 'bg-white/10 text-white/50'],
            };
        @endphp
        <div class="relative pl-10 group">
            <div class="absolute left-0 top-1 w-6 h-6 rounded-lg {{ $actionInfo['color'] }} flex items-center justify-center text-[10px] z-10 shadow-lg shadow-black/20 group-hover:scale-110 transition-transform">
                <i class="bi {{ $actionInfo['icon'] }}"></i>
            </div>
            <div class="text-[10px] font-black text-white/20 uppercase tracking-widest mb-0.5">
                {{ $log->created_at->diffForHumans() }}
            </div>
            <div class="text-xs font-bold text-white/80 group-hover:text-rose-400 transition-colors">
                {{ $actionInfo['label'] }}
            </div>
            @if(isset($details['title']))
                <div class="text-[10px] text-white/30 font-bold truncate italic mt-0.5">
                    {{ $details['title'] }}
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-6 text-white/20 text-sm italic">Keine Aktivitäten.</div>
    @endforelse
</div>
