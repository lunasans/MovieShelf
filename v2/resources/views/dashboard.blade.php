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
         @stats-open.window="fetchStats()"
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
                @forelse ($movies as $movie)
                    <div class="group cursor-pointer" @click="fetchDetails({{ $movie->id }})">
                        <div class="relative aspect-[2/3] rounded-2xl overflow-hidden glass border border-white/5 shadow-xl transition-all duration-500 group-hover:scale-[1.03] group-hover:shadow-blue-500/20 group-hover:border-white/20">
                            <!-- Movie Cover Placeholder -->
                            <div class="absolute inset-0 bg-gray-800 flex items-center justify-center">
                                @if($movie->cover_id)
                                    <img src="{{ Storage::url($movie->cover_id) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black to-transparent z-10"></div>
                                @else
                                    <i class="bi bi-film text-4xl text-gray-700"></i>
                                @endif
                            </div>

                            <!-- Rating Badge -->
                            <div class="absolute top-3 right-3 z-20 flex flex-col gap-2">
                                <div class="bg-blue-600/90 backdrop-blur-md px-2 py-1 rounded-lg border border-white/20 flex items-center gap-1 shadow-lg">
                                    <i class="bi bi-star-fill text-[10px] text-yellow-400"></i>
                                    <span class="text-[11px] font-bold text-white">8.2</span>
                                </div>
                                @if($movie->boxsetChildren->count() > 0)
                                    <div class="bg-purple-600/90 backdrop-blur-md px-2 py-1 rounded-lg border border-white/20 flex items-center gap-1 shadow-lg">
                                        <i class="bi bi-collection-play text-[10px] text-white"></i>
                                        <span class="text-[11px] font-bold text-white">{{ $movie->boxsetChildren->count() }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Content Below -->
                        <div class="mt-3 px-1">
                            <h3 class="text-sm font-bold text-gray-200 truncate group-hover:text-blue-400 transition-colors">
                                {{ $movie->title }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[11px] text-gray-500">{{ $movie->year }}</span>
                                <span class="w-1 h-1 bg-gray-700 rounded-full"></span>
                                <span class="text-[11px] text-blue-400 font-medium">{{ $movie->collection_type }}</span>
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
