<x-admin-layout>
    <x-slot name="header_title">Datenmigration v1.5 → v2.0</x-slot>

    <div class="space-y-8">
        <!-- Status Card -->
        <div class="glass-strong rounded-3xl p-8 border border-white/5 relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-xl font-bold text-white mb-2">Datenquelle: DVD Profiler v1.5 (MySQL)</h3>
                <p class="text-gray-400 text-sm mb-6">Prüfung der Verbindung zur alten Datenbank (v1.5).</p>

                @if($connectionStatus)
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-bold">
                        <i class="bi bi-check-circle-fill"></i>
                        Verbindung erfolgreich
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-bold">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Keine Verbindung
                    </div>
                    @if($error)
                        <div class="mt-4 p-4 bg-black/40 rounded-xl border border-white/5 font-mono text-xs text-red-300">
                            {{ $error }}
                        </div>
                    @endif
                    <p class="mt-4 text-sm text-gray-500 italic">
                        Hinweis: Bitte prüfe die <code class="bg-white/5 px-1 rounded text-blue-400">DB_V1_*</code> Einstellungen in deiner <code class="bg-white/5 px-1 rounded text-blue-400">.env</code> Datei.
                    </p>
                @endif
            </div>
            <div class="absolute -right-10 -bottom-10 opacity-5">
                <i class="bi bi-database-fill text-9xl"></i>
            </div>
        </div>

        @if($connectionStatus)
        <!-- Migration Tool Card -->
        <div class="glass-strong rounded-3xl p-8 border border-white/5 relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-xl font-bold text-white mb-2">Ziel: MovieShelf v2.0 (SQLite)</h3>
                <p class="text-gray-400 text-sm mb-8">Dieser Vorgang kopiert alle Daten von der alten v1.5 Datenbank in das neue v2.0 System.</p>

                <form action="{{ route('admin.migration.run') }}" method="POST" id="migrationForm" x-data="{ 
                    modules: ['users', 'actors', 'movies', 'watched', 'ratings', 'wishlist', 'seasons', 'episodes', 'settings', 'counter', 'logs'],
                    movieFields: ['year', 'genre', 'rating', 'runtime', 'rating_age', 'overview', 'director', 'trailer_url', 'view_count', 'created_at', 'cover_id', 'backdrop_id', 'collection_type', 'boxset_parent', 'is_deleted', 'tmdb_id', 'tmdb_json'],
                    selectedModules: ['users', 'actors', 'movies', 'watched', 'ratings', 'wishlist', 'seasons', 'episodes', 'settings', 'counter', 'logs'],
                    selectedFields: ['year', 'genre', 'rating', 'runtime', 'rating_age', 'overview', 'director', 'trailer_url', 'view_count', 'created_at', 'cover_id', 'backdrop_id', 'collection_type', 'boxset_parent', 'is_deleted', 'tmdb_id', 'tmdb_json'],
                    selectAll() { 
                        this.selectedModules = [...this.modules];
                        this.selectedFields = [...this.movieFields];
                    }
                }">
                    @csrf
                    <div class="flex flex-col gap-8">
                        <div class="flex items-center gap-4 group cursor-pointer">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="fresh" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                            <span class="text-sm font-medium text-gray-300">Alle Tabellen vor der Migration leeren (Fresh Install)</span>
                        </div>

                        <!-- Modules Section -->
                        <div class="space-y-4 pt-4 border-t border-white/5">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black text-white/40 uppercase tracking-widest">1. Datenkategorien</h4>
                                <button type="button" @click="selectedModules = [...modules]" class="text-[10px] font-black text-blue-400 uppercase tracking-widest hover:text-blue-300 transition-colors">Alle wählen</button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                <template x-for="mod in modules">
                                    <label class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/5 hover:border-white/10 cursor-pointer transition-all" :class="selectedModules.includes(mod) ? 'bg-blue-500/10 border-blue-500/30' : ''">
                                        <input type="checkbox" name="modules[]" :value="mod" x-model="selectedModules" class="w-4 h-4 rounded bg-white/5 border-white/10 text-blue-600 focus:ring-blue-500/50">
                                        <span class="text-[11px] text-gray-300 font-bold uppercase tracking-wider" x-text="{
                                            'users': 'Benutzer',
                                            'actors': 'Schauspieler',
                                            'movies': 'Filme',
                                            'watched': 'Gesehen-Status',
                                            'ratings': 'Bewertungen',
                                            'wishlist': 'Wunschliste',
                                            'seasons': 'Staffeln',
                                            'episodes': 'Episoden',
                                            'settings': 'Einstellungen',
                                            'counter': 'Besucher',
                                            'logs': 'Protokolle'
                                        }[mod]"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Movie Fields Section -->
                        <div class="space-y-4 pt-4 border-t border-white/5" x-show="selectedModules.includes('movies')">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black text-white/40 uppercase tracking-widest">2. Film-Details (Attribute)</h4>
                                <div class="flex gap-4">
                                    <button type="button" @click="selectedFields = []" class="text-[10px] font-black text-rose-400 uppercase tracking-widest hover:text-rose-300 transition-colors">Nichts wählen</button>
                                    <button type="button" @click="selectedFields = [...movieFields]" class="text-[10px] font-black text-blue-400 uppercase tracking-widest hover:text-blue-300 transition-colors">Alle wählen</button>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                <template x-for="field in movieFields">
                                    <label class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/5 hover:border-white/10 cursor-pointer transition-all" :class="selectedFields.includes(field) ? 'bg-indigo-500/10 border-indigo-500/30' : ''">
                                        <input type="checkbox" name="movie_fields[]" :value="field" x-model="selectedFields" class="w-4 h-4 rounded bg-white/5 border-white/10 text-indigo-600 focus:ring-indigo-500/50">
                                        <span class="text-[11px] text-gray-300 font-medium" x-text="{
                                            'year': 'Produktionsjahr',
                                            'genre': 'Genre',
                                            'rating': 'Bewertung',
                                            'runtime': 'Laufzeit',
                                            'rating_age': 'FSK',
                                            'overview': 'Handlung',
                                            'director': 'Regie',
                                            'trailer_url': 'Trailer URL',
                                            'view_count': 'Aufrufe',
                                            'created_at': 'Hinzugefügt am',
                                            'cover_id': 'Cover ID',
                                            'backdrop_id': 'Backdrop ID',
                                            'collection_type': 'Medientyp',
                                            'boxset_parent': 'Boxset-Verkn.',
                                            'is_deleted': 'Geloescht-Flag',
                                            'tmdb_id': 'TMDB ID',
                                            'tmdb_json': 'TMDB JSON-Daten (Cache)'
                                        }[field]"></span>
                                    </label>
                                </template>
                            </div>
                            <p class="text-[10px] text-white/20 italic mt-2">Hinweis: Titel und Verknüpfungen werden für die Konsistenz immer übernommen.</p>
                        </div>

                        <div class="bg-blue-600/10 border border-blue-500/20 rounded-2xl p-4 text-blue-300 text-sm">
                            <i class="bi bi-info-circle-fill mr-2"></i>
                            Die Migration kann je nach Datenmenge einige Minuten in Anspruch nehmen. Bitte schließe das Fenster nicht.
                        </div>

                        <div>
                            <button type="submit" 
                                    id="submitBtn"
                                    class="inline-flex items-center gap-2 px-8 py-4 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-2xl transition-all hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-blue-600/20">
                                <i class="bi bi-play-fill text-xl" id="btnIcon"></i>
                                <span id="btnText">Migration jetzt starten</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="absolute -right-10 -bottom-10 opacity-5">
                <i class="bi bi-rocket-takeoff-fill text-9xl"></i>
            </div>
        </div>
        @endif

        @if(session('migration_logs'))
        <!-- Logs Card -->
        <div class="glass-strong rounded-3xl p-8 border border-white/5">
            <h3 class="text-xl font-bold text-white mb-6">Migration Protokoll</h3>
            <div class="bg-black/60 rounded-2xl border border-white/5 p-6 font-mono text-sm max-h-[400px] overflow-y-auto custom-scrollbar">
                <div class="space-y-1">
                    @foreach(session('migration_logs') as $log)
                        <div class="flex gap-4">
                            <span class="text-gray-600">[{{ date('H:i:s') }}]</span>
                            <span class="{{ str_contains($log, 'migrated') ? 'text-emerald-400' : (str_contains($log, 'Truncating') ? 'text-yellow-400' : 'text-blue-400') }}">
                                {{ $log }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.getElementById('migrationForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const icon = document.getElementById('btnIcon');
            const text = document.getElementById('btnText');

            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            icon.classList.value = 'bi bi-arrow-repeat animate-spin text-xl';
            text.innerText = 'Migration läuft...';
        });
    </script>
    @endpush
</x-admin-layout>
