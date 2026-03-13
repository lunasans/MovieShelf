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
        },
        nextPageUrl: '{{ $movies->nextPageUrl() }}',
        isLoading: false,
        async loadMore() {
            if (this.isLoading || !this.nextPageUrl) return;
            this.isLoading = true;
            
            try {
                const response = await fetch(this.nextPageUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await response.text();
                
                // Create a temporary element to parse the HTML
                const temp = document.createElement('div');
                temp.innerHTML = html;
                
                // Append items to grid
                const grid = this.$refs.grid;
                while (temp.firstChild) {
                    grid.appendChild(temp.firstChild);
                }
                
                // Check if there's a next page in the pagination (which we'll keep hidden)
                // Actually, let's just increment the page or look at the response.
                // Simple way: the pagination links are still there but hidden.
                // We can fetch the NEXT next page by incrementing our URL.
                const url = new URL(this.nextPageUrl);
                const page = parseInt(url.searchParams.get('page')) + 1;
                url.searchParams.set('page', page);
                
                // We need to know if we hit the end.
                // If the response was empty or fewer items than expected?
                // Better: The partial-list will have a "no more" indicator or we check item count.
                if (html.trim() === '') {
                    this.nextPageUrl = null;
                } else {
                    this.nextPageUrl = url.toString();
                }
            } catch (e) {
                console.error('Failed to load more trailers', e);
            } finally {
                this.isLoading = false;
            }
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8" x-ref="grid">
                @forelse($movies as $movie)
                    @include('trailers.partials.movie-card', ['movie' => $movie])
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

            <!-- Infinite Scroll Trigger -->
            <div x-show="nextPageUrl" 
                 x-intersect.margin.500px="loadMore()" 
                 class="mt-20 flex flex-col items-center justify-center gap-4">
                <div x-show="isLoading" class="flex flex-col items-center gap-4 animate-in fade-in duration-500">
                    <div class="w-12 h-12 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin"></div>
                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] italic">Lade weitere Trailer...</span>
                </div>
                <button x-show="!isLoading" @click="loadMore()" class="px-8 py-4 glass border border-white/10 rounded-2xl text-xs font-black text-gray-400 hover:text-white hover:border-blue-500/50 transition-all uppercase tracking-widest italic group">
                    <span>{{ __('Mehr laden') }}</span>
                    <i class="bi bi-chevron-down ml-2 group-hover:translate-y-1 transition-transform inline-block"></i>
                </button>
            </div>

            <!-- Legacy Pagination (Hidden, but useful for SEO/No-JS) -->
            <div class="hidden">
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
