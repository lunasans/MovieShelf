<x-admin-layout>
    <div class="p-6 md:p-10" x-data="tmdbImport()">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-white tracking-tight">TMDb Import</h1>
                <p class="text-white/50 mt-1 uppercase text-xs font-bold tracking-widest">Filme & Serien aus der Datenbank importieren</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex bg-white/5 p-1 rounded-xl border border-white/10">
                    <button @click="type = 'movie'; search()" :class="type === 'movie' ? 'bg-purple-500 text-white shadow-lg' : 'text-white/50 hover:text-white'" class="px-4 py-2 rounded-lg text-xs font-bold transition-all">FILME</button>
                    <button @click="type = 'tv'; search()" :class="type === 'tv' ? 'bg-purple-500 text-white shadow-lg' : 'text-white/50 hover:text-white'" class="px-4 py-2 rounded-lg text-xs font-bold transition-all">SERIEN</button>
                </div>
                <button @click="startAutoMatching()" class="glass-button flex items-center gap-2 group border-indigo-500/30 hover:bg-indigo-500/20 text-indigo-400">
                    <i class="bi bi-magic transition-transform group-hover:scale-110"></i>
                    Auto-Matching starten
                </button>
                <button @click="startMassUpdate()" class="glass-button flex items-center gap-2 group border-purple-500/30 hover:bg-purple-500/20 text-purple-400">
                    <i class="bi bi-arrow-repeat transition-transform group-hover:rotate-180 duration-700"></i>
                    Mass-Update starten
                </button>
                <a href="{{ route('admin.movies.index') }}" class="glass-button flex items-center gap-2 group">
                    <i class="bi bi-arrow-left transition-transform group-hover:-translate-x-1"></i>
                    Zurück
                </a>
            </div>
        </div>

        <div class="glass p-8 rounded-[2rem] mb-10">
            <div class="relative max-w-4xl mx-auto">
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="query" 
                        @input.debounce.500ms="search()"
                        placeholder="Titel suchen oder TMDb-URL einfügen..." 
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-purple-500/50 transition-all text-lg shadow-2xl"
                    >
                    <div class="absolute right-6 top-1/2 -translate-y-1/2 flex items-center gap-3">
                        <div x-show="loading" class="animate-spin h-6 w-6 border-2 border-purple-500 border-t-transparent rounded-full"></div>
                        <i class="bi bi-search text-white/20 text-xl"></i>
                    </div>
                </div>
                <p class="text-center text-white/20 text-[10px] mt-4 uppercase tracking-[0.2em] font-bold">Tipp: Du kannst auch einfach eine URL von themoviedb.org kopieren und hier einfügen</p>
            </div>
            
            <template x-if="error">
                <div class="mt-6 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-center font-bold" x-text="error"></div>
            </template>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <template x-for="item in results" :key="item.id">
                <div class="glass overflow-hidden rounded-[2.5rem] group flex flex-col h-full hover:border-white/20 transition-all duration-500 shadow-xl">
                    <div class="relative aspect-[2/3] overflow-hidden">
                        <template x-if="item.poster_path">
                            <img :src="'https://image.tmdb.org/t/p/w500' + item.poster_path" :alt="item.title || item.name" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        </template>
                        <template x-if="!item.poster_path">
                            <div class="w-full h-full bg-white/5 flex items-center justify-center">
                                <i class="bi bi-camera-video text-white/20 text-4xl"></i>
                            </div>
                        </template>
                        
                        <!-- Overlay Info -->
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 bg-black/60 backdrop-blur-md rounded-full text-[10px] font-black text-white/80 border border-white/10 uppercase tracking-widest" x-text="type === 'movie' ? 'Film' : 'Serie'"></span>
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-8">
                            <template x-if="type === 'movie'">
                                <form :action="'{{ route('admin.tmdb.import') }}'" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="tmdb_id" :value="item.id">
                                    <input type="hidden" name="media_type" :value="type">
                                    <button type="submit" class="w-full py-4 bg-white text-black font-black rounded-2xl hover:bg-purple-500 hover:text-white transition-all transform group-hover:translate-y-0 translate-y-4 duration-500 uppercase tracking-widest text-xs shadow-2xl">
                                        <i class="bi bi-download mr-2"></i> Importieren
                                    </button>
                                </form>
                            </template>
                            <template x-if="type === 'tv'">
                                <button @click="openSeasonModal(item)" class="w-full py-4 bg-white text-black font-black rounded-2xl hover:bg-purple-500 hover:text-white transition-all transform group-hover:translate-y-0 translate-y-4 duration-500 uppercase tracking-widest text-xs shadow-2xl">
                                    <i class="bi bi-list-ul mr-2"></i> Staffeln wählen
                                </button>
                            </template>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-white font-black text-lg leading-tight group-hover:text-purple-400 transition-colors" x-text="item.title || item.name"></h3>
                        <p class="text-white/40 text-xs font-bold mt-2 uppercase tracking-widest" x-text="(item.release_date || item.first_air_date) ? (item.release_date || item.first_air_date).substring(0, 4) : 'N/A'"></p>
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

        <!-- Season Selection Modal -->
        <div x-show="showSeasonModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             style="display: none;">
            
            <div class="glass w-full max-w-2xl rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/10 max-h-[90vh] flex flex-col" @click.away="showSeasonModal = false">
                <div class="p-8 border-b border-white/10 flex justify-between items-center bg-white/5">
                    <div>
                        <h2 class="text-2xl font-black text-white leading-tight" x-text="activeSeries?.name"></h2>
                        <p class="text-white/50 text-[10px] uppercase font-bold tracking-widest mt-1">Staffelauswahl</p>
                    </div>
                    <button @click="showSeasonModal = false" class="text-white/30 hover:text-white transition-colors">
                        <i class="bi bi-x-lg text-2xl"></i>
                    </button>
                </div>

                <div class="overflow-y-auto p-8 flex-1">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-white/40 text-xs font-bold uppercase tracking-widest" x-text="(activeSeries?.seasons?.length || 0) + ' Staffeln gefunden'"></span>
                        <div class="flex gap-4">
                            <button @click="selectAll()" class="text-purple-400 hover:text-purple-300 text-[10px] font-black uppercase tracking-widest">Alle wählen</button>
                            <button @click="selectedSeasons = []" class="text-rose-400 hover:text-rose-300 text-[10px] font-black uppercase tracking-widest">Keine wählen</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <template x-for="season in activeSeries?.seasons?.filter(s => s.season_number > 0)" :key="season.id">
                            <label class="group flex items-center gap-4 p-4 rounded-2xl border border-white/5 bg-white/5 hover:bg-white/10 hover:border-white/20 transition-all cursor-pointer">
                                <input type="checkbox" :value="season.season_number" x-model="selectedSeasons" class="w-6 h-6 rounded-lg bg-white/5 border-white/10 text-purple-600 focus:ring-purple-500/50">
                                <div class="w-12 h-18 bg-white/5 rounded-lg overflow-hidden flex-shrink-0">
                                    <template x-if="season.poster_path">
                                        <img :src="'https://image.tmdb.org/t/p/w92' + season.poster_path" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!season.poster_path">
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="bi bi-image text-white/10"></i>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <div class="text-white font-black" x-text="season.name"></div>
                                    <div class="text-white/40 text-[10px] uppercase font-bold tracking-widest" x-text="season.episode_count + ' Episoden'"></div>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <div class="p-8 bg-white/5 border-t border-white/10 flex gap-4">
                    <button @click="showSeasonModal = false" class="flex-1 py-4 text-white/50 font-black uppercase tracking-widest text-xs hover:text-white transition-colors">Abbrechen</button>
                    <button @click="startImport()" :disabled="selectedSeasons.length === 0" class="flex-[2] py-4 bg-purple-600 hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black rounded-2xl transition-all shadow-xl uppercase tracking-widest text-xs">
                        <i class="bi bi-download mr-2"></i> <span x-text="'Jetzt ' + selectedSeasons.length + ' Staffel(n) importieren'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mass Update Progress Modal -->
        <div x-show="showMassUpdateModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
             style="display: none;">
            
            <div class="glass w-full max-w-xl rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/10 p-10 text-center">
                <div class="w-20 h-20 bg-purple-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6 text-purple-400 text-3xl">
                    <i class="bi bi-arrow-repeat" :class="updating ? 'animate-spin' : ''"></i>
                </div>
                
                <h2 class="text-2xl font-black text-white mb-2" x-text="updating ? (mode === 'matching' ? 'Auto-Matching läuft...' : 'Mass-Update läuft...') : 'Vorgang abgeschlossen!'"></h2>
                <p class="text-white/50 text-sm mb-8" x-text="updating ? 'Bitte lass dieses Fenster offen.' : (mode === 'matching' ? 'Das Auto-Matching wurde beendet.' : 'Alle Filme wurden erfolgreich aktualisiert.')"></p>

                <div class="mb-4 flex justify-between items-end">
                    <span class="text-[10px] font-black text-white/30 uppercase tracking-[0.2em]" x-text="updateCount + ' von ' + updateTotal + ' verarbeitet'"></span>
                    <span class="text-sm font-black text-purple-400" x-text="updateTotal > 0 ? Math.round((updateCount / updateTotal) * 100) : 0 + '%'"></span>
                </div>

                <div class="w-full h-4 bg-white/5 rounded-full overflow-hidden mb-10 border border-white/5 p-1">
                    <div class="h-full bg-gradient-to-r from-purple-600 to-indigo-600 rounded-full transition-all duration-500" :style="'width: ' + (updateTotal > 0 ? (updateCount / updateTotal * 100) : 0) + '%'"></div>
                </div>

                <div class="space-y-4">
                    <div x-show="updating" class="text-white/40 text-xs italic" x-text="(mode === 'matching' ? 'Prüfe: ' : 'Aktualisiere: ') + currentUpdateTitle"></div>
                    <div x-show="!updating && mode === 'matching'" class="p-4 bg-white/5 rounded-2xl text-xs text-white/60 mb-4">
                        <span class="font-bold text-green-400" x-text="matchedCount"></span> Treffer erzielt, 
                        <span class="font-bold text-rose-400" x-text="failedCount"></span> nicht gefunden.
                    </div>
                    <button x-show="!updating" @click="showMassUpdateModal = false; window.location.reload()" class="w-full py-4 bg-white text-black font-black rounded-2xl hover:bg-purple-500 hover:text-white transition-all shadow-xl uppercase tracking-widest text-xs">Schließen</button>
                    <button x-show="updating" @click="cancelUpdate()" class="text-white/20 hover:text-rose-500 text-[10px] font-black uppercase tracking-widest transition-colors">Vorgang abbrechen</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tmdbImport', () => ({
                query: '',
                type: 'movie',
                results: [],
                loading: false,
                error: null,
                showSeasonModal: false,
                activeSeries: null,
                selectedSeasons: [],
                showMassUpdateModal: false,
                updating: false,
                updateTotal: 0,
                updateCount: 0,
                currentUpdateTitle: '',
                updateQueue: [],
                shouldCancel: false,
                mode: 'update',
                matchedCount: 0,
                failedCount: 0,
                search() {
                    if (this.query.length < 3) {
                        if (this.query.includes('themoviedb.org/')) {
                            this.handleUrl();
                            return;
                        }
                        this.results = [];
                        return;
                    }
                    this.loading = true;
                    this.error = null;
                    fetch(`{{ route('admin.tmdb.search') }}?query=${encodeURIComponent(this.query)}&type=${this.type}`)
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
                },
                openSeasonModal(item) {
                    this.loading = true;
                    fetch(`{{ route('admin.tmdb.details') }}?tmdb_id=${item.id}&type=tv`)
                        .then(res => res.json())
                        .then(data => {
                            this.activeSeries = data;
                            this.selectedSeasons = data.seasons?.filter(s => s.season_number > 0).map(s => s.season_number) || [];
                            this.showSeasonModal = true;
                            this.loading = false;
                        })
                        .catch(err => {
                            this.error = 'Details konnten nicht geladen werden.';
                            this.loading = false;
                        });
                },
                selectAll() {
                    this.selectedSeasons = this.activeSeries.seasons?.filter(s => s.season_number > 0).map(s => s.season_number) || [];
                },
                startImport() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('admin.tmdb.import') }}';
                    
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    const id = document.createElement('input');
                    id.type = 'hidden';
                    id.name = 'tmdb_id';
                    id.value = this.activeSeries.id;
                    form.appendChild(id);

                    const media = document.createElement('input');
                    media.type = 'hidden';
                    media.name = 'media_type';
                    media.value = 'tv';
                    form.appendChild(media);

                    this.selectedSeasons.forEach(s => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'seasons[]';
                        input.value = s;
                        form.appendChild(input);
                    });

                    document.body.appendChild(form);
                    form.submit();
                },
                handleUrl() {
                    let tmdbId = null;
                    let mediaType = 'movie';
                    
                    if (this.query.includes('/movie/')) {
                        tmdbId = this.query.match(/\/movie\/(\d+)/)?.[1];
                        mediaType = 'movie';
                    } else if (this.query.includes('/tv/')) {
                        tmdbId = this.query.match(/\/tv\/(\d+)/)?.[1];
                        mediaType = 'tv';
                    }
                    
                    if (tmdbId) {
                        if (mediaType === 'tv') {
                            this.openSeasonModal({id: tmdbId});
                        } else {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '{{ route('admin.tmdb.import') }}';
                            form.innerHTML = `
                                @csrf
                                <input type="hidden" name="tmdb_id" value="${tmdbId}">
                                <input type="hidden" name="media_type" value="${mediaType}">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    }
                },
                async startAutoMatching() {
                    this.loading = true;
                    this.error = null;
                    try {
                        const res = await fetch('{{ route('admin.tmdb.unlinked-list') }}');
                        const movies = await res.json();
                        
                        if (movies.length === 0) {
                            this.error = 'Alle Filme scheinen bereits verknüpft zu sein!';
                            this.loading = false;
                            return;
                        }

                        this.updateQueue = movies;
                        this.updateTotal = movies.length;
                        this.updateCount = 0;
                        this.matchedCount = 0;
                        this.failedCount = 0;
                        this.shouldCancel = false;
                        this.mode = 'matching';
                        this.showMassUpdateModal = true;
                        this.updating = true;
                        this.loading = false;
                        
                        this.processNextInQueue();
                    } catch (err) {
                        this.error = 'Fehler beim Laden der Filmliste.';
                        this.loading = false;
                    }
                },
                async startMassUpdate() {
                    this.loading = true;
                    this.error = null;
                    try {
                        const res = await fetch('{{ route('admin.tmdb.update-list') }}');
                        const movies = await res.json();
                        
                        if (movies.length === 0) {
                            this.error = 'Keine Filme mit TMDb-ID zum Aktualisieren gefunden. (Bestehende Filme müssen erst verknüpft werden)';
                            this.loading = false;
                            return;
                        }

                        this.updateQueue = movies;
                        this.updateTotal = movies.length;
                        this.updateCount = 0;
                        this.shouldCancel = false;
                        this.mode = 'update';
                        this.showMassUpdateModal = true;
                        this.updating = true;
                        this.loading = false;
                        
                        this.processNextInQueue();
                    } catch (err) {
                        this.error = 'Fehler beim Laden der Filmliste.';
                        this.loading = false;
                    }
                },
                async processNextInQueue() {
                    if (this.updateQueue.length === 0 || this.shouldCancel) {
                        this.updating = false;
                        return;
                    }

                    const movie = this.updateQueue.shift();
                    this.currentUpdateTitle = movie.title;

                    try {
                        const endpoint = this.mode === 'matching' ? '{{ route('admin.tmdb.auto-link') }}' : '{{ route('admin.tmdb.bulk-update') }}';
                        const res = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ movie_id: movie.id })
                        });
                        const data = await res.json();
                        
                        if (this.mode === 'matching') {
                            if (data.success) this.matchedCount++;
                            else this.failedCount++;
                        }

                        this.updateCount++;
                        // Delay to avoid rate limiting
                        setTimeout(() => this.processNextInQueue(), 600);
                    } catch (err) {
                        console.error('Operation failed for ' + movie.title);
                        if (this.mode === 'matching') this.failedCount++;
                        this.updateCount++;
                        setTimeout(() => this.processNextInQueue(), 600);
                    }
                },
                cancelUpdate() {
                    this.shouldCancel = true;
                    this.updating = false;
                    this.showMassUpdateModal = false;
                }
            }));
        });
    </script>

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
