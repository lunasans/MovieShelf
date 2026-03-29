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
                                        <div>
                                            <span class="text-rose-400"><i class="bi bi-exclamation-circle mr-1"></i>Fehlerhaft</span>
                                            <div class="text-[10px] text-rose-500/80 mt-1 max-w-[150px] truncate" title="{{ $run->error_message }}">
                                                {{ $run->error_message }}
                                            </div>
                                        </div>
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

    <!-- Terminal Modal -->
    <div id="logsModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md hidden opacity-0 transition-opacity duration-300">
        <div class="p-1 rounded-xl border border-white/20 w-full max-w-5xl h-[85vh] max-h-[85vh] flex flex-col overflow-hidden transform scale-95 transition-transform duration-300 shadow-2xl bg-[#1e1e1e]">
            <!-- Terminal Header -->
            <div class="h-12 flex items-center justify-between px-4 shrink-0 bg-[#2d2d2d] rounded-t-lg border-b border-black">
                <div class="flex gap-2">
                    <button onclick="closeLogs()" class="w-3 h-3 rounded-full bg-rose-500 hover:bg-rose-400 transition-colors shadow-inner cursor-pointer" title="Schließen"></button>
                    <div class="w-3 h-3 rounded-full bg-amber-500 shadow-inner"></div>
                    <div class="w-3 h-3 rounded-full bg-emerald-500 shadow-inner"></div>
                </div>
                <div class="text-xs font-mono text-gray-400 font-bold tracking-widest uppercase truncate ml-2">Actor-Bot <span class="hidden md:inline">Terminal</span> ~ Lauf <span id="modalRunId"></span></div>
                <div class="w-5"></div><!-- Spacer for centering -->
            </div>
            
            <!-- Terminal Body -->
            <div id="terminalBody" class="h-[calc(100%-3rem)] w-full overflow-y-auto custom-scrollbar bg-[#0d1117] p-4 font-mono text-xs md:text-sm text-gray-300 leading-relaxed" style="overscroll-behavior: contain;">
                <div id="logsTableBody" class="space-y-0.5">
                    <!-- Filled via JS -->
                </div>
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
            const modalContent = modal.querySelector('div[class*="transform"]');
            
            let terminalPoller = null;
            let currentRenderedCount = 0;

            function showLogs(runId) {
                document.getElementById('modalRunId').innerText = '#' + runId;
                const tbody = document.getElementById('logsTableBody');
                tbody.innerHTML = '<div class="text-blue-400 animate-pulse">> Initialisiere Verbindung zur Datenbank...</div>';
                currentRenderedCount = 0;
                
                modal.classList.remove('hidden');
                void modal.offsetWidth;
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');

                fetchLogs(runId);
                // Live Polling every 2 secs
                terminalPoller = setInterval(() => fetchLogs(runId), 2000);
            }

            function fetchLogs(runId) {
                fetch(`/admin/bot/${runId}/logs`)
                    .then(r => r.json())
                    .then(data => {
                        const tbody = document.getElementById('logsTableBody');
                        const terminalBody = document.getElementById('terminalBody');
                        
                        if (currentRenderedCount === 0) {
                            tbody.innerHTML = '';
                            if (data.logs.length === 0) {
                                tbody.innerHTML = '<div class="text-gray-500">> Warte auf erste Log-Einträge...</div>';
                                return;
                            }
                        }

                        if (data.logs.length > currentRenderedCount) {
                            // Check if user is scrolled to bottom (allow 50px tolerance)
                            let isAtBottom = Math.abs((terminalBody.scrollHeight - terminalBody.clientHeight) - terminalBody.scrollTop) < 50;
                            if (currentRenderedCount === 0) {
                                isAtBottom = true;
                                if (tbody.innerHTML.includes('> Warte auf')) tbody.innerHTML = '';
                            }

                            for (let i = currentRenderedCount; i < data.logs.length; i++) {
                                let log = data.logs[i];
                                let statusColor = 'text-gray-500';
                                
                                if (log.status === 'success') {
                                    statusColor = 'text-emerald-400 font-bold';
                                } else if (log.status === 'error') {
                                    statusColor = 'text-rose-500 font-bold';
                                } else if (log.status === 'skipped') {
                                    statusColor = 'text-blue-400/80';
                                }

                                let actorName = log.actor ? log.actor.first_name + ' ' + (log.actor.last_name || '') : 'Gelöscht (ID: ' + log.actor_id + ')';
                                
                                // Parse time specifically to HH:mm:ss if it's an ISO timestamp
                                let rawDate = log.created_at;
                                if(rawDate && rawDate.length > 18) {
                                    rawDate = rawDate.substring(11, 19);
                                }

                                let div = document.createElement('div');
                                div.className = 'hover:bg-white/5 px-2 py-0.5 rounded transition-colors break-words flex flex-col md:flex-row md:gap-4 md:items-start group';
                                div.innerHTML = `
                                    <div class="flex gap-3 shrink-0 opacity-70 group-hover:opacity-100 transition-opacity">
                                        <span class="text-gray-600 font-semibold">[${rawDate}]</span>
                                        <span class="${statusColor} w-24 whitespace-nowrap">[${log.status.toUpperCase()}]</span>
                                    </div>
                                    <div class="flex-1 flex flex-col md:flex-row gap-2 md:gap-4">
                                        <span class="text-white font-semibold md:w-48 truncate flex-shrink-0 mr-2">${actorName}</span>
                                        <span class="text-gray-400 font-extralight">> ${log.message}</span>
                                    </div>
                                `;
                                tbody.appendChild(div);
                            }
                            
                            currentRenderedCount = data.logs.length;
                            
                            // Only auto-scroll if user was already at the bottom
                            if (isAtBottom) {
                                terminalBody.scrollTop = terminalBody.scrollHeight;
                            }
                        }
                    });
            }

            function closeLogs() {
                if (terminalPoller) {
                    clearInterval(terminalPoller);
                    terminalPoller = null;
                }
                modal.classList.add('opacity-0');
                modalContent.classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        </script>
    @endpush
</x-admin-layout>
