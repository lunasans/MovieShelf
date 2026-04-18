 <x-app-layout>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboard', () => ({
                selectedMovie: null,
                isStatsView: false,
                isSearchFocused: false,
                loading: false,
                error: null,

                fetchDetails(id, backdropUrl = null) {
                    this.isStatsView = false;
                    this.loading = true;
                    this.error = null;

                    if (backdropUrl) {
                        window.dispatchEvent(new CustomEvent('change-background', { detail: backdropUrl }));
                    }

                    fetch(`/movies/${id}/details`)
                        .then(res => res.text())
                        .then(html => {
                            this.selectedMovie = html;
                            this.loading = false;
                            
                            // Update URL without reload
                            const url = new URL(window.location);
                            url.searchParams.set('movie', id);
                            window.history.pushState({}, '', url);
                        })
                        .catch(err => {
                            this.error = "{{ __('Error loading details.') }}";
                            this.loading = false;
                        });
                },

                fetchActor(id) {
                    this.isStatsView = false;
                    this.loading = true;
                    this.error = null;

                    fetch(`/actors/${id}/details`)
                        .then(res => res.text())
                        .then(html => {
                            this.selectedMovie = html; // Reusing the same container for simplicity
                            this.loading = false;
                        })
                        .catch(err => {
                            this.error = "{{ __('Error loading profile.') }}";
                            this.loading = false;
                        });
                },

                fetchStats() {
                    this.isStatsView = true;
                    this.loading = true;
                    this.error = null;

                    window.dispatchEvent(new CustomEvent('change-background', { detail: '' }));

                    fetch('{{ route('statistics') }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.text())
                        .then(html => {
                            this.selectedMovie = html;
                            this.loading = false;
                            this.$nextTick(() => this.initCharts());
                        })
                        .catch(err => {
                            this.error = "{{ __('Error loading statistics.') }}";
                            this.loading = false;
                        });
                },

                fetchImpressum() {
                    this.isStatsView = true; // Use wide layout like statistics
                    this.loading = true;
                    this.error = null;

                    window.dispatchEvent(new CustomEvent('change-background', { detail: '' }));

                    fetch('{{ route('impressum') }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.text())
                        .then(html => {
                            this.selectedMovie = html;
                            this.loading = false;
                        })
                        .catch(err => {
                            this.error = "{{ __('Error loading imprint.') }}";
                            this.loading = false;
                        });
                },

                async fetchRandom() {
                    this.loading = true;
                    this.error = null;

                    const params = new URLSearchParams(window.location.search);
                    params.delete('movie');
                    params.delete('page');

                    try {
                        const response = await fetch(`/movies/random?${params.toString()}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();

                        if (data.id) {
                            this.fetchDetails(data.id, data.backdrop_url);
                        } else {
                            this.error = "{{ __('No movies found.') }}";
                        }
                    } catch (e) {
                        console.error('Random fetch failed', e);
                        this.error = "{{ __('Error loading.') }}";
                    } finally {
                        this.loading = false;
                    }
                },

                layoutMode: '{{ auth()->user()->layout ?? \App\Models\Setting::get("default_guest_layout", "classic") }}' || localStorage.getItem('dashboardLayout') || 'classic',

                async toggleLayout(mode) {
                    this.layoutMode = mode;
                    localStorage.setItem('dashboardLayout', mode);
                    
                    // Persist to user profile via AJAX
                    try {
                        await fetch('{{ route("profile.settings.update") }}', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ 
                                layout: mode,
                                language: '{{ app()->getLocale() }}' // Include language as it's now required by the new validation logic
                            })
                        });
                    } catch (e) {
                        console.error('Failed to save layout preference:', e);
                    }

                    // If switching to streaming, we might want to reset background
                    if (mode === 'streaming') {
                         window.dispatchEvent(new CustomEvent('change-background', { detail: '' }));
                    }
                },

                viewMode: localStorage.getItem('movieViewMode') || '{{ $defaultViewMode }}',

                toggleView(mode) {
                    this.viewMode = mode;
                    localStorage.setItem('movieViewMode', mode);
                },

                initCharts() {
                    const isStreaming = this.layoutMode === 'streaming';
                    const chartOptions = {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { 
                                display: false,
                                labels: {
                                    color: isStreaming ? 'rgba(255, 255, 255, 0.7)' : 'rgba(255, 255, 255, 0.5)',
                                    font: { size: 10, weight: 'bold', family: 'Inter, sans-serif' }
                                }
                            } 
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { color: isStreaming ? 'rgba(255, 255, 255, 0.03)' : 'rgba(255, 255, 255, 0.05)' }, 
                                ticks: { color: isStreaming ? 'rgba(255, 255, 255, 0.3)' : 'rgba(255, 255, 255, 0.5)', font: { size: 10, weight: 'bold' } } 
                            },
                            x: { 
                                grid: { display: false }, 
                                ticks: { color: isStreaming ? 'rgba(255, 255, 255, 0.3)' : 'rgba(255, 255, 255, 0.5)', font: { size: 10, weight: 'bold' } } 
                            }
                        }
                    };

                    document.querySelectorAll('canvas[data-chart-type]').forEach(canvas => {
                        const type = canvas.getAttribute('data-chart-type');
                        const labels = JSON.parse(canvas.getAttribute('data-labels'));
                        const values = JSON.parse(canvas.getAttribute('data-values'));

                        let config = {
                            type: type,
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: values,
                                    backgroundColor: type === 'line' 
                                        ? (isStreaming ? 'rgba(59, 130, 246, 0.15)' : 'rgba(96, 165, 250, 0.1)') 
                                        : (isStreaming 
                                            ? ['#3b82f6', '#8b5cf6', '#ec4899', '#f43f5e', '#f59e0b', '#10b981'] 
                                            : ['#3b82f6', '#f59e0b', '#ef4444', '#10b981', '#8b5cf6', '#a855f7']),
                                    borderColor: type === 'line' ? (isStreaming ? '#3b82f6' : '#60a5fa') : 'transparent',
                                    borderWidth: type === 'line' ? (isStreaming ? 4 : 3) : 0,
                                    fill: type === 'line',
                                    tension: type === 'line' ? 0.4 : 0,
                                    borderRadius: type === 'bar' ? (isStreaming ? 12 : 8) : 0,
                                    pointBackgroundColor: type === 'line' ? '#3b82f6' : 'transparent',
                                    pointRadius: type === 'line' ? (isStreaming ? 0 : 3) : 0,
                                    pointHoverRadius: 6,
                                }]
                            },
                            options: (type === 'doughnut' || type === 'polarArea')
                                ? { ...chartOptions, scales: { x: { display: false }, y: { display: false } }, plugins: { legend: { display: true, position: 'bottom', labels: { color: '#fff' } } } }
                                : chartOptions
                        };
                        new Chart(canvas, config);
                    });
                },

                nextMoviesPageUrl: '{{ $movies->nextPageUrl() }}',
                isMoviesLoading: false,

                async loadMoreMovies() {
                    if (this.isMoviesLoading || !this.nextMoviesPageUrl) return;
                    this.isMoviesLoading = true;

                    try {
                        const response = await fetch(this.nextMoviesPageUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const html = await response.text();

                        if (html.trim() === '') {
                            this.nextMoviesPageUrl = null;
                            return;
                        }

                        // Create a temporary element to parse the HTML
                        const temp = document.createElement('div');
                        temp.innerHTML = html;

                        // Append items to list
                        const grid = this.$refs.movieGrid;
                        while (temp.firstChild) {
                            grid.appendChild(temp.firstChild);
                        }

                        // Update nextMoviesPageUrl safely
                        try {
                            const url = new URL(this.nextMoviesPageUrl);
                            const page = parseInt(url.searchParams.get('page')) + 1;
                            url.searchParams.set('page', page);
                            this.nextMoviesPageUrl = url.toString();
                        } catch (urlErr) {
                            this.nextMoviesPageUrl = null;
                        }
                    } catch (e) {
                        console.error('Failed to load more movies', e);
                    } finally {
                        this.isMoviesLoading = false;
                    }
                },

                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const movieId = urlParams.get('movie');
                    const actorId = urlParams.get('actor');
                    const showStats = urlParams.get('stats');
                    const showImpressum = urlParams.get('impressum');

                    if (movieId) this.fetchDetails(movieId);
                    else if (actorId) this.fetchActor(actorId);
                    else if (showStats) this.fetchStats();
                    else if (showImpressum) this.fetchImpressum();
                },

                // Boxset Modal State
                boxsetOpen: false,
                boxsetMovie: null,
                boxsetChildren: [],
                boxsetLoading: false,

                async openBoxset(id, title) {
                    this.boxsetOpen = true;
                    this.boxsetMovie = { id, title };
                    this.boxsetLoading = true;
                    this.boxsetChildren = [];

                    try {
                        const response = await fetch(`/movies/${id}/boxset`);
                        const data = await response.json();
                        this.boxsetChildren = data.children;
                    } catch (e) {
                        console.error('Failed to load boxset children', e);
                    } finally {
                        this.boxsetLoading = false;
                    }
                },

                selectBoxsetChild(child) {
                    this.boxsetOpen = false;
                    this.fetchDetails(child.id);
                }
            }));
        });
    </script>

    <div class="layout transition-all duration-500 ease-in-out"
         :class="{ 'layout-stats-active': isStatsView, 'layout-streaming': layoutMode === 'streaming' }"
         x-data="dashboard"
         @stats-open.window="fetchStats()"
         @impressum-open.window="fetchImpressum()"
         @open-boxset.window="openBoxset($event.detail.id, $event.detail.title)"
         @layout-change.window="toggleLayout($event.detail)"
    >
        <template x-if="layoutMode === 'classic'">
            <div class="contents">
                <!-- Film-Liste Area (Left Column) -->
        <section class="film-list-area shadow-2xl">
                <div class="flex flex-col gap-6 mb-4">
                    <!-- Search Bar Section -->
                    <div class="relative group max-w-lg px-2">
                        <form action="{{ route('dashboard') }}" method="GET" class="relative transition-all duration-500 ease-in-out">
                            <input type="text" name="q" value="{{ request('q') }}"
                                @focus="isSearchFocused = true"
                                @blur="isSearchFocused = false"
                                @keydown.window.prevent.slash="if($event.target.tagName !== 'INPUT' && $event.target.tagName !== 'TEXTAREA') { $el.querySelector('input').focus() }"
                                placeholder="{{ __('Search in your library...') }}"
                                class="w-full bg-white/10 border border-white/20 rounded-[2rem] py-2 px-6 pl-14 focus:ring-8 focus:ring-blue-500/20 focus:border-blue-500/50 text-base transition-all placeholder:text-gray-400 backdrop-blur-2xl group-hover:bg-white/15 shadow-2xl text-white"
                                :class="isSearchFocused ? 'bg-white/20 border-blue-500/50 shadow-[0_0_40px_rgba(59,130,246,0.3)]' : ''"
                            >
                            <div class="absolute left-6 top-1/2 -translate-y-1/2 flex items-center justify-center transition-transform duration-300" :class="isSearchFocused ? 'scale-110' : ''">
                                <i class="bi bi-search text-xl text-gray-500 group-hover:text-blue-400 transition-colors" :class="isSearchFocused ? 'text-blue-400' : ''"></i>
                            </div>
                            
                            <!-- Shortcut Hint -->
                            <div class="absolute right-6 top-1/2 -translate-y-1/2 px-3 py-1 rounded-xl bg-black/20 border border-white/10 text-xs font-black text-gray-500 pointer-events-none transition-opacity duration-300 flex items-center gap-1" :class="isSearchFocused ? 'opacity-0' : 'opacity-100'">
                                <span class="tracking-widest capitalize">Suchen</span>
                                <span class="bg-white/10 px-1.5 py-0.5 rounded ml-1">/</span>
                            </div>
                        </form>
                    </div>

                    <div class="flex items-center justify-between gap-4 flex-wrap min-h-[46px]">
                        <div class="flex items-center gap-2 bg-white/5 border border-white/10 p-1.5 rounded-2xl overflow-x-auto no-scrollbar max-w-full">
                        <a href="{{ route('dashboard') }}"
                            class="px-5 py-2 rounded-xl text-sm font-semibold transition-all {{ !request('type') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            {{ __('All') }}
                        </a>
                        @foreach($collectionTypes as $type)
                            <a href="{{ route('dashboard', ['type' => $type, 'q' => request('q')]) }}"
                                class="px-5 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition-all {{ request('type') === $type ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                                {{ $type }}
                            </a>
                        @endforeach
                    </div>

                <!-- Random + View Mode Toggle -->
                <div class="flex gap-4 items-center shrink-0">
                    <button
                        @click="fetchRandom()"
                        class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-xl shadow-lg shadow-purple-900/40 transition-all font-bold text-xs group active:scale-95"
                        title="{{ __('Zufälliger Film') }}"
                    >
                        <i class="bi bi-dice-5 text-lg group-hover:rotate-45 transition-transform duration-500"></i>
                        <span class="hidden sm:inline uppercase tracking-widest">Was soll ich schauen?</span>
                    </button>

                    <div class="flex gap-2 bg-white/5 border border-white/10 p-1.5 rounded-2xl">
                        <button
                            @click="toggleView('grid')"
                            class="p-2 rounded-xl transition-all"
                            :class="viewMode === 'grid' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : 'text-gray-500 hover:text-white hover:bg-white/5'"
                        >
                            <i class="bi bi-grid-3x3-gap"></i>
                        </button>
                        <button
                            @click="toggleView('list')"
                            class="p-2 rounded-xl transition-all"
                            :class="viewMode === 'list' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : 'text-gray-500 hover:text-white hover:bg-white/5'"
                        >
                            <i class="bi bi-list-ul"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Advanced Filters (always visible) -->
            @php
                $hasActiveFilters = request()->hasAny(['genre','year_from','year_to','rating_min','runtime_max']);
            @endphp
            <form action="{{ route('dashboard') }}" method="GET"
                  class="px-2 mt-6 glass border {{ $hasActiveFilters ? 'border-blue-500/30' : 'border-white/10' }} rounded-2xl p-5 flex flex-wrap gap-4 items-end w-full">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="type" value="{{ request('type') }}">

                <div class="flex items-center gap-2 text-gray-500 self-center shrink-0 pr-2 border-r border-white/10">
                    <i class="bi bi-funnel-fill text-base {{ $hasActiveFilters ? 'text-blue-400' : '' }}"></i>
                    <span class="text-[9px] font-black uppercase tracking-widest {{ $hasActiveFilters ? 'text-blue-400' : '' }}">Filter</span>
                </div>

                {{-- Genre --}}
                <div class="filter-bar-field">
                    <label class="text-[9px] font-black text-white/30 uppercase tracking-widest px-1">Genre</label>
                    <select name="genre" class="bg-white/5 border {{ request('genre') ? 'border-blue-500/50 text-white' : 'border-white/10 text-gray-400' }} rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-blue-500/50 appearance-none cursor-pointer">
                        <option value="">Alle</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre }}" {{ request('genre') === $genre ? 'selected' : '' }} class="bg-zinc-900 text-white">{{ $genre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Jahr von/bis --}}
                <div class="filter-bar-field">
                    <label class="text-[9px] font-black text-white/30 uppercase tracking-widest px-1">Jahr</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="year_from" value="{{ request('year_from') }}" placeholder="von" min="1900" max="{{ date('Y') }}"
                            class="w-full bg-white/5 border {{ request('year_from') ? 'border-blue-500/50 text-white' : 'border-white/10 text-gray-400' }} rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                        <span class="text-gray-600 text-xs shrink-0">–</span>
                        <input type="number" name="year_to" value="{{ request('year_to') }}" placeholder="bis" min="1900" max="{{ date('Y') }}"
                            class="w-full bg-white/5 border {{ request('year_to') ? 'border-blue-500/50 text-white' : 'border-white/10 text-gray-400' }} rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                    </div>
                </div>

                {{-- Min. Bewertung --}}
                <div class="filter-bar-field">
                    <label class="text-[9px] font-black text-white/30 uppercase tracking-widest px-1">Mind. Bewertung</label>
                    <input type="number" name="rating_min" value="{{ request('rating_min') }}" placeholder="0–10" min="0" max="10" step="0.5"
                        class="w-full bg-white/5 border {{ request('rating_min') ? 'border-blue-500/50 text-white' : 'border-white/10 text-gray-400' }} rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                </div>

                {{-- Max. Laufzeit --}}
                <div class="filter-bar-field">
                    <label class="text-[9px] font-black text-white/30 uppercase tracking-widest px-1">Max. Laufzeit (Min.)</label>
                    <input type="number" name="runtime_max" value="{{ request('runtime_max') }}" placeholder="z.B. 120" min="1"
                        class="w-full bg-white/5 border {{ request('runtime_max') ? 'border-blue-500/50 text-white' : 'border-white/10 text-gray-400' }} rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                </div>

                {{-- Buttons --}}
                <div class="flex gap-2 self-end shrink-0">
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if($hasActiveFilters)
                        <a href="{{ route('dashboard', array_filter(['q' => request('q'), 'type' => request('type')])) }}"
                           class="px-5 py-2 bg-white/5 hover:bg-red-500/10 border border-white/10 hover:border-red-500/30 text-gray-400 hover:text-red-400 text-xs font-black rounded-xl transition-all" title="Filter zurücksetzen">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- List Area Header -->
            <div class="flex items-center justify-between mb-8 px-2 gap-4 flex-wrap min-h-[40px]">
                <h2 class="text-xl font-black text-white flex items-center gap-4">
                    <div class="h-10 w-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                        <i class="bi bi-collection-play"></i>
                    </div>
                    <span>
                        @if(request('q'))
                            {{ __('Search: :query', ['query' => request('q')]) }}
                        @else
                            {{ request('type') ?? __('Mediathek') }}
                        @endif
                    </span>
                </h2>
                <div class="flex flex-col items-end">
                    <span class="text-white font-black text-lg leading-none">{{ $movies->total() }}</span>
                    <span class="text-gray-500 text-[10px] uppercase font-bold tracking-widest">{{ __('Total') }}</span>
                </div>
            </div>

            <!-- Film Grid/List -->
            <div x-ref="movieGrid" :class="viewMode === 'grid' ? 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-4' : 'flex flex-col gap-3'">
                @forelse ($movies as $movie)
                    <template x-if="viewMode === 'grid'">
                        @include('tenant.movies.partials.grid-item', ['movie' => $movie])
                    </template>
                    <template x-if="viewMode === 'list'">
                        @include('tenant.movies.partials.list-item', ['movie' => $movie])
                    </template>
                @empty
                    <div class="col-span-full py-20 text-center glass rounded-2xl border-dashed">
                        <i class="bi bi-search text-6xl text-gray-800 mb-4 block"></i>
                        <h3 class="text-xl font-bold text-white">{{ __('No movies found.') }}</h3>
                        <p class="text-gray-500 mt-2">{{ __('Adjust your search or filters.') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Manual Load More Trigger -->
            <div x-show="nextMoviesPageUrl"
                 class="mt-12 flex flex-col items-center justify-center gap-4">
                <div x-show="isMoviesLoading" class="flex flex-col items-center gap-2 animate-in fade-in duration-500">
                    <div class="w-10 h-10 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin"></div>
                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest italic tracking-[0.2em]">{{ __('Loading more movies...') }}</span>
                </div>
                <button x-show="!isMoviesLoading" @click="loadMoreMovies()" class="px-8 py-3 glass border border-white/10 rounded-2xl text-[10px] font-black text-gray-500 hover:text-white hover:border-blue-500/50 transition-all uppercase tracking-[0.2em] italic group">
                    <span>{{ __('Load more movies') }}</span>
                    <i class="bi bi-chevron-down ml-2 group-hover:translate-y-1 transition-transform inline-block"></i>
                </button>
            </div>

            <!-- Legacy Pagination (Hidden) -->
            <div class="hidden">
                {{ $movies->links() }}
            </div>
        </section>

        <!-- Detail Panel (Right Column) -->
        <aside class="detail-panel shadow-2xl block relative min-h-[600px]">
            <!-- Loading State -->
            <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm z-50 rounded-2xl">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin"></div>
                    <span class="text-sm font-medium text-gray-400 tracking-widest uppercase italic">{{ __('Loading details...') }}</span>
                </div>
            </div>

            <!-- Error State -->
            <div x-show="error" class="h-full flex flex-col items-center justify-center p-8 text-center">
                <i class="bi bi-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                <p x-text="error" class="text-white font-medium"></p>
                <button @click="error = null" class="mt-4 text-sm text-blue-400 hover:underline">{{ __('Close') }}</button>
            </div>

            <!-- Content Area: Show Latest Movies when no movie is selected -->
            <div x-show="!selectedMovie && !loading && !error" class="h-full p-0 overflow-y-auto no-scrollbar">
                <!-- Spacer to match Tabs on the left -->
                <div class="h-[46px] mb-8"></div>
                @include('tenant.movies.partials.latest', ['latestMovies' => $latestMovies])
            </div>

            <!-- Dynamic Detail Content -->
                <div x-show="selectedMovie && !loading" x-html="selectedMovie" class="h-full"></div>
            </aside>
        </template>

        <template x-if="layoutMode === 'streaming'">
            <div class="relative min-h-screen" :class="(isStatsView || selectedMovie) ? 'pt-32' : 'pt-0'">
                <template x-if="isStatsView || selectedMovie">
                    <div class="animate-in fade-in slide-in-from-bottom-8 duration-700 relative">
                        <!-- Close Button (Hidden for Stats, shown for Movie Details) -->
                        <button x-show="selectedMovie && !isStatsView" 
                                @click="selectedMovie = null; isStatsView = false; const url = new URL(window.location); url.searchParams.delete('movie'); window.history.pushState({}, '', url); window.dispatchEvent(new CustomEvent('toggle-movie-title', { detail: { show: false } }));" 
                                class="fixed top-8 right-8 z-[100] w-14 h-14 bg-white/5 backdrop-blur-xl border border-white/10 rounded-full flex items-center justify-center text-white hover:bg-rose-600 hover:border-rose-500 transition-all shadow-2xl group active:scale-90">
                            <i class="bi bi-x-lg text-xl group-hover:rotate-90 transition-transform"></i>
                        </button>

                        <div x-html="selectedMovie" class="w-full"></div>
                    </div>
                </template>
                <template x-if="!isStatsView && !selectedMovie">
                    <div class="animate-in fade-in duration-700">
                        @include('tenant.movies.partials.streaming-layout')
                    </div>
                </template>
                
                {{-- Global Streaming Loading State --}}
                <div x-show="loading" class="fixed inset-0 z-[100] flex items-center justify-center bg-[#0c0c0e]/80 backdrop-blur-xl">
                    <div class="flex flex-col items-center gap-6">
                        <div class="relative w-20 h-20">
                            <div class="absolute inset-0 border-4 border-blue-500/20 rounded-full"></div>
                            <div class="absolute inset-0 border-4 border-t-blue-500 rounded-full animate-spin"></div>
                        </div>
                        <span class="text-sm font-black text-white/40 uppercase tracking-[0.3em] animate-pulse">{{ __('Loading...') }}</span>
                    </div>
                </div>
            </div>
        </template>

        @if(\App\Models\Setting::get('boxset_quick_view_style', 'island') === 'modal')
            @include('tenant.movies.partials.boxset-modal')
        @else
            @include('tenant.movies.partials.boxset-popover')
        @endif
    </div>

    <!-- Custom CSS for no-scrollbar -->
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</x-app-layout>