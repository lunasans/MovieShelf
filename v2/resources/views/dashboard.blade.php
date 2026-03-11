<x-app-layout>
    <div class="layout transition-all duration-500 ease-in-out" 
         :class="{ 'layout-stats-active': isStatsView }"
         x-data="{ 
            selectedMovie: null, 
            isStatsView: false,
            loading: false,
            error: null,
            fetchDetails(id) {
                this.isStatsView = false;
                this.loading = true;
                this.error = null;
                fetch(`/movies/${id}/details`)
                    .then(res => res.text())
                    .then(html => {
                        this.selectedMovie = html;
                        this.loading = false;
                    })
                    .catch(err => {
                        this.error = 'Fehler beim Laden der Details.';
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
                        this.error = 'Fehler beim Laden des Profils.';
                        this.loading = false;
                    });
            },
            fetchStats() {
                this.isStatsView = true;
                this.loading = true;
                this.error = null;
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
                        this.error = 'Fehler beim Laden der Statistik.';
                        this.loading = false;
                    });
            },
            fetchImpressum() {
                this.isStatsView = true; // Use wide layout like statistics
                this.loading = true;
                this.error = null;
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
                        this.error = 'Fehler beim Laden des Impressums.';
                        this.loading = false;
                    });
            },
            initCharts() {
                const chartOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: 'rgba(255, 255, 255, 0.5)', font: { size: 10, weight: 'bold' } } },
                        x: { grid: { display: false }, ticks: { color: 'rgba(255, 255, 255, 0.5)', font: { size: 10, weight: 'bold' } } }
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
                                backgroundColor: type === 'line' ? 'rgba(96, 165, 250, 0.1)' : ['#3b82f6', '#f59e0b', '#ef4444', '#10b981', '#8b5cf6', '#a855f7'],
                                borderColor: type === 'line' ? '#60a5fa' : 'transparent',
                                borderWidth: type === 'line' ? 3 : 0,
                                fill: type === 'line',
                                tension: type === 'line' ? 0.4 : 0,
                                borderRadius: type === 'bar' ? 8 : 0,
                            }]
                        },
                        options: (type === 'doughnut' || type === 'polarArea') 
                            ? { ...chartOptions, scales: { x: { display: false }, y: { display: false } }, plugins: { legend: { display: true, position: 'bottom', labels: { color: '#fff' } } } }
                            : chartOptions
                    };

                    new Chart(canvas, config);
                });
            }
         }"
         x-init="() => {
             const urlParams = new URLSearchParams(window.location.search);
             const movieId = urlParams.get('movie');
             const actorId = urlParams.get('actor');
             const showStats = urlParams.get('stats');
             const showImpressum = urlParams.get('impressum');

             if (movieId) fetchDetails(movieId);
             else if (actorId) fetchActor(actorId);
             else if (showStats) fetchStats();
             else if (showImpressum) fetchImpressum();
          }"
         @stats-open.window="fetchStats()"
         @impressum-open.window="fetchImpressum()"
    >
        <!-- Film-Liste Area (Left Column) -->
        <section class="film-list-area shadow-2xl">
            <!-- Tabs for Collection Types -->
            <div class="flex items-center justify-between mb-8 gap-4 flex-wrap">
                <div class="flex items-center gap-2 bg-white/5 border border-white/10 p-1.5 rounded-2xl overflow-x-auto no-scrollbar max-w-full">
                    <a href="{{ route('dashboard') }}" 
                        class="px-5 py-2 rounded-xl text-sm font-semibold transition-all {{ !request('type') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        {{ __('Alle') }}
                    </a>
                    @foreach($collectionTypes as $type)
                        <a href="{{ route('dashboard', ['type' => $type, 'q' => request('q')]) }}" 
                            class="px-5 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition-all {{ request('type') === $type ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            {{ $type }}
                        </a>
                    @endforeach
                </div>

                <!-- View Mode Toggle -->
                <div class="flex gap-2 bg-white/5 border border-white/10 p-1.5 rounded-2xl shrink-0">
                    <button class="p-2 rounded-xl bg-blue-500/20 text-blue-400 border border-blue-500/30">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button class="p-2 rounded-xl text-gray-500 hover:text-white hover:bg-white/5">
                        <i class="bi bi-list-ul"></i>
                    </button>
                </div>
            </div>

            <!-- List Area Header -->
            <div class="flex items-center justify-between mb-6 px-2">
                <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-500">
                    @if(request('q'))
                        {{ __('Search Results for ":query"', ['query' => request('q')]) }}
                    @else
                        {{ request('type') ?? __('Movie Collection') }}
                    @endif
                </h2>
                <span class="text-gray-500 text-sm font-medium">{{ __('Total: :count', ['count' => $movies->total()]) }}</span>
            </div>

            <!-- Film Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @forelse ($movies as $movie)
                    <div class="group cursor-pointer" 
                         x-data="{ isWatched: {{ Auth::check() && Auth::user()->watchedMovies()->where('movie_id', $movie->id)->exists() ? 'true' : 'false' }} }"
                         @movie-watched-updated.window="if($event.detail.movieId === {{ $movie->id }}) isWatched = $event.detail.watched"
                         @click="fetchDetails({{ $movie->id }})">
                        <div class="relative aspect-[2/3] rounded-3xl overflow-hidden glass border border-white/10 shadow-2xl transition-all duration-500 group-hover:scale-[1.05] group-hover:shadow-blue-500/30 group-hover:border-blue-500/50">
                            <!-- Movie Cover Placeholder -->
                            <div class="absolute inset-0 bg-gray-900 flex items-center justify-center">
                                @if($movie->cover_id)
                                    <img src="{{ Storage::url($movie->cover_id) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
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
                @empty
                    <div class="col-span-full py-20 text-center glass rounded-2xl border-dashed">
                        <i class="bi bi-search text-6xl text-gray-800 mb-4 block"></i>
                        <h3 class="text-xl font-bold text-white">{{ __('Keine Filme gefunden') }}</h3>
                        <p class="text-gray-500 mt-2">{{ __('Passen Sie Ihre Suche oder Filter an.') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-12">
                {{ $movies->links() }}
            </div>
        </section>

        <!-- Detail Panel (Right Column) -->
        <aside class="detail-panel shadow-2xl block relative min-h-[600px]">
            <!-- Loading State -->
            <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm z-50 rounded-2xl">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin"></div>
                    <span class="text-sm font-medium text-gray-400 tracking-widest uppercase italic">{{ __('Lade Details...') }}</span>
                </div>
            </div>

            <!-- Error State -->
            <div x-show="error" class="h-full flex flex-col items-center justify-center p-8 text-center">
                <i class="bi bi-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                <p x-text="error" class="text-white font-medium"></p>
                <button @click="error = null" class="mt-4 text-sm text-blue-400 hover:underline">{{ __('Schließen') }}</button>
            </div>

            <!-- Content Area: Show Latest Movies when no movie is selected -->
            <div x-show="!selectedMovie && !loading && !error" class="h-full p-8 overflow-y-auto no-scrollbar">
                @include('movies.partials.latest', ['latestMovies' => $latestMovies])
            </div>

            <!-- Dynamic Detail Content -->
            <div x-show="selectedMovie && !loading" x-html="selectedMovie" class="h-full"></div>
        </aside>
    </div>

    <!-- Custom CSS for no-scrollbar -->
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</x-app-layout>
