<div class="grid grid-cols-3 md:grid-cols-5 gap-3">
    <a href="{{ route('admin.tmdb.index') }}"
       class="glass p-4 rounded-2xl border-white/5 flex items-center gap-3 hover:border-rose-500/40 transition-all group">
        <div class="w-9 h-9 rounded-xl bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform shrink-0">
            <i class="bi bi-cloud-download"></i>
        </div>
        <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">TMDb Import</span>
    </a>
    <a href="{{ route('admin.movies.index') }}"
       class="glass p-4 rounded-2xl border-white/5 flex items-center gap-3 hover:border-rose-500/40 transition-all group">
        <div class="w-9 h-9 rounded-xl bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform shrink-0">
            <i class="bi bi-film"></i>
        </div>
        <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Alle Filme</span>
    </a>
    <a href="{{ route('admin.users.index') }}"
       class="glass p-4 rounded-2xl border-white/5 flex items-center gap-3 hover:border-amber-500/40 transition-all group">
        <div class="w-9 h-9 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform shrink-0">
            <i class="bi bi-people"></i>
        </div>
        <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Benutzer</span>
    </a>
    <a href="{{ route('admin.stats.index') }}"
       class="glass p-4 rounded-2xl border-white/5 flex items-center gap-3 hover:border-indigo-500/40 transition-all group">
        <div class="w-9 h-9 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform shrink-0">
            <i class="bi bi-graph-up"></i>
        </div>
        <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Statistiken</span>
    </a>
    <a href="{{ route('admin.settings.index') }}"
       class="glass p-4 rounded-2xl border-white/5 flex items-center gap-3 hover:border-white/20 transition-all group">
        <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-white/40 group-hover:scale-110 transition-transform shrink-0">
            <i class="bi bi-sliders"></i>
        </div>
        <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Einstellungen</span>
    </a>
</div>
