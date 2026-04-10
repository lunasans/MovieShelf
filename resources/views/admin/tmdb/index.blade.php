<x-admin-layout>
    <div class="p-6 md:p-10" x-data="tmdbImport()">
        <!-- Header Section -->
        <div class="mb-10 flex flex-col xl:flex-row xl:items-center justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-white tracking-tight">TMDb Import</h1>
                <p class="text-white/40 mt-1 uppercase text-xs font-black tracking-[0.3em]">Globale Filmdatenbank durchsuchen</p>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex bg-white/5 p-1.5 rounded-2xl border border-white/10">
                    <button @click="type = 'movie'; search()" :class="type === 'movie' ? 'bg-rose-600 text-white shadow-xl' : 'text-white/30 hover:text-white'" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">FILME</button>
                    <button @click="type = 'tv'; search()" :class="type === 'tv' ? 'bg-rose-600 text-white shadow-xl' : 'text-white/30 hover:text-white'" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">SERIEN</button>
                </div>
                <button @click="startAutoMatching()" class="px-6 py-3.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 rounded-2xl font-black text-xs uppercase tracking-widest transition-all flex items-center gap-2 group">
                    <i class="bi bi-magic transition-transform group-hover:scale-110"></i>
                    Auto-Match
                </button>
                <button @click="startMassUpdate()" class="px-6 py-3.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 rounded-2xl font-black text-xs uppercase tracking-widest transition-all flex items-center gap-2 group">
                    <i class="bi bi-arrow-repeat transition-transform group-hover:rotate-180 duration-700"></i>
                    Mass-Update
                </button>
                <a href="{{ route('admin.movies.index') }}" class="px-6 py-3.5 bg-white/5 hover:bg-white/10 text-white/40 hover:text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all flex items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    Zurück
                </a>
            </div>
        </div>

        <!-- Search Bar Section -->
        <div class="glass p-10 rounded-[3rem] mb-12 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-rose-600/5 to-transparent pointer-events-none"></div>
            <div class="relative max-w-4xl mx-auto">
                <div class="relative">
                    <input
                        type="text"
                        x-model="query"
                        @input.debounce.500ms="search()"
                        placeholder="Titel suchen oder TMDb-URL einfügen..."
                        class="w-full bg-white/5 border border-white/10 rounded-[1.5rem] px-8 py-6 text-white placeholder-white/20 focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all text-xl shadow-2xl"
                    >
                    <div class="absolute right-8 top-1/2 -translate-y-1/2 flex items-center gap-4">
                        <div x-show="loading" x-cloak class="animate-spin h-6 w-6 border-2 border-rose-500 border-t-transparent rounded-full"></div>
                        <i class="bi bi-search text-white/10 text-2xl"></i>
                    </div>
                </div>
                <p class="text-center text-white/20 text-[10px] mt-6 uppercase tracking-[0.3em] font-black italic">Tipp: Du kannst auch TMDb-Links direkt hier einfügen</p>
            </div>
            <template x-if="error">
                <div class="mt-8 p-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-[1.5rem] text-center font-bold animate-in fade-in slide-in-from-top-4" x-text="error"></div>
            </template>
        </div>

        <!-- Results Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10">
            <template x-for="item in results" :key="item.id">
                <div class="glass overflow-hidden rounded-[3rem] group flex flex-col h-full hover:border-rose-500/30 transition-all duration-700 shadow-2xl relative">
                    <div class="relative aspect-[2/3] overflow-hidden">
                        <template x-if="item.poster_path">
                            <img :src="'https://image.tmdb.org/t/p/w500' + item.poster_path" :alt="item.title || item.name" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                        </template>
                        <template x-if="!item.poster_path">
                            <div class="w-full h-full bg-white/5 flex items-center justify-center">
                                <i class="bi bi-camera-video text-white/10 text-5xl"></i>
                            </div>
                        </template>

                        <!-- Type Badge -->
                        <div class="absolute top-6 left-6">
                            <span class="px-4 py-1.5 bg-black/60 backdrop-blur-xl rounded-full text-[10px] font-black text-white border border-white/10 uppercase tracking-widest" x-text="type === 'movie' ? 'Film' : 'Serie'"></span>
                        </div>

                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-rose-900/90 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500 flex items-end p-8">
                            <template x-if="type === 'movie'">
                                <form :action="'{{ route('admin.tmdb.import') }}'" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="tmdb_id" :value="item.id">
                                    <input type="hidden" name="media_type" :value="type">
                                    <button type="submit" class="w-full py-5 bg-white text-black font-black rounded-2xl hover:bg-rose-600 hover:text-white transition-all transform group-hover:translate-y-0 translate-y-6 duration-500 uppercase tracking-widest text-[10px] shadow-2xl">
                                        <i class="bi bi-download mr-2 text-base"></i> Importieren
                                    </button>
                                </form>
                            </template>
                            <template x-if="type === 'tv'">
                                <button @click="openSeasonModal(item)" class="w-full py-5 bg-white text-black font-black rounded-2xl hover:bg-rose-600 hover:text-white transition-all transform group-hover:translate-y-0 translate-y-6 duration-500 uppercase tracking-widest text-[10px] shadow-2xl">
                                    <i class="bi bi-list-ul mr-2 text-base"></i> Staffeln wählen
                                </button>
                            </template>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-white font-black text-xl leading-tight group-hover:text-rose-400 transition-colors truncate" x-text="item.title || item.name"></h3>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-white/30 text-[10px] font-black uppercase tracking-widest" x-text="(item.release_date || item.first_air_date) ? (item.release_date || item.first_air_date).substring(0, 4) : 'N/A'"></span>
                            <div class="flex items-center gap-1 text-rose-500">
                                <i class="bi bi-star-fill text-[10px]"></i>
                                <span class="text-[10px] font-black" x-text="item.vote_average?.toFixed(1) || '0.0'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- No Results / Help Messages -->
        <template x-if="results.length === 0 && query.length >= 3 && !loading && !error">
            <div class="text-center py-32 animate-in fade-in duration-700">
                <div class="w-24 h-24 bg-white/5 rounded-[2rem] flex items-center justify-center mx-auto mb-8 border border-white/5">
                    <i class="bi bi-search text-white/10 text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-white/30 tracking-tight">Keine Treffer</h3>
                <p class="text-white/10 text-sm mt-2 font-medium tracking-wide">Für \"<span class="text-white/40" x-text="query"></span>\" konnten wir leider nichts finden.</p>
            </div>
        </template>
        <template x-if="query.length < 3 && !loading">
            <div class="text-center py-32 opacity-20">
                <div class="w-24 h-24 bg-rose-500/10 rounded-[2rem] flex items-center justify-center mx-auto mb-8 animate-pulse">
                    <i class="bi bi-lightning-charge-fill text-rose-500 text-4xl"></i>
                </div>
                <p class="text-white font-black uppercase tracking-[0.3em] text-[10px]">Starte deine Suche oben</p>
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
             class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/90 backdrop-blur-md"
             x-cloak>
            <div class="glass w-full max-w-2xl rounded-[3rem] overflow-hidden shadow-3xl border border-white/10 max-h-[85vh] flex flex-col" @click.away="showSeasonModal = false">
                <div class="p-10 border-b border-white/10 flex justify-between items-center bg-white/[0.02]">
                    <div>
                        <h2 class="text-3xl font-black text-white tracking-tight" x-text="activeSeries?.name"></h2>
                        <p class="text-rose-500/60 text-[10px] uppercase font-black tracking-[0.3em] mt-2">Staffel-Archiv</p>
                    </div>
                    <button @click="showSeasonModal = false" class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-white/20 hover:text-white hover:bg-white/10 transition-all">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
                <div class="overflow-y-auto p-10 flex-1 custom-scrollbar">
                    <div class="flex items-center justify-between mb-8">
                        <span class="text-white/20 text-[10px] font-black uppercase tracking-widest" x-text="(activeSeries?.seasons?.length || 0) + ' Staffeln verfügbar'"></span>
                        <div class="flex gap-6">
                            <button @click="selectAll()" class="text-rose-400 hover:text-rose-300 text-[10px] font-black uppercase tracking-widest transition-colors">Alle wählen</button>
                            <button @click="selectedSeasons = []" class="text-white/20 hover:text-white text-[10px] font-black uppercase tracking-widest transition-colors">Abbrechen</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5">
                        <template x-for="season in activeSeries?.seasons?.filter(s => s.season_number > 0)" :key="season.id">
                            <label class="group flex items-center gap-6 p-5 rounded-[1.5rem] border border-white/5 bg-white/[0.02] hover:bg-white/[0.06] hover:border-rose-500/30 transition-all cursor-pointer">
                                <input type="checkbox" :value="season.season_number" x-model="selectedSeasons" class="w-6 h-6 rounded-lg bg-white/5 border-white/10 text-rose-600 focus:ring-rose-500/50">
                                <div class="w-14 h-20 bg-gray-800 rounded-xl overflow-hidden flex-shrink-0 border border-white/10 shadow-lg">
                                    <template x-if="season.poster_path">
                                        <img :src="'https://image.tmdb.org/t/p/w92' + season.poster_path" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!season.poster_path">
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="bi bi-image text-white/5"></i>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <div class="text-white font-black text-lg group-hover:text-rose-400 transition-colors" x-text="season.name"></div>
                                    <div class="text-white/30 text-[10px] font-black uppercase tracking-widest" x-text="season.episode_count + ' Episoden'"></div>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>
                <div class="p-10 bg-white/[0.03] border-t border-white/10 flex gap-6">
                    <button @click="showSeasonModal = false" class="flex-1 py-5 bg-white/5 text-white/30 font-black rounded-2xl hover:bg-white/10 hover:text-white transition-all uppercase tracking-widest text-[10px]">Abbrechen</button>
                    <button @click="startImport()" :disabled="selectedSeasons.length === 0" class="flex-[2] py-5 bg-rose-600 hover:bg-rose-500 disabled:opacity-30 disabled:cursor-not-allowed text-white font-black rounded-2xl transition-all shadow-2xl shadow-rose-600/20 uppercase tracking-widest text-[10px]">
                        <i class="bi bi-download mr-2"></i> <span x-text="'Jetzt ' + selectedSeasons.length + ' Staffel(n) archivieren'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mass Update Progress Modal -->
        <div x-show="showMassUpdateModal"
             class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-black/95 backdrop-blur-xl"
             x-cloak>
            <div class="glass w-full max-w-xl rounded-[3rem] overflow-hidden shadow-3xl border border-white/10 p-12 text-center animate-in zoom-in-95 duration-500">
                <div class="w-24 h-24 bg-rose-500/10 rounded-[2rem] flex items-center justify-center mx-auto mb-8 text-rose-500 text-4xl shadow-xl shadow-rose-500/10">
                    <i class="bi bi-arrow-repeat" :class="updating ? 'animate-spin' : ''"></i>
                </div>
                <h2 class="text-3xl font-black text-white mb-3 tracking-tight" x-text="updating ? (mode === 'matching' ? 'Auto-Matching...' : 'Mass-Update...') : 'Abgeschlossen!'"></h2>
                <p class="text-white/30 text-sm mb-10 font-medium" x-text="updating ? 'System verarbeitet Daten, bitte warten.' : 'Alle Operationen wurden erfolgreich beendet.'"></p>
                
                <div class="mb-5 flex justify-between items-end px-2">
                    <span class="text-[10px] font-black text-white/20 uppercase tracking-[0.2em]" x-text="updateCount + ' / ' + updateTotal"></span>
                    <span class="text-base font-black text-rose-500 tracking-widest" x-text="updateTotal > 0 ? Math.round((updateCount / updateTotal) * 100) : 0 + '%'"></span>
                </div>
                <div class="w-full h-5 bg-white/5 rounded-full overflow-hidden mb-12 border border-white/5 p-1 shadow-inner">
                    <div class="h-full bg-gradient-to-r from-rose-600 via-red-500 to-rose-400 rounded-full shadow-[0_0_15px_rgba(225,29,72,0.5)] transition-all duration-700" :style="'width: ' + (updateTotal > 0 ? (updateCount / updateTotal * 100) : 0) + '%'"></div>
                </div>

                <div class="space-y-6">
                     <div x-show="updating" x-cloak class="text-rose-400/60 text-[10px] font-black uppercase tracking-widest truncate" x-text="currentUpdateTitle"></div>
                     <button x-show="!updating" x-cloak @click="showMassUpdateModal = false; window.location.reload()" class="w-full py-5 bg-white text-black font-black rounded-2xl hover:bg-rose-600 hover:text-white transition-all shadow-xl uppercase tracking-widest text-[10px]">Dashboard aktualisieren</button>
                    <button x-show="updating" x-cloak @click="cancelUpdate()" class="text-white/10 hover:text-rose-500 text-[10px] font-black uppercase tracking-widest transition-colors">Vorgang abbrechen</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function tmdbImport() {
            return {
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
                            this.error = 'Keine Filme mit TMDb-ID zum Aktualisieren gefunden.';
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
                        setTimeout(() => this.processNextInQueue(), 600);
                    } catch (err) {
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
            };
        }

        if (window.Alpine) {
            Alpine.data('tmdbImport', tmdbImport);
        } else {
            document.addEventListener('alpine:init', () => {
                Alpine.data('tmdbImport', tmdbImport);
            });
        }
    </script>
    @endpush
</x-admin-layout>