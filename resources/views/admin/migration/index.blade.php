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

                <form action="{{ route('admin.migration.run') }}" method="POST" id="migrationForm">
                    @csrf
                    <div class="flex flex-col gap-6">
                        <div class="flex items-center gap-4 group cursor-pointer">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="fresh" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                            <span class="text-sm font-medium text-gray-300">Alle Tabellen vor der Migration leeren (Fresh Install)</span>
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
