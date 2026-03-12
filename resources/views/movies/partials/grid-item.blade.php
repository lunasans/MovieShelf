<div class="group cursor-pointer" 
     x-data="{ isWatched: {{ Auth::check() && Auth::user()->watchedMovies()->where('movie_id', $movie->id)->exists() ? 'true' : 'false' }} }"
     @movie-watched-updated.window="if($event.detail.movieId === {{ $movie->id }}) isWatched = $event.detail.watched"
     @click="fetchDetails({{ $movie->id }})">
    <div class="relative aspect-[2/3] rounded-3xl overflow-hidden glass border border-white/10 shadow-2xl transition-all duration-500 group-hover:scale-[1.05] group-hover:shadow-blue-500/30 group-hover:border-blue-500/50">
        <!-- Movie Cover Placeholder -->
        <div class="absolute inset-0 bg-gray-900 flex items-center justify-center">
            @if($movie->cover_url)
                <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
            @else
                <i class="bi bi-film text-4xl text-white/5"></i>
            @endif
        </div>

        <!-- Watched Indicator (Top Left) -->
        <div class="absolute top-3 left-3 z-20 transition-all duration-300" x-show="isWatched" x-cloak>
            <div class="bg-blue-500/80 backdrop-blur-md p-1.5 rounded-lg border border-white/20 shadow-lg">
                <i class="bi bi-eye-fill text-white text-[10px]"></i>
            </div>
        </div>

        <!-- Rating & Collection Badge -->
        <div class="absolute top-3 right-3 z-20 flex flex-col gap-2 transform translate-x-4 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all duration-500">
            <div class="bg-blue-600 px-2 py-1 rounded-lg border border-white/20 flex items-center gap-1 shadow-xl">
                <i class="bi bi-star-fill text-[10px] text-yellow-400"></i>
                <span class="text-[11px] font-black text-white">{{ number_format($movie->rating ?? 0, 1) }}</span>
            </div>
        </div>
        
        <!-- Collection Type Badge (Bottom Left) -->
        <div class="absolute bottom-3 left-3 z-20">
            <span class="text-[9px] font-black text-white/90 uppercase tracking-widest glass px-2 py-1 rounded-lg border border-white/10 shadow-lg">
                {{ $movie->collection_type }}
            </span>
        </div>

        <!-- Hover Play Icon -->
        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-500 bg-blue-500/10">
            <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white border border-white/30 transform scale-75 group-hover:scale-100 transition-transform duration-500">
                <i class="bi bi-plus-lg text-2xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Content Below -->
    <div class="mt-4 px-1">
        <h3 class="text-[13px] font-black text-white leading-tight truncate group-hover:text-blue-400 transition-colors uppercase tracking-tight">
            {{ $movie->title }}
        </h3>
        <div class="flex items-center gap-2 mt-1 opacity-60 group-hover:opacity-100 transition-opacity">
            <span class="text-[10px] text-gray-400 font-bold italic">{{ $movie->year }}</span>
            <span class="w-1 h-1 bg-blue-500 rounded-full shadow-[0_0_8px_rgba(59,130,246,0.8)]"></span>
            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $movie->genre }}</span>
        </div>
    </div>
</div>
