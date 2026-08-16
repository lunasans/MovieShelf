<div class="flex items-center justify-between mb-6">
    <h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] flex items-center gap-3">
        <i class="bi bi-clock text-rose-400"></i> Zuletzt hinzugefügt
    </h3>
    <a href="{{ route('admin.movies.index') }}"
       class="text-[10px] font-black text-white/20 uppercase tracking-widest hover:text-rose-400 transition-colors">
        Alle →
    </a>
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
    @foreach($stats['latestMovies'] as $movie)
        <a href="{{ route('admin.movies.edit', $movie) }}" class="group flex flex-col gap-2">
            @if($movie->cover_url)
                <img src="{{ $movie->cover_url }}"
                     class="w-full aspect-[2/3] object-cover rounded-xl bg-white/5 group-hover:scale-105 transition-transform duration-300"
                     alt="{{ $movie->title }}">
            @else
                <div class="w-full aspect-[2/3] rounded-xl bg-white/5 flex items-center justify-center">
                    <i class="bi bi-film text-white/10 text-2xl"></i>
                </div>
            @endif
            <div class="text-[10px] font-bold text-white/60 truncate group-hover:text-white transition-colors leading-tight">
                {{ $movie->title }}
            </div>
        </a>
    @endforeach
</div>
