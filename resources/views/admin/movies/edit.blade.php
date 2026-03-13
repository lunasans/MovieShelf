<x-admin-layout>
    @section('header_title', 'Film bearbeiten')

    <div class="max-w-4xl mx-auto" x-data="tmdbSearch()">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('admin.movies.index') }}" class="text-sm text-gray-500 hover:text-blue-400 transition-colors flex items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                Zurück zur Übersicht
            </a>
            
            <button @click="openModal()" type="button" class="px-4 py-2 bg-purple-600/20 hover:bg-purple-600/30 text-purple-400 rounded-xl font-bold text-xs transition-all border border-purple-500/20 flex items-center gap-2">
                <i class="bi bi-search"></i>
                TMDb Suche
            </button>
        </div>

        <form action="{{ route('admin.movies.update', $movie) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- General Info -->
            <div class="glass p-8 rounded-3xl border-white/5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="bi bi-info-circle text-blue-400"></i>
                    Allgemeine Informationen
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="title" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Titel *</label>
                        <input type="text" name="title" id="title" x-model="formData.title" value="{{ old('title', $movie->title) }}" required
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                        @error('title') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="year" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Erscheinungsjahr *</label>
                        <input type="number" name="year" id="year" x-model="formData.year" value="{{ old('year', $movie->year) }}" required
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>

                    <div>
                        <label for="collection_type" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Medientyp *</label>
                        <select name="collection_type" id="collection_type" x-model="formData.collection_type" required
                                class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all appearance-none">
                            <option value="Owned" {{ old('collection_type', $movie->collection_type) == 'Owned' ? 'selected' : '' }}>Owned</option>
                            <option value="Serie" {{ old('collection_type', $movie->collection_type) == 'Serie' ? 'selected' : '' }}>Serie</option>
                            <option value="Stream" {{ old('collection_type', $movie->collection_type) == 'Stream' ? 'selected' : '' }}>Stream</option>
                        </select>
                    </div>

                    <div>
                        <label for="genre" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Genre</label>
                        <input type="text" name="genre" id="genre" x-model="formData.genre" value="{{ old('genre', $movie->genre) }}"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>

                    <div>
                        <label for="runtime" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Laufzeit (Min.)</label>
                        <input type="number" name="runtime" id="runtime" x-model="formData.runtime" value="{{ old('runtime', $movie->runtime) }}"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>

                    <div>
                        <label for="rating" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Bewertung (TMDb)</label>
                        <input type="number" step="0.1" min="0" name="rating" id="rating" x-model="formData.rating" value="{{ old('rating', $movie->rating ? round($movie->rating, 1) : null) }}"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>

                    <div>
                        <label for="rating_age" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">FSK</label>
                        <select name="rating_age" id="rating_age" x-model="formData.rating_age"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all appearance-none">
                            <option value="">Keine Angabe</option>
                            @foreach([0, 6, 12, 16, 18] as $age)
                                <option value="{{ $age }}" {{ old('rating_age', $movie->rating_age) == $age ? 'selected' : '' }}>FSK {{ $age }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="created_at" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Hinzugefügt am</label>
                        <input type="date" name="created_at" id="created_at" value="{{ old('created_at', $movie->created_at?->format('Y-m-d')) }}"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>
                </div>
            </div>

            <!-- Media -->
            <div class="glass p-8 rounded-3xl border-white/5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="bi bi-play-circle text-red-500"></i>
                    Medien & Links
                </h3>

                <div class="space-y-6">
                    <div>
                        <label for="trailer_url" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Trailer URL (YouTube)</label>
                        <input type="url" name="trailer_url" id="trailer_url" x-model="formData.trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}"
                               placeholder="https://www.youtube.com/watch?v=..."
                               class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                    </div>

                    <div>
                        <label for="overview" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Handlung / Beschreibung</label>
                        <textarea name="overview" id="overview" rows="5" x-model="formData.overview"
                                  class="w-full bg-white/5 border border-white/10 rounded-3xl py-4 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all leading-relaxed">{{ old('overview', $movie->overview) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-4">
                <a href="{{ route('admin.movies.index') }}" class="px-8 py-3 rounded-2xl font-bold text-sm text-gray-400 hover:bg-white/5 transition-all">
                    Abbrechen
                </a>
                <button type="submit" class="px-10 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-bold text-sm transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2">
                    <i class="bi bi-save"></i>
                    Änderungen speichern
                </button>
            </div>
        </form>

        <!-- TMDb Search Modal -->
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             style="display: none;">
            
            <div class="glass w-full max-w-2xl rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/10 max-h-[90vh] flex flex-col" @click.away="showModal = false">
                <div class="p-8 border-b border-white/10 flex justify-between items-center bg-white/5">
                    <div>
                        <h2 class="text-2xl font-black text-white leading-tight">TMDb Suche</h2>
                        <p class="text-white/50 text-[10px] uppercase font-bold tracking-widest mt-1">Informationen synchronisieren</p>
                    </div>
                    <button @click="showModal = false" class="text-white/30 hover:text-white transition-colors">
                        <i class="bi bi-x-lg text-2xl"></i>
                    </button>
                </div>

                <div class="p-8 pb-4">
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="searchQuery" 
                            @input.debounce.500ms="search()"
                            placeholder="Titel suchen..." 
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition-all"
                        >
                        <div class="absolute right-6 top-1/2 -translate-y-1/2 flex items-center gap-3">
                            <div x-show="loading" class="animate-spin h-5 w-5 border-2 border-purple-500 border-t-transparent rounded-full"></div>
                            <i class="bi bi-search text-white/20"></i>
                        </div>
                    </div>
                </div>

                <div class="overflow-y-auto p-8 pt-0 flex-1">
                    <div class="grid grid-cols-1 gap-4">
                        <template x-for="item in results" :key="item.id">
                            <div @click="selectItem(item)" class="group flex items-center gap-4 p-4 rounded-2xl border border-white/5 bg-white/5 hover:bg-white/10 hover:border-white/20 transition-all cursor-pointer">
                                <div class="w-16 h-24 bg-white/5 rounded-lg overflow-hidden flex-shrink-0">
                                    <template x-if="item.poster_path">
                                        <img :src="'https://image.tmdb.org/t/p/w92' + item.poster_path" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.poster_path">
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="bi bi-camera-video text-white/10"></i>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <div class="text-white font-black" x-text="item.title || item.name"></div>
                                    <div class="text-white/40 text-[10px] uppercase font-bold tracking-widest" x-text="(item.release_date || item.first_air_date || '').substring(0, 4)"></div>
                                    <div class="text-white/30 text-xs mt-2 line-clamp-2" x-text="item.overview"></div>
                                </div>
                                <div class="text-purple-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="bi bi-check2-circle text-2xl"></i>
                                </div>
                            </div>
                        </template>
                    </div>

                    <template x-if="results.length === 0 && searchQuery.length >= 3 && !loading">
                        <div class="text-center py-10">
                            <p class="text-white/40">Keine Ergebnisse gefunden.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tmdbSearch', () => ({
                showModal: false,
                searchQuery: '{{ str_replace("'", "\'", $movie->title) }}',
                results: [],
                loading: false,
                formData: {
                    title: '{{ str_replace("'", "\'", old('title', $movie->title)) }}',
                    year: '{{ old('year', $movie->year) }}',
                    collection_type: '{{ old('collection_type', $movie->collection_type) }}',
                    genre: '{{ str_replace("'", "\'", old('genre', $movie->genre)) }}',
                    runtime: '{{ old('runtime', $movie->runtime) }}',
                    rating: '{{ old('rating', $movie->rating ? round($movie->rating, 1) : null) }}',
                    rating_age: '{{ old('rating_age', $movie->rating_age) }}',
                    trailer_url: '{{ old('trailer_url', $movie->trailer_url) }}',
                    overview: `{!! str_replace("`", "\`", old('overview', $movie->overview)) !!}`
                },
                openModal() {
                    this.showModal = true;
                    if (this.results.length === 0) {
                        this.search();
                    }
                },
                search() {
                    if (this.searchQuery.length < 3) return;
                    this.loading = true;
                    const type = this.formData.collection_type === 'Serie' ? 'tv' : 'movie';
                    fetch(`{{ route('admin.tmdb.search') }}?query=${encodeURIComponent(this.searchQuery)}&type=${type}`)
                        .then(res => res.json())
                        .then(data => {
                            this.results = data.results || [];
                            this.loading = false;
                        })
                        .catch(() => {
                            this.loading = false;
                        });
                },
                selectItem(item) {
                    const type = this.formData.collection_type === 'Serie' ? 'tv' : 'movie';
                    this.loading = true;
                    fetch(`{{ route('admin.tmdb.details') }}?tmdb_id=${item.id}&type=${type}`)
                        .then(res => res.json())
                        .then(data => {
                            this.formData.title = data.title || data.name;
                            this.formData.year = (data.release_date || data.first_air_date || '').substring(0, 4);
                            this.formData.genre = (data.genres || []).map(g => g.name).join(', ');
                            this.formData.runtime = data.runtime || (data.episode_run_time ? data.episode_run_time[0] : null);
                            this.formData.rating = data.vote_average ? Math.round(data.vote_average * 10) / 10 : null;
                            this.formData.overview = data.overview;
                            
                            // Extract Trailer
                            if (data.videos && data.videos.results) {
                                const trailer = data.videos.results.find(v => v.site === 'YouTube' && (v.type === 'Trailer' || v.type === 'Teaser'));
                                if (trailer) {
                                    this.formData.trailer_url = `https://www.youtube.com/watch?v=${trailer.key}`;
                                }
                            }

                            // Extract Rating (FSK)
                            let rating = null;
                            if (data.release_dates && data.release_dates.results) {
                                const de = data.release_dates.results.find(r => r.iso_3166_1 === 'DE');
                                if (de && de.release_dates) {
                                    const cert = de.release_dates.find(rd => rd.certification);
                                    if (cert) rating = cert.certification;
                                }
                            } else if (data.content_ratings && data.content_ratings.results) {
                                const de = data.content_ratings.results.find(r => r.iso_3166_1 === 'DE');
                                if (de) rating = de.rating;
                            }
                            if (rating) this.formData.rating_age = rating;

                            this.showModal = false;
                            this.loading = false;
                        })
                        .catch(() => {
                            this.loading = false;
                        });
                }
            }));
        });
    </script>
</x-admin-layout>
