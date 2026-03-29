<x-admin-layout>
    @section('header_title', 'Actor Bot')

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Bot Status Card -->
            <div class="glass p-6 rounded-2xl border border-white/5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold">Bot Status</h2>
                    @if($currentRun)
                        <span id="bot-status-badge" class="px-3 py-1 rounded-full bg-blue-500/20 text-blue-400 text-xs font-bold border border-blue-500/30 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                            Läuft im Hintergrund...
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-gray-500/20 text-gray-400 text-xs font-bold border border-gray-500/30">
                            Bereit
                        </span>
                    @endif
                </div>

                @if($currentRun)
                    <div class="space-y-4" id="bot-status-container" data-run-id="{{ $currentRun->id }}">
                        <p class="text-xs text-gray-400 mb-2">
                            <i class="bi bi-info-circle"></i> Der Bot wurde als Server-Prozess gestartet. Du kannst das Fenster bedenkenlos schließen!
                        </p>
                        <div class="flex justify-between text-sm text-gray-400">
                            <span>Fortschritt</span>
                            <span id="bot-progress-text">{{ $currentRun->processed_actors }} / {{ max(1, $currentRun->total_actors) }}</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2.5 dark:bg-gray-700">
                            @php
                                $percent = $currentRun->total_actors > 0 ? min(100, round(($currentRun->processed_actors / $currentRun->total_actors) * 100)) : 0;
                            @endphp
                            <div id="bot-progress-bar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                        </div>

                        <form action="{{ route('admin.bot.cancel') }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-rose-500/20 text-rose-400 border border-rose-500/30 hover:bg-rose-500 hover:text-white px-4 py-2 rounded-xl text-sm font-bold transition-all">
                                <i class="bi bi-x-circle text-lg"></i>
                                Prozess abbrechen
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-sm text-gray-400 mb-6">
                        Der echte System-Bot durchsucht im Hintergrund (Queue) die Datenbank nach Schauspielern mit fehlenden Informationen und aktualisiert diese automatisch über die TMDb API.
                    </p>
                    <form action="{{ route('admin.bot.start') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-4 py-3 rounded-xl font-bold transition-all hover:scale-[1.02] shadow-lg shadow-blue-500/20">
                            <i class="bi bi-robot text-xl"></i>
                            Bot als Daemon ausführen
                        </button>
                    </form>
                @endif
            </div>
            
            <!-- Quick Stats Card -->
            <div class="glass p-6 rounded-2xl border border-white/5 flex flex-col justify-center">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Statistik</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/5 p-4 rounded-xl border border-white/5">
                        <div class="text-sm text-gray-400">Schauspieler Gesamt</div>
                        <div class="text-2xl font-bold mt-1">{{ \App\Models\Actor::count() }}</div>
                    </div>
                    <div class="bg-white/5 p-4 rounded-xl border border-white/5">
                        <div class="text-sm text-gray-400">Ohne TMDb Profil</div>
                        <div class="text-2xl font-bold mt-1 text-yellow-500">{{ \App\Models\Actor::whereNull('tmdb_id')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History -->
        <div class="glass p-6 rounded-2xl border border-white/5">
            <h2 class="text-lg font-bold mb-4">Dashboard-Verlauf</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-300">
                    <thead class="text-xs uppercase bg-white/5 text-gray-400 border-b border-white/10">
                        <tr>
                            <th class="px-4 py-3 rounded-tl-xl">Lauf ID</th>
                            <th class="px-4 py-3">Datum</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Fortschritt</th>
                            <th class="px-4 py-3 text-right rounded-tr-xl">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRuns as $run)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="px-4 py-3 font-medium text-white">#{{ $run->id }}</td>
                                <td class="px-4 py-3">{{ $run->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    @if($run->status === 'completed')
                                        <span class="text-emerald-400"><i class="bi bi-check-circle mr-1"></i>Beendet</span>
                                    @elseif($run->status === 'failed')
                                        <span class="text-rose-400"><i class="bi bi-exclamation-circle mr-1"></i>Fehlerhaft</span>
                                    @elseif($run->status === 'aborted')
                                        <span class="text-rose-400"><i class="bi bi-x-circle mr-1"></i>Abgebrochen</span>
                                    @else
                                        <span class="text-blue-400"><i class="bi bi-arrow-repeat animate-spin mr-1 inline-block"></i>Aktiv (Hintergrund)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $run->processed_actors }} / {{ max(1, $run->total_actors) }} ({{ round(($run->processed_actors / max(1, $run->total_actors)) * 100) }}%)</td>
                                <td class="px-4 py-3 text-right">
                                    <button onclick="showLogs({{ $run->id }})" class="text-blue-400 hover:text-blue-300 transition-colors">
                                        <i class="bi bi-list-columns-reverse mr-1"></i> Logs
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                    Der Bot ist noch nie gelaufen.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Logs Modal -->
    <div id="logsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
        <div class="glass p-6 rounded-2xl border border-white/10 w-full max-w-4xl max-h-[80vh] flex flex-col transform scale-95 transition-transform duration-300">
            <div class="flex items-center justify-between mb-4 shrink-0">
                <h2 class="text-lg font-bold">Log-Protkoll für Lauf <span id="modalRunId"></span></h2>
                <button onclick="closeLogs()" class="text-gray-400 hover:text-white transition-colors">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar p-2 bg-black/40 rounded-xl border border-white/5">
                <table class="w-full text-left text-sm text-gray-300">
                    <thead>
                        <tr class="text-xs uppercase text-gray-500 border-b border-white/10">
                            <th class="pb-2 w-1/4">Actor</th>
                            <th class="pb-2 w-1/4">Bot Aktion</th>
                            <th class="pb-2">Nachricht</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody">
                        <!-- Filled via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            @if($currentRun)
                // Poll status every 3 seconds to see progress of the backend worker
                let statusInterval = setInterval(function() {
                    fetch('{{ route('admin.bot.status') }}')
                        .then(response => response.json())
                        .then(data => {
                            if (data.running) {
                                let percent = data.total > 0 ? (data.processed / data.total) * 100 : 0;
                                document.getElementById('bot-progress-text').innerText = data.processed + ' / ' + data.total;
                                document.getElementById('bot-progress-bar').style.width = Math.min(percent, 100) + '%';
                            } else {
                                clearInterval(statusInterval);
                                location.reload();
                            }
                        })
                        .catch(() => {
                            // ignore silent polling errors
                        });
                }, 3000);
            @endif

            const modal = document.getElementById('logsModal');
            const modalContent = modal.querySelector('.glass');

            function showLogs(runId) {
                document.getElementById('modalRunId').innerText = '#' + runId;
                const tbody = document.getElementById('logsTableBody');
                tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4"><i class="bi bi-arrow-repeat animate-spin text-xl text-blue-400 inline-block"></i> Lade Logs aus der Datenbank...</td></tr>';
                
                modal.classList.remove('hidden');
                void modal.offsetWidth;
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');

                fetch(`/admin/bot/${runId}/logs`)
                    .then(r => r.json())
                    .then(data => {
                        tbody.innerHTML = '';
                        if (data.logs.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="3" class="py-4 text-center text-gray-500">Der Bot hat für diesen Lauf keine Logs hinterlassen.</td></tr>';
                            return;
                        }
                        
                        data.logs.forEach(log => {
                            let statusColor = 'text-gray-400';
                            let statusIcon = 'bi-dash-circle';
                            
                            if (log.status === 'success') {
                                statusColor = 'text-emerald-400';
                                statusIcon = 'bi-check-circle';
                            } else if (log.status === 'error') {
                                statusColor = 'text-rose-400';
                                statusIcon = 'bi-exclamation-circle';
                            }

                            let actorName = log.actor ? log.actor.first_name + ' ' + (log.actor.last_name || '') : 'Gelöscht (ID: ' + log.actor_id + ')';
                            
                            tbody.innerHTML += `
                                <tr class="border-b border-white/5 last:border-0 hover:bg-white/5">
                                    <td class="py-2 pr-4 font-medium">${actorName}</td>
                                    <td class="py-2 pr-4 ${statusColor}"><i class="bi ${statusIcon} mr-1"></i> ${log.status}</td>
                                    <td class="py-2 text-gray-400 text-xs">${log.message}</td>
                                </tr>
                            `;
                        });
                    });
            }

            function closeLogs() {
                modal.classList.add('opacity-0');
                modalContent.classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        </script>
    @endpush
</x-admin-layout>
