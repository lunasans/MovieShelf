<div class="streaming-container animate-in fade-in duration-700 overflow-x-hidden">
    {{-- Hero Slider --}}
    @if($featuredMovies->isNotEmpty())
    <section class="relative h-[85vh] w-full rounded-b-[3rem] mb-0 group"
             x-data="{ 
                active: 0, 
                count: {{ $featuredMovies->count() }},
                next() { this.active = (this.active + 1) % this.count },
                prev() { this.active = (this.active - 1 + this.count) % this.count },
                init() { if(this.count > 1) setInterval(() => this.next(), 8000) }
             }">
        
        @foreach($featuredMovies as $index => $movie)
        <div x-show="active === {{ $index }}" 
             x-transition:enter="transition ease-out duration-1000"
             x-transition:enter-start="opacity-0 scale-105"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-1000"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute inset-0 z-0">
            
            <!-- Hero Backdrop -->
            <div class="absolute inset-0 overflow-hidden rounded-b-[3rem]">
                <img src="{{ $movie->backdrop_url ?: $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover transition-transform duration-[20s] ease-linear group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0c0c0e] via-[#0c0c0e]/60 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-[#0c0c0e] via-[#0c0c0e]/20 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#0c0c0e] via-transparent to-transparent"></div>
            </div>

            <!-- Hero Content -->
            <div class="absolute inset-0 z-10 flex flex-col justify-center px-12 md:px-20 max-w-4xl pt-32">
                <div class="mb-4 flex items-center gap-2">
                    <span class="bg-red-600 text-white text-[10px] font-black px-2 py-1 rounded shadow-lg uppercase tracking-widest">
                        {{ __('Featured') }}
                    </span>
                    <span class="text-white/60 text-xs font-bold">{{ $movie->year }} • {{ $movie->collection_type }}</span>
                </div>
                <h1 class="text-5xl md:text-7xl font-black text-white tracking-tighter mb-4 drop-shadow-2xl">
                    {{ $movie->title }}
                </h1>
                <div class="text-white/70 text-lg line-clamp-3 mb-8 max-w-xl font-medium hero-overview">
                    {!! \App\Services\ShortcodeService::parse($movie->overview) ?: __('Experience the latest cinematic masterpiece added to your collection.') !!}
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('movies.show', $movie) }}" 
                            class="px-8 py-4 bg-white text-black rounded-2xl font-black text-lg flex items-center gap-3 hover:scale-105 transition-all shadow-xl active:scale-95">
                        <i class="bi bi-play-fill text-2xl"></i>
                        {{ __('Details') }}
                    </a>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Slider Indicators -->
        @if($featuredMovies->count() > 1)
        <div class="absolute bottom-12 right-12 z-20 flex gap-3">
            @foreach($featuredMovies as $index => $movie)
            <button @click="active = {{ $index }}" 
                    class="h-1.5 transition-all duration-500 rounded-full"
                    :class="active === {{ $index }} ? 'w-12 bg-blue-600' : 'w-4 bg-white/20 hover:bg-white/40'"></button>
            @endforeach
        </div>
        @endif
        
        {{-- Integrated Search Bar (Full Width) --}}
        <div class="absolute bottom-0 left-0 right-0 z-40" style="transform: translateY(50%);">
            <div class="w-full">
                <div class="relative group">
                    <form action="{{ route('dashboard') }}" method="GET" class="relative transition-all duration-700 ease-in-out transform">
                        <input type="text" name="q" value="{{ request('q') }}"
                            @focus="isSearchFocused = true"
                            @blur="isSearchFocused = false"
                            @keydown.window.prevent.slash="if($event.target.tagName !== 'INPUT' && $event.target.tagName !== 'TEXTAREA') { $el.querySelector('input').focus() }"
                            placeholder="{{ __('What do you want to watch today?') }}"
                            class="w-full bg-white/10 border-y border-white/20 py-4 px-12 pl-20 focus:ring-0 focus:border-blue-500/50 text-xl md:text-2xl transition-all placeholder:text-gray-400 backdrop-blur-3xl group-hover:bg-white/15 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.7)] text-white font-light tracking-wide outline-none"
                            :class="isSearchFocused ? 'bg-white/20 border-blue-500/50 shadow-[0_0_80px_rgba(59,130,246,0.3)]' : ''"
                        >
                        <div class="absolute left-10 top-1/2 -translate-y-1/2 flex items-center justify-center transition-all duration-500" :class="isSearchFocused ? 'scale-110 text-blue-400' : 'text-gray-500'">
                            <i class="bi bi-search text-2xl group-hover:text-blue-400 transition-colors"></i>
                        </div>
                        
                        <!-- Shortcut Hint -->
                        <div class="absolute right-12 top-1/2 -translate-y-1/2 px-5 py-2 rounded-2xl bg-black/40 border border-white/10 text-[10px] font-black text-gray-500 pointer-events-none transition-opacity duration-500 flex items-center gap-3 uppercase tracking-[0.3em]" :class="isSearchFocused ? 'opacity-0' : 'opacity-100'">
                            <span>Search</span>
                            <span class="bg-white/10 px-2 py-1 rounded ml-2">/</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    @endif
    

    {{-- Content Rows --}}
    <div class="space-y-16 pb-20" style="padding-top: 2.5rem;">

        {{-- Advanced Filter Bar --}}
        @php
            $hasActiveFilters = request()->hasAny(['genre','year_from','year_to','rating_min','runtime_max']);
        @endphp
        <div class="px-12 md:px-20">
            <form action="{{ route('dashboard') }}" method="GET"
                  class="glass border {{ $hasActiveFilters ? 'border-blue-500/30' : 'border-white/10' }} rounded-2xl p-4 flex flex-wrap gap-3 items-end">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="type" value="{{ request('type') }}">

                <div class="flex items-center gap-1.5 text-gray-500 self-center shrink-0">
                    <i class="bi bi-funnel-fill text-sm {{ $hasActiveFilters ? 'text-blue-400' : '' }}"></i>
                    <span class="text-[9px] font-black uppercase tracking-widest {{ $hasActiveFilters ? 'text-blue-400' : '' }}">Filter</span>
                </div>

                <div class="flex flex-col gap-1 min-w-[120px]">
                    <label class="text-[9px] font-black text-white/30 uppercase tracking-widest px-1">Genre</label>
                    <select name="genre" class="bg-white/5 border {{ request('genre') ? 'border-blue-500/50 text-white' : 'border-white/10 text-gray-400' }} rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-blue-500/50 appearance-none cursor-pointer">
                        <option value="">Alle</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre }}" {{ request('genre') === $genre ? 'selected' : '' }} class="bg-zinc-900 text-white">{{ $genre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[9px] font-black text-white/30 uppercase tracking-widest px-1">Jahr</label>
                    <div class="flex items-center gap-1">
                        <input type="number" name="year_from" value="{{ request('year_from') }}" placeholder="von" min="1900" max="{{ date('Y') }}"
                            class="w-20 bg-white/5 border {{ request('year_from') ? 'border-blue-500/50 text-white' : 'border-white/10 text-gray-400' }} rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                        <span class="text-gray-600 text-xs">–</span>
                        <input type="number" name="year_to" value="{{ request('year_to') }}" placeholder="bis" min="1900" max="{{ date('Y') }}"
                            class="w-20 bg-white/5 border {{ request('year_to') ? 'border-blue-500/50 text-white' : 'border-white/10 text-gray-400' }} rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[9px] font-black text-white/30 uppercase tracking-widest px-1">Mind. Bewertung</label>
                    <input type="number" name="rating_min" value="{{ request('rating_min') }}" placeholder="0–10" min="0" max="10" step="0.5"
                        class="w-24 bg-white/5 border {{ request('rating_min') ? 'border-blue-500/50 text-white' : 'border-white/10 text-gray-400' }} rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[9px] font-black text-white/30 uppercase tracking-widest px-1">Max. Laufzeit (Min.)</label>
                    <input type="number" name="runtime_max" value="{{ request('runtime_max') }}" placeholder="z.B. 120" min="1"
                        class="w-28 bg-white/5 border {{ request('runtime_max') ? 'border-blue-500/50 text-white' : 'border-white/10 text-gray-400' }} rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                </div>

                <div class="flex gap-2 self-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if($hasActiveFilters)
                        <a href="{{ route('dashboard', array_filter(['q' => request('q'), 'type' => request('type')])) }}"
                           class="px-4 py-2 bg-white/5 hover:bg-red-500/10 border border-white/10 hover:border-red-500/30 text-gray-400 hover:text-red-400 text-xs font-black rounded-xl transition-all" title="Filter zurücksetzen">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if(request('q') || request('type') || $hasActiveFilters)
            {{-- Search/Filter Results View --}}
            <section class="animate-in fade-in slide-in-from-bottom-8 duration-700">
                <div class="flex items-center justify-between mb-10 px-12 md:px-20">
                    <div>
                        <h2 class="text-4xl font-black text-white tracking-tighter mb-2 uppercase italic">
                            @if(request('q'))
                                {{ __('Results for ":query"', ['query' => request('q')]) }}
                            @else
                                {{ request('type') }}
                            @endif
                        </h2>
                        <p class="text-white/30 text-[10px] font-black uppercase tracking-[0.3em] italic">
                            {{ $movies->total() }} {{ __('Movies found') }}
                        </p>
                    </div>
                    <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-white/5 border border-white/10 rounded-2xl text-[10px] font-black text-white/40 uppercase tracking-widest hover:text-white hover:bg-white/10 transition-all italic">
                        <i class="bi bi-x-lg mr-2"></i> {{ __('Clear') }}
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-8 px-12 md:px-20">
                    @forelse($movies as $movie)
                        @include('tenant.movies.partials.streaming-grid-item', ['movie' => $movie])
                    @empty
                        <div class="col-span-full py-32 text-center">
                            <i class="bi bi-search text-7xl text-white/5 mb-8 block"></i>
                            <h3 class="text-2xl font-black text-white uppercase tracking-tighter italic">{{ __('No movies found.') }}</h3>
                            <p class="text-white/20 mt-4 uppercase text-[10px] font-black tracking-widest italic">{{ __('Try different keywords or filters.') }}</p>
                        </div>
                    @endforelse
                </div>

                @if($movies->hasPages())
                    <div class="mt-20 flex justify-center">
                        {{ $movies->appends(request()->except('page'))->links() }}
                    </div>
                @endif
            </section>
        @else
            {{-- Standard Discovery View (New Arrivals & Genres) --}}

        {{-- Row: New Arrivals --}}
        <section x-data="{ 
            canScrollLeft: false, 
            canScrollRight: true,
            checkScroll() {
                const el = this.$refs.slider;
                this.canScrollLeft = el.scrollLeft > 10;
                this.canScrollRight = el.scrollLeft < (el.scrollWidth - el.clientWidth - 10);
            }
        }" x-init="checkScroll()" class="relative group/row">
            <div class="flex items-center justify-between mb-6 px-12 md:px-20">
                <h2 class="text-2xl font-black text-white tracking-tight flex items-center gap-4">
                    <span>{{ __('New Arrivals') }}</span>
                    <div class="h-1 w-12 bg-blue-600 rounded-full"></div>
                </h2>
                <a href="#" class="text-sm font-bold text-gray-500 hover:text-white transition-colors">{{ __('View All') }}</a>
            </div>

            <!-- Slider Controls -->
            <button @click="$refs.slider.scrollBy({ left: -600, behavior: 'smooth' })" 
                    x-show="canScrollLeft"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    class="absolute left-4 top-[calc(50%+1rem)] -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-black/60 backdrop-blur-xl border border-white/10 text-white flex items-center justify-center opacity-0 group-hover/row:opacity-100 transition-all hover:bg-blue-600 shadow-2xl">
                <i class="bi bi-chevron-left text-xl"></i>
            </button>

            <button @click="$refs.slider.scrollBy({ left: 600, behavior: 'smooth' })" 
                    x-show="canScrollRight"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    class="absolute right-4 top-[calc(50%+1rem)] -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-black/60 backdrop-blur-xl border border-white/10 text-white flex items-center justify-center opacity-0 group-hover/row:opacity-100 transition-all hover:bg-blue-600 shadow-2xl">
                <i class="bi bi-chevron-right text-xl"></i>
            </button>

            <div x-ref="slider" 
                 @scroll.debounce.50ms="checkScroll()"
                 class="flex gap-6 overflow-x-auto no-scrollbar pb-8 px-12 md:px-20 scroll-smooth">
                @foreach($latestMovies as $movie)
                     <div @click="window.location.href = '{{ route('movies.show', $movie) }}'" 
                         class="w-[160px] md:w-[220px] shrink-0 aspect-[2/3] relative rounded-[2rem] overflow-hidden glass-streaming border border-white/10 group cursor-pointer hover:border-blue-500/50 hover:scale-105 transition-all duration-500 shadow-2xl">
                        <img src="{{ $movie->cover_url }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        {{-- Permanent subtle bottom shadow for legibility on light covers --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 transition-opacity duration-500 group-hover:opacity-0"></div>
                        {{-- Hover gradient --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        
                        <div class="absolute bottom-0 left-0 right-0 p-6 translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                             <h4 class="text-xs font-black text-white uppercase tracking-wider mb-2 drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)] truncate">{{ $movie->title }}</h4>
                             <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">
                                 <span class="text-[8px] font-black text-blue-400 uppercase tracking-widest">{{ $movie->year }}</span>
                                 <div class="h-1 w-1 bg-white/20 rounded-full"></div>
                                 <span class="text-[8px] font-black text-white/40 uppercase tracking-widest">{{ $movie->collection_type }}</span>
                             </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Row: All Movies Grid --}}
        <section class="animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-500">
            <div class="flex items-center justify-between mb-10 px-12 md:px-20">
                <div>
                    <h2 class="text-4xl font-black text-white tracking-tighter mb-2 uppercase italic">
                        {{ __('All Movies') }}
                    </h2>
                    <p class="text-white/30 text-[10px] font-black uppercase tracking-[0.3em] italic">
                        {{ $movies->total() }} {{ __('Movies available') }}
                    </p>
                </div>
            </div>

            <div x-ref="movieGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-8 px-12 md:px-20 pb-20">
                @foreach($movies as $movie)
                    @include('tenant.movies.partials.streaming-grid-item', ['movie' => $movie])
                @endforeach
            </div>

            <!-- Manual Load More Trigger for Streaming -->
            <div x-show="nextMoviesPageUrl"
                 class="pb-32 flex flex-col items-center justify-center gap-6 animate-in fade-in slide-in-from-bottom-4 duration-1000 delay-700">
                <div x-show="isMoviesLoading" class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin"></div>
                    <span class="text-[10px] font-black text-white/40 uppercase tracking-[0.3em] animate-pulse">{{ __('Loading more...') }}</span>
                </div>
                <button x-show="!isMoviesLoading" @click="loadMoreMovies()" 
                        class="px-12 py-5 bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl text-xs font-black text-white uppercase tracking-[0.3em] hover:bg-white/10 hover:border-blue-500/50 hover:scale-105 transition-all shadow-2xl group active:scale-95">
                    <span>{{ __('Load more movies') }}</span>
                    <i class="bi bi-chevron-down ml-3 group-hover:translate-y-1 transition-transform inline-block text-blue-500"></i>
                </button>
            </div>
        </section>
        @endif
    </div>
</div>

<style>
    .glass-streaming {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
    }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    .streaming-container section {
        animation: reveal-row 0.8s cubic-bezier(0.4, 0, 0.2, 1) both;
    }
    
    @keyframes reveal-row {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .streaming-container section:nth-child(1) { animation-delay: 0.1s; }
    .streaming-container section:nth-child(2) { animation-delay: 0.3s; }
    .streaming-container section:nth-child(3) { animation-delay: 0.5s; }

    /* Fix line-clamp for Hero Overview containing Quill HTML */
    .hero-overview p, .hero-overview div {
        display: inline !important;
        margin: 0 !important;
    }
</style>
