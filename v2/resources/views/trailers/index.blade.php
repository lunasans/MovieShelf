<x-app-layout>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="px-8 py-10 min-h-screen" x-data="{ 
        playingTrailer: null,
        openTrailer(title, url) {
            if (!url) return;
            let videoId = '';
            const ytRegExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
            const match = url.match(ytRegExp);
            
            let finalUrl = '';
            if (match && match[2].length === 11) {
                videoId = match[2];
                finalUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&rel=0&modestbranding=1&origin=${window.location.origin}`;
            } else {
                // Fallback/Already embed
                finalUrl = url + (url.includes('?') ? '&' : '?') + 'autoplay=1&mute=1';
            }
            this.playingTrailer = { title: title, url: finalUrl };
        }
    }">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h1 class="text-4xl font-black text-white tracking-tighter mb-2">
                        Trailer <span class="text-blue-500">Galerie</span>
                    </h1>
                    <p class="text-gray-400 font-medium italic opacity-80">{{ __('Entdecke Trailer aus deiner Sammlung') }}</p>
                </div>

                <!-- Search Form -->
                <form action="{{ route('movies.trailers') }}" method="GET" class="relative w-full max-w-md">
                    <input type="text" name="q" value="{{ request('q') }}" 
                        placeholder="{{ __('Nach Trailern suchen...') }}" 
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 pl-14 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-white transition-all outline-none placeholder:text-gray-500 glass">
                    <i class="bi bi-search absolute left-6 top-1/2 -translate-y-1/2 text-gray-500 text-xl"></i>
                </form>
            </div>

            <!-- Trailers Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @forelse($movies as $movie)
                    <div class="group relative flex flex-col gap-4">
                        <!-- Preview Card -->
                        <div @click="openTrailer('{{ addslashes($movie->title) }}', '{{ $movie->trailer_url }}')" 
                             class="relative aspect-video rounded-3xl overflow-hidden glass border border-white/10 transition-all duration-500 hover:scale-[1.03] hover:border-blue-500/50 shadow-2xl group/card cursor-pointer">
                            <!-- Backdrop Image -->
                            @if($movie->backdrop_id)
                                <img src="{{ asset('storage/' . $movie->backdrop_id) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover opacity-60 group-hover/card:scale-110 group-hover/card:opacity-90 transition-all duration-700">
                            @elseif($movie->cover_id)
                                <img src="{{ asset('storage/' . $movie->cover_id) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover blur-sm opacity-40 group-hover/card:scale-110 group-hover/card:opacity-60 transition-all duration-700">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-950 flex items-center justify-center">
                                    <i class="bi bi-film text-4xl text-white/10"></i>
                                </div>
                            @endif

                            <!-- Play Overlay -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/card:opacity-100 transition-opacity duration-500 bg-black/40 backdrop-blur-[2px]">
                                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white shadow-[0_0_30px_rgba(37,99,235,0.6)] transform translate-y-4 group-hover/card:translate-y-0 transition-all duration-500 hover:scale-110">
                                    <i class="bi bi-play-fill text-3xl"></i>
                                </div>
                            </div>

                            <!-- Duration/Label -->
                            <div class="absolute bottom-4 right-4 px-3 py-1 glass rounded-xl text-[10px] font-black text-white italic uppercase tracking-widest border border-white/10 shadow-lg">
                                {{ $movie->year }}
                            </div>
                        </div>

                        <!-- Info Section -->
                        <div class="px-2">
                            <h3 class="text-white font-black text-lg leading-tight tracking-tight mb-1 truncate group-hover:text-blue-400 transition-colors uppercase">
                                {{ $movie->title }}
                            </h3>
                            <div @click.stop="window.location.href = '{{ route('dashboard', ['movie' => $movie->id]) }}'" class="flex items-center gap-2 text-[10px] font-bold text-blue-400 uppercase tracking-widest cursor-pointer hover:underline italic w-fit">
                                <span>{{ __('Zum Film') }}</span>
                                <i class="bi bi-arrow-right"></i>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center glass rounded-[3rem] border border-dashed border-white/10">
                        <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="bi bi-play-circle text-5xl text-gray-700"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-500 uppercase tracking-widest italic">{{ __('Keine Trailer gefunden') }}</h3>
                        <p class="text-gray-600 font-medium mt-2">{{ __('Probiere es mit einem anderen Suchbegriff.') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-20">
                {{ $movies->links() }}
            </div>
        </div>

        <!-- Lightbox / Modal Player -->
        <div x-show="playingTrailer" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-10 bg-black/95 backdrop-blur-3xl"
             @click.away="playingTrailer = null"
             @keydown.escape.window="playingTrailer = null">
            
            <div class="relative w-full max-w-6xl aspect-video rounded-[2.5rem] overflow-hidden shadow-[0_0_100px_rgba(37,99,235,0.3)] border border-white/10 bg-black" @click.stop="">
                <!-- Close Button -->
                <button @click.stop="playingTrailer = null" class="absolute top-6 right-6 w-12 h-12 glass hover:bg-red-500/50 rounded-2xl flex items-center justify-center text-white z-50 transition-all border border-white/20 shadow-2xl">
                    <i class="bi bi-x-lg"></i>
                </button>

                <template x-if="playingTrailer">
                    <iframe :src="playingTrailer.url" 
                            class="absolute inset-0 w-full h-full"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
