<h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
    <i class="bi bi-shield-check text-rose-400"></i> Datenqualität
</h3>
<div class="space-y-3">
    <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/5 hover:border-rose-500/30 transition-all group">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-rose-500/20 flex items-center justify-center text-rose-400 shrink-0">
                <i class="bi bi-link-45deg text-lg"></i>
            </div>
            <div>
                <div class="text-sm font-black text-white">{{ $stats['missingTmdbCount'] }}</div>
                <div class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Ohne TMDb</div>
            </div>
        </div>
        <a href="{{ route('admin.movies.index', ['filter' => 'missing_tmdb']) }}"
           class="text-white/20 hover:text-white transition-colors">
            <i class="bi bi-arrow-right-short text-2xl"></i>
        </a>
    </div>
    <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/5 hover:border-amber-500/30 transition-all group">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                <i class="bi bi-image text-lg"></i>
            </div>
            <div>
                <div class="text-sm font-black text-white">{{ $stats['missingCoverCount'] }}</div>
                <div class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Ohne Cover</div>
            </div>
        </div>
        <a href="{{ route('admin.movies.index', ['filter' => 'missing_cover']) }}"
           class="text-white/20 hover:text-white transition-colors">
            <i class="bi bi-arrow-right-short text-2xl"></i>
        </a>
    </div>
    <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/5 hover:border-indigo-500/30 transition-all group">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                <i class="bi bi-play-btn text-lg"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <div class="text-sm font-black text-white">{{ $stats['missingTrailerCount'] }}</div>
                    @if($lastStatus = \App\Models\Setting::get('smart_trailer_last_status'))
                        <i class="bi {{ $lastStatus === 'success' ? 'bi-check-circle-fill text-emerald-500' : 'bi-exclamation-circle-fill text-rose-500' }} text-[10px]"></i>
                    @endif
                </div>
                <div class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Ohne Trailer</div>
            </div>
        </div>
        <div class="flex flex-col items-end gap-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.movies.sync-logs') }}"
                   class="text-white/20 hover:text-white transition-colors" title="Sync-Verlauf">
                    <i class="bi bi-list-ul text-lg"></i>
                </a>
                <a href="{{ route('admin.movies.index', ['filter' => 'missing_trailer']) }}"
                   class="text-white/20 hover:text-white transition-colors">
                    <i class="bi bi-arrow-right-short text-2xl"></i>
                </a>
            </div>
            @if($lastRun = \App\Models\Setting::get('smart_trailer_last_run'))
                <span class="text-[8px] text-white/20 font-bold uppercase tracking-widest">
                    {{ \Carbon\Carbon::parse($lastRun)->diffForHumans() }}
                </span>
            @endif
        </div>
    </div>
</div>
