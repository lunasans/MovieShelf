@php $topGenreCount = $stats['genres']->first()?->count ?: 1; @endphp

<h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
    <i class="bi bi-tags-fill text-rose-400"></i> Genre-Verteilung
</h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-5">
    @foreach($stats['genres'] as $genre)
        @php $pct = round(($genre->count / $topGenreCount) * 100); @endphp
        <div class="space-y-1.5 group">
            <div class="flex justify-between text-[11px] font-black uppercase tracking-widest">
                <span class="text-white/70 group-hover:text-white transition-colors">{{ $genre->genre }}</span>
                <span class="text-rose-400">{{ $genre->count }}</span>
            </div>
            <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-rose-600 to-rose-400 rounded-full transition-all duration-700"
                     style="width: {{ $pct }}%"></div>
            </div>
        </div>
    @endforeach
</div>
