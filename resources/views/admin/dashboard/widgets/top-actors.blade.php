<h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
    <i class="bi bi-star-fill text-amber-400"></i> Top Schauspieler
</h3>
<div class="space-y-3">
    @foreach($stats['topActors'] as $i => $actor)
        <div class="flex items-center gap-3 group">
            <span class="text-[10px] font-black text-white/20 w-4 shrink-0">{{ $i + 1 }}</span>
            @if($actor->profile_url)
                <img src="{{ $actor->profile_url }}"
                     class="w-8 h-8 rounded-full object-cover bg-white/5 shrink-0"
                     alt="{{ $actor->first_name }}">
            @else
                <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center shrink-0">
                    <i class="bi bi-person text-white/20 text-xs"></i>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="text-xs font-bold text-white/80 truncate group-hover:text-white transition-colors">
                    {{ $actor->first_name }} {{ $actor->last_name }}
                </div>
                <div class="text-[10px] text-white/30 font-bold">{{ $actor->movies_count }} Filme</div>
            </div>
        </div>
    @endforeach
</div>
