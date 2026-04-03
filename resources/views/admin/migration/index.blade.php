<x-admin-layout>
    <x-slot name="header_title">Datenmigration v1.5 → v2.0</x-slot>

    <div class="max-w-5xl mx-auto space-y-10">
        <!-- Status Card -->
        <div class="glass p-10 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <h3 class="text-2xl font-black text-white tracking-tight uppercase mb-2">Datenquelle: DVD Profiler Legacy</h3>
                <p class="text-sm text-white/40 font-medium mb-8">Prüfung der Verbindung zur MySQL-Datenbank (v1.5).</p>
                
                @if($connectionStatus)
                    <div class="inline-flex items-center gap-3 px-6 py-3 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-black uppercase tracking-widest">
                        <i class="bi bi-check-circle-fill text-base"></i>
                        Verbindung hergestellt
                    </div>
                @else
                    <div class="inline-flex items-center gap-3 px-6 py-3 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-black uppercase tracking-widest">
                        <i class="bi bi-exclamation-triangle-fill text-base"></i>
                        Keine Verbindung
                    </div>
                    @if($error)
                        <div class="mt-6 p-6 bg-black/40 rounded-2xl border border-white/5 font-mono text-[11px] text-rose-300 leading-relaxed overflow-x-auto">
                            {{ $error }}
                        </div>
                    @endif
                    <p class="mt-6 text-[10px] text-white/20 italic uppercase tracking-widest font-black leading-relaxed">
                        Hinweis: Prüfe <code class="bg-white/5 px-2 py-0.5 rounded text-rose-400 font-mono">DB_V1_*</code> in deiner <code class="bg-white/5 px-2 py-0.5 rounded text-rose-400 font-mono">.env</code> Datei.
                    </p>
                @endif
            </div>
            <div class="absolute -right-10 -bottom-10 opacity-5">
                <i class="bi bi-database-fill text-[12rem]"></i>
            </div>
        </div>

        @if($connectionStatus)
        <!-- Migration Tool Card -->
        <div class="glass p-10 rounded-[3.5rem] border-white/5 shadow-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <h3 class="text-2xl font-black text-white tracking-tight uppercase mb-2">Ziel: MovieShelf Modern</h3>
                <p class="text-sm text-white/40 font-medium mb-12 italic">Dieser Prozess transferiert alle Bestandsdaten in die neue High-Performance Architektur.</p>
                
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
                    <div class="flex flex-col gap-10">
                        <div class="flex flex-col gap-3">
                            <label for="v1_path" class="text-[10px] font-black text-white/30 uppercase tracking-[0.2em] px-1">v1.5 Projekt-Pfad (Für Medien-Sync)</label>
                            <input type="text" name="v1_path" id="v1_path"
                                   value="{{ old('v1_path', base_path('../dvdprofiler.liste')) }}"
                                   placeholder="/var/www/legacy-system"
                                   class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white font-bold focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all">
                        </div>

                        <div class="flex items-center gap-5 p-6 bg-white/5 rounded-2xl border border-white/5 group hover:border-rose-500/30 transition-all cursor-pointer">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="fresh" value="1" class="sr-only peer">
                                <div class="w-12 h-6 bg-white/10 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                            </label>
                            <span class="text-xs font-black text-white/40 uppercase tracking-widest">Alle Tabellen vorab leeren (Fresh Install)</span>
                        </div>

                        <!-- Modules -->
                        <div class="space-y-6 pt-10 border-t border-white/5">
                            <div class="flex items-center justify-between px-1">
                                <h4 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em]">1. Datenkategorien</h4>
                                <button type="button" @click="selectedModules = [...modules]" class="text-[9px] font-black text-rose-500 uppercase tracking-widest hover:text-rose-400 transition-colors">Alle wählen</button>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <template x-for="mod in modules">
                                    <label class="flex items-center gap-3 p-4 rounded-xl bg-white/[0.02] border border-white/5 hover:border-rose-500/30 cursor-pointer transition-all" 
                                           :class="selectedModules.includes(mod) ? 'bg-rose-500/10 border-rose-500/30' : ''">
                                        <input type="checkbox" name="modules[]" :value="mod" x-model="selectedModules" class="w-5 h-5 rounded-lg bg-white/5 border-white/10 text-rose-600 focus:ring-rose-500/50 cursor-pointer">
                                        <span class="text-[10px] text-white/60 font-black uppercase tracking-widest truncate" x-text="{
                                            'users': 'Benutzer', 'actors': 'Stars', 'movies': 'Filme', 'watched': 'Gesehen',
                                            'ratings': 'Votings', 'wishlist': 'Wunschliste', 'seasons': 'Staffeln',
                                            'episodes': 'Episoden', 'settings': 'Settings', 'counter': 'Counter', 'logs': 'Logs'
                                        }[mod]"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Movie Fields Section -->
                        <div class="space-y-6 pt-10 border-t border-white/5" x-show="selectedModules.includes('movies')">
                            <div class="flex items-center justify-between px-1">
                                <h4 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em]">2. Film-Attribute</h4>
                                <div class="flex gap-6">
                                    <button type="button" @click="selectedFields = []" class="text-[9px] font-black text-white/30 uppercase tracking-widest hover:text-white transition-colors">Nichts</button>
                                    <button type="button" @click="selectedFields = [...movieFields]" class="text-[9px] font-black text-rose-500 uppercase tracking-widest hover:text-rose-400 transition-colors">Alle</button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <template x-for="field in movieFields">
                                    <label class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.01] border border-white/5 hover:border-rose-500/20 cursor-pointer transition-all" 
                                           :class="selectedFields.includes(field) ? 'bg-rose-500/5 border-rose-500/20 text-rose-400' : 'text-white/40'">
                                        <input type="checkbox" name="movie_fields[]" :value="field" x-model="selectedFields" class="w-4 h-4 rounded-md bg-white/5 border-white/10 text-rose-600 focus:ring-rose-500/50 cursor-pointer">
                                        <span class="text-[9px] font-bold uppercase tracking-wider truncate" x-text="{
                                            'year': 'Jahr', 'genre': 'Genre', 'rating': 'Bewertung', 'runtime': 'Laufzeit',
                                            'rating_age': 'FSK', 'overview': 'Plot', 'director': 'Regie', 'trailer_url': 'Trailer',
                                            'view_count': 'Hits', 'created_at': 'Datum', 'cover_id': 'Cover', 'backdrop_id': 'BG',
                                            'collection_type': 'Typ', 'boxset_parent': 'Parent', 'is_deleted': 'Deleted',
                                            'tmdb_id': 'TMDB ID', 'tmdb_json': 'TMDB Json'
                                        }[field]"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div class="bg-rose-600/10 border border-rose-500/20 rounded-3xl p-6 flex items-center gap-4">
                            <i class="bi bi-info-circle-fill text-rose-500 text-xl"></i>
                            <p class="text-xs text-rose-300/80 font-medium leading-relaxed italic">
                                Die Migration verarbeitet große Datenmengen. Bitte lass dieses Tab während des Vorgangs geöffnet.
                            </p>
                        </div>

                        <div class="pt-6">
                            <button type="submit" id="submitBtn"
                                    class="inline-flex items-center gap-4 px-12 py-5 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white rounded-[2rem] font-black text-xs uppercase tracking-[0.3em] transition-all shadow-2xl shadow-rose-600/30 transform hover:scale-[1.03] active:scale-[0.98]">
                                <i class="bi bi-rocket-takeoff text-xl" id="btnIcon"></i>
                                <span id="btnText">Migration starten</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="absolute -right-10 -bottom-10 opacity-5">
                <i class="bi bi-rocket-takeoff-fill text-[12rem]"></i>
            </div>
        </div>
        @endif

        @if(session('migration_logs'))
        <!-- Logs Card -->
        <div class="glass p-10 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
            <h3 class="text-xl font-black text-white tracking-tight uppercase mb-8">Echtzeit-Protokoll</h3>
            <div class="bg-black/60 rounded-3xl border border-white/5 p-8 font-mono text-[11px] max-h-[500px] overflow-y-auto custom-scrollbar">
                <div class="space-y-2">
                    @foreach(session('migration_logs') as $log)
                        <div class="flex gap-6 py-1 border-b border-white/[0.02]">
                            <span class="text-white/10 font-black">[{{ date('H:i:s') }}]</span>
                            <span class="{{ str_contains($log, 'migrated') ? 'text-emerald-400 font-bold' : (str_contains($log, 'Truncating') ? 'text-amber-400 font-bold' : 'text-rose-400/60') }}">
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
            text.innerText = 'Verarbeitung...';
        });
    </script>
    @endpush
</x-admin-layout>