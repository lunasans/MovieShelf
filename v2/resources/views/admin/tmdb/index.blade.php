<x-admin-layout>
    <div class="p-6 md:p-10" x-data="{ 
        query: '', 
        results: [], 
        loading: false, 
        error: null,
        search() {
            if (this.query.length < 3) {
                this.results = [];
                return;
            }
            this.loading = true;
            this.error = null;
            fetch(`{{ route('admin.tmdb.search') }}?query=${encodeURIComponent(this.query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        this.error = data.error;
                        this.results = [];
                    } else {
                        this.results = data.results || [];
                    }
                    this.loading = false;
                })
                .catch(err => {
                    this.error = 'Suche fehlgeschlagen.';
                    this.loading = false;
                });
        }
    }">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-white tracking-tight">TMDb Import</h1>
                <p class="text-white/50 mt-1 uppercase text-xs font-bold tracking-widest">Filme aus der Datenbank importieren</p>
            </div>
            <a href="{{ route('admin.movies.index') }}" class="glass-button flex items-center gap-2 group">
                <i class="bi bi-arrow-left transition-transform group-hover:-translate-x-1"></i>
                Zurück zur Liste
            </a>
        </div>

        <div class="glass p-8 rounded-[2rem] mb-10">
            <div class="relative max-w-2xl mx-auto">
                <input 
                    type="text" 
                    x-model="query" 
                    @input.debounce.500ms="search()"
                    placeholder="Filmtitel suchen..." 
                    class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition-all text-lg"
                >
                <div class="absolute right-4 top-1/2 -translate-y-1/2" x-show="loading">
                    <div class="animate-spin h-6 w-6 border-2 border-purple-500 border-t-transparent rounded-full"></div>
                </div>
            </div>
            
            <template x-if="error">
                <div class="mt-4 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl text-center" x-text="error"></div>
            </template>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="movie in results" :key="movie.id">
                <div class="glass overflow-hidden rounded-[2rem] group flex flex-col h-full hover:border-white/20 transition-all duration-500">
                    <div class="relative aspect-[2/3]">
                        <template x-if="movie.poster_path">
                            <img :src="'https://image.tmdb.org/t/p/w500' + movie.poster_path" :alt="movie.title" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        </template>
                        <template x-if="!movie.poster_path">
                            <div class="w-full h-full bg-white/5 flex items-center justify-center">
                                <i class="bi bi-camera-video text-white/20 text-4xl"></i>
                            </div>
                        </template>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-6">
                            <form :action="'{{ route('admin.tmdb.import') }}'" method="POST" class="w-full">
                                @csrf
                                <input type="hidden" name="tmdb_id" :value="movie.id">
                                <button type="submit" class="w-full py-3 bg-white text-black font-black rounded-xl hover:bg-purple-400 hover:text-white transition-colors uppercase tracking-widest text-xs">
                                    Jetzt Importieren
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-white font-bold leading-tight group-hover:text-purple-400 transition-colors" x-text="movie.title"></h3>
                        <p class="text-white/40 text-sm mt-1" x-text="movie.release_date ? movie.release_date.substring(0, 4) : 'N/A'"></p>
                    </div>
                </div>
            </template>
        </div>

        <template x-if="results.length === 0 && query.length >= 3 && !loading && !error">
            <div class="text-center py-20">
                <i class="bi bi-search text-white/10 text-6xl mb-4 block"></i>
                <p class="text-white/40">Keine Filme für "<span class="text-white" x-text="query"></span>" gefunden.</p>
            </div>
        </template>
        
        <template x-if="query.length < 3 && !loading">
            <div class="text-center py-20">
                <i class="bi bi-lightning-charge text-purple-500/20 text-6xl mb-4 block"></i>
                <p class="text-white/40 italic">Gib mindestens 3 Zeichen ein, um die Suche zu starten.</p>
            </div>
        </template>
    </div>

    <style>
        .glass-button {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px border rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        .glass-button:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }
    </style>
</x-admin-layout>
