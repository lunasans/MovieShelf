@php
    $layoutMode = auth()->user()->layout ?? 'classic';
@endphp

<x-app-layout>
    <style>
        [x-cloak] { display: none !important; }
        .glass-streaming {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(40px);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                inset 0 1px 1px 0 rgba(255, 255, 255, 0.05);
        }
    </style>
    <script>
        function trailerGallery() {
            return {
                playingTrailer: null,
                nextPageUrl: '{{ $movies->nextPageUrl() }}',
                isLoading: false,
                openTrailer(title, url) {
                    if (!url) return;
                    let videoId = '';
                    const ytRegExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
                    const match = url.match(ytRegExp);
                    let finalUrl = '';
                    if (match && match[2].length === 11) {
                        videoId = match[2];
                        finalUrl = `https://www.youtube-nocookie.com/embed/${videoId}`;
                    } else {
                        finalUrl = url + (url.includes('?') ? '&' : '?') + 'autoplay=1&mute=0';
                    }
                    this.playingTrailer = { title: title, url: finalUrl };
                },
                async loadMore() {
                    if (this.isLoading || !this.nextPageUrl) return;
                    this.isLoading = true;
                    try {
                        const response = await fetch(this.nextPageUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const html = await response.text();
                        if (html.trim() === '') {
                            this.nextPageUrl = null;
                            return;
                        }
                        const temp = document.createElement('div');
                        temp.innerHTML = html;
                        const grid = this.$refs.grid;
                        while (temp.firstChild) {
                            grid.appendChild(temp.firstChild);
                        }
                        try {
                            const url = new URL(this.nextPageUrl);
                            const page = parseInt(url.searchParams.get('page')) + 1;
                            url.searchParams.set('page', page);
                            this.nextPageUrl = url.toString();
                        } catch (urlErr) {
                            this.nextPageUrl = null;
                        }
                    } catch (e) {
                        console.error('Failed to load more trailers', e);
                    } finally {
                        this.isLoading = false;
                    }
                }
            }
        }
    </script>

    <div class="{{ $layoutMode === 'streaming' ? 'streaming-trailers-view min-h-screen pt-4 pb-20 px-12 md:px-20 relative' : 'px-8 py-10 min-h-screen' }}" 
         x-data="trailerGallery()">
        
        @if($layoutMode === 'streaming')
            {{-- Background Elements --}}
            <div class="fixed inset-0 z-0 pointer-events-none">
                <div class="absolute inset-0 bg-[#0c0c0e]"></div>
                <div class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-blue-600/10 rounded-full blur-[120px]"></div>
                <div class="absolute bottom-[-10%] left-[-10%] w-[50%] h-[50%] bg-rose-600/10 rounded-full blur-[120px]"></div>
            </div>

            <div class="relative z-10">
                <!-- Header & Back Button -->
                <div class="mb-16 animate-in slide-in-from-left duration-700">
                    <a href="{{ route('dashboard') }}" class="group inline-flex items-center gap-4 text-white/60 hover:text-white transition-all mb-8">
                        <div class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center bg-white/5 backdrop-blur-xl group-hover:border-white/30 group-hover:scale-110 transition-all">
                            <i class="bi bi-arrow-left text-xl"></i>
                        </div>
                        <span class="font-black uppercase tracking-widest text-sm italic">{{ __('Back to Library') }}</span>
                    </a>

                    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8">
                        <div class="flex-1">
                            <h1 class="text-6xl md:text-8xl font-black text-white tracking-tighter mb-4 drop-shadow-2xl uppercase">
                                {!! __('Trailer <span class="text-blue-500">Gallery</span>') !!}
                            </h1>
                            <p class="text-white/40 text-lg font-bold max-w-2xl italic tracking-wide">
                                {{ __('Discover trailers from your collection') }}
                            </p>
                        </div>
                        
                        <!-- Search Form (Streaming Style) -->
                        <form action="{{ route('movies.trailers') }}" method="GET" class="relative w-full max-w-md group">
                            <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-rose-600 rounded-2xl blur opacity-20 group-focus-within:opacity-50 transition duration-500"></div>
                            <input type="text" name="q" value="{{ request('q') }}"
                                placeholder="{{ __('Search for trailers...') }}"
                                class="relative w-full bg-[#0c0c0e]/80 border border-white/10 rounded-2xl px-6 py-5 pl-14 focus:ring-0 focus:border-white/30 text-white transition-all outline-none placeholder:text-white/20 backdrop-blur-xl">
                            <i class="bi bi-search absolute left-6 top-1/2 -translate-y-1/2 text-white/30 text-xl group-focus-within:text-blue-500 transition-colors"></i>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <!-- Classic Header Section -->
            <div class="max-w-7xl mx-auto relative z-10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                    <div>
                        <h1 class="text-4xl font-black text-white tracking-tighter mb-2 italic">
                            {!! __('Trailer <span class="text-blue-500">Gallery</span>') !!}
                        </h1>
                        <p class="text-gray-400 font-medium italic opacity-80">{{ __('Discover trailers from your collection') }}</p>
                    </div>
                    <!-- Search Form -->
                    <form action="{{ route('movies.trailers') }}" method="GET" class="relative w-full max-w-md">
                        <input type="text" name="q" value="{{ request('q') }}"
                            placeholder="{{ __('Search for trailers...') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 pl-14 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-white transition-all outline-none placeholder:text-gray-500 glass">
                        <i class="bi bi-search absolute left-6 top-1/2 -translate-y-1/2 text-gray-500 text-xl"></i>
                    </form>
                </div>
            </div>
        @endif

        <div class="{{ $layoutMode === 'streaming' ? 'relative z-10' : 'max-w-[90rem] mx-auto' }}">
            <!-- Trailers Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 gap-10" x-ref="grid">
                @forelse($movies as $movie)
                    @include('tenant.trailers.partials.movie-card', ['movie' => $movie])
                @empty
                    <div class="col-span-full py-32 text-center {{ $layoutMode === 'streaming' ? 'glass-streaming rounded-[3rem]' : 'glass rounded-[3rem]' }} border border-dashed border-white/10 animate-in fade-in duration-1000">
                        <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-8 border border-white/10 group-hover:scale-110 transition-transform">
                            <i class="bi bi-play-circle text-6xl text-white/20"></i>
                        </div>
                        <h3 class="text-2xl font-black text-white/40 uppercase tracking-[0.3em] italic mb-4">{{ __('No trailers found') }}</h3>
                        <p class="text-white/20 font-bold uppercase tracking-widest text-xs">{{ __('Try another search term.') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Infinite Scroll Trigger -->
            <div x-show="$data.nextPageUrl" x-intersect.margin.800px="loadMore()" class="mt-24 flex flex-col items-center justify-center gap-6">
                <div x-show="$data.isLoading" class="flex flex-col items-center gap-6 animate-in fade-in duration-500">
                    <div class="w-16 h-16 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin shadow-[0_0_30px_rgba(59,130,246,0.3)]"></div>
                    <span class="text-[12px] font-black text-white/30 uppercase tracking-[0.4em] italic animate-pulse">{{ __('Loading more trailers...') }}</span>
                </div>
                <button x-show="!$data.isLoading" @click="loadMore()" class="{{ $layoutMode === 'streaming' ? 'glass-streaming border-white/10' : 'glass border-white/5' }} px-12 py-5 border rounded-3xl text-xs font-black text-white/40 hover:text-white hover:border-blue-500 transition-all uppercase tracking-[0.3em] italic group shadow-2xl">
                    <span>{{ __('Load more') }}</span>
                    <i class="bi bi-chevron-down ml-3 group-hover:translate-y-1 transition-transform inline-block"></i>
                </button>
            </div>

            <!-- Legacy Pagination (Hidden) -->
            <div class="hidden">
                {{ $movies->links() }}
            </div>
        </div>

        <!-- Lightbox / Modal Player -->
        <div x-show="playingTrailer"
             x-cloak
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 scale-95 blur-xl"
             x-transition:enter-end="opacity-100 scale-100 blur-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100 blur-0"
             x-transition:leave-end="opacity-0 scale-95 blur-xl"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-12 xl:p-20 bg-[#0c0c0e]/95 backdrop-blur-[60px]"
             @click.away="playingTrailer = null"
             @keydown.escape.window="playingTrailer = null">
            
            <div class="absolute inset-0 z-[-1] pointer-events-none overflow-hidden">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[80%] h-[80%] bg-blue-600/20 rounded-full blur-[180px] animate-pulse"></div>
            </div>

            <div class="relative w-full max-w-7xl aspect-video rounded-[3rem] overflow-hidden shadow-[0_0_120px_rgba(0,0,0,0.8)] border border-white/10 bg-black animate-in zoom-in duration-500" @click.stop="">
                <!-- Close Button -->
                <button @click.stop="playingTrailer = null" class="absolute top-8 right-8 w-14 h-14 bg-black/50 backdrop-blur-xl border border-white/20 hover:bg-rose-600 hover:border-rose-400 rounded-3xl flex items-center justify-center text-white z-50 transition-all shadow-2xl group active:scale-90">
                    <i class="bi bi-x-lg text-xl group-hover:rotate-90 transition-transform"></i>
                </button>
                
                <template x-if="playingTrailer">
                    <iframe :src="playingTrailer.url"
                            :title="playingTrailer.title"
                            class="absolute inset-0 w-full h-full"
                            style="border: 0;"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>