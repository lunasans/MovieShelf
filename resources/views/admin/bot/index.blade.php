<x-admin-layout>
    @section('header_title', 'Actor Database Bot')

    <div class="max-w-6xl mx-auto space-y-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Bot Status Card -->
            <div class="lg:col-span-2 glass p-10 rounded-[3rem] border-white/5 shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                
                <div class="flex items-center justify-between mb-10">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-600 to-red-800 flex items-center justify-center text-white text-2xl shadow-xl shadow-rose-600/20 ring-2 ring-white/10">
                            <i class="bi bi-robot"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-white tracking-tight uppercase">Bot-Status</h2>
                            <p class="text-[10px] text-white/30 font-black uppercase tracking-widest mt-1">Hintergrundprozess-Steuerung</p>
                        </div>
                    </div>
                    
                    @if($currentRun)
                        <span id="bot-status-badge" class="px-5 py-2 rounded-full bg-rose-500/10 text-rose-400 text-[10px] font-black uppercase tracking-widest border border-rose-500/20 flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                            Aktiv
                        </span>
                    @else
                        <span class="px-5 py-2 rounded-full bg-emerald-500/10 text-emerald-400 text-[10px] font-black uppercase tracking-widest border border-emerald-500/20">
                            Bereit
                        </span>
                    @endif
                </div>

                @if($currentRun)
                    <div class="space-y-8" id="bot-status-container" data-run-id="{{ $currentRun->id }}">
                        <div class="p-5 bg-black/20 rounded-2xl border border-white/5 relative overflow-hidden">
                            <div class="absolute inset-0 bg-rose-500/5 animate-pulse pointer-events-none"></div>
                            <p class="text-xs text-white/40 font-medium leading-relaxed italic relative z-10">
                                <i class="bi bi-info-circle-fill text-rose-500 mr-2"></i> 
                                <span class="text-white/60 font-bold">Browser-Modus Aktiv:</span> Der Bot wird direkt durch dieses Fenster gesteuert. 
                                <span class="block mt-1 text-[10px] opacity-70">Die Seite muss für den Fortschritt ohne externen Worker (Terminal) geöffnet bleiben.</span>
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between text-[10px] font-black text-white/30 uppercase tracking-[0.2em] px-1">
                                <span>Verarbeitungsfortschritt</span>
                                <span id="bot-progress-text" class="text-rose-400">{{ $currentRun->processed_actors }} / {{ max(1, $currentRun->total_actors) }} Stars</span>
                            </div>
                            <div class="w-full bg-white/5 rounded-full h-3 p-0.5 border border-white/10">
                                @php
                                    $percent = $currentRun->total_actors > 0 ? min(100, round(($currentRun->processed_actors / $currentRun->total_actors) * 100)) : 0;
                                @endphp
                                <div id="bot-progress-bar" class="bg-gradient-to-r from-rose-600 to-red-500 h-full rounded-full transition-all duration-700 shadow-lg shadow-rose-600/20" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>

                        <form action="{{ route('admin.bot.cancel') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-3 bg-white/5 hover:bg-rose-500/10 text-white/40 hover:text-rose-400 border border-white/10 hover:border-rose-500/30 px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">
                                <i class="bi bi-stop-circle text-lg"></i>
                                Prozess gewaltsam stoppen
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-sm text-white/40 leading-relaxed mb-10 font-medium italic">
                        Der Actor-Bot synchronisiert fehlende Metadaten (Geburtsdaten, Biografien, Profile) automatisch im Hintergrund über die TMDb API, um deine Datenbank aktuell zu halten.
                    </p>
                    <form action="{{ route('admin.bot.start') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-4 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white px-8 py-5 rounded-[2rem] font-black text-xs uppercase tracking-[0.3em] transition-all shadow-2xl shadow-rose-600/30 transform hover:scale-[1.02] active:scale-[0.98]">
                            <i class="bi bi-robot text-xl"></i>
                            Bot-Daemon starten
                        </button>
                    </form>
                @endif
            </div>
            
            <!-- Quick Stats Card -->
            <div class="lg:col-span-1 space-y-6">
                <div class="glass p-10 rounded-[3rem] border-white/5 shadow-2xl text-center h-full flex flex-col justify-center">
                    <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] mb-8">Datenabgleich</h3>
                    <div class="space-y-10">
                        <div>
                            <div class="text-[10px] font-black text-white/30 uppercase tracking-widest mb-2">Stars Gesamt</div>
                            <div class="text-5xl font-black text-white tracking-tighter">{{ \App\Models\Actor::count() }}</div>
                        </div>
                        <div class="pt-10 border-t border-white/5">
                            <div class="text-[10px] font-black text-white/30 uppercase tracking-widest mb-2">Unverknüpft</div>
                            <div class="text-5xl font-black text-rose-500 tracking-tighter">{{ \App\Models\Actor::whereNull('tmdb_id')->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History -->
        <div class="glass p-10 rounded-[3.5rem] border-white/5 shadow-2xl relative overflow-hidden">
            <h2 class="text-xl font-black text-white tracking-tight uppercase mb-10 flex items-center gap-4">
                <i class="bi bi-clock-history text-rose-500/40"></i>
                Verlauf & Logs
            </h2>
            
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm text-white/60">
                    <thead>
                        <tr class="text-[10px] uppercase font-black text-white/30 tracking-[0.2em] border-b border-white/5">
                            <th class="px-6 py-5">Lauf</th>
                            <th class="px-6 py-5">Datum / Uhrzeit</th>
                            <th class="px-6 py-5">Status</th>
                            <th class="px-6 py-5">Ergebnis</th>
                            <th class="px-6 py-5 text-right">Protokoll</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.03]">
                        @forelse($recentRuns as $run)
                            <tr class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-6 font-black text-white/40">#{{ $run->id }}</td>
                                <td class="px-6 py-6 font-bold">{{ $run->created_at->format('d. M Y') }} <span class="text-white/20 ml-2">{{ $run->created_at->format('H:i') }}</span></td>
                                <td class="px-6 py-6">
                                    @if($run->status === 'completed')
                                        <span class="text-emerald-400 font-black text-[10px] uppercase tracking-widest bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-500/20">Erfolgreich</span>
                                    @elseif($run->status === 'failed' || $run->status === 'aborted')
                                        <span class="text-rose-400 font-black text-[10px] uppercase tracking-widest bg-rose-500/10 px-3 py-1 rounded-full border border-rose-500/20">Fehlgeschlagen</span>
                                    @else
                                        <span class="text-rose-400 font-black text-[10px] uppercase tracking-widest flex items-center gap-2">
                                            <i class="bi bi-arrow-repeat animate-spin"></i> Aktiv
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-6 font-mono text-xs">{{ $run->processed_actors }} / {{ max(1, $run->total_actors) }}</td>
                                <td class="px-6 py-6 text-right">
                                    <button onclick="showLogs({{ $run->id }})" class="px-5 py-2 bg-rose-600/10 hover:bg-rose-600 text-rose-500 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border border-rose-500/20">
                                        <i class="bi bi-list-columns-reverse mr-2"></i> Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-white/20 italic font-medium uppercase tracking-widest text-[10px]">
                                    Keine bisherigen Aufzeichnungen gefunden
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Terminal Modal -->
    <div id="logsModal" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-black/90 backdrop-blur-2xl hidden opacity-0 transition-all duration-500">
        <div class="glass border-white/20 w-full max-w-6xl h-[85vh] rounded-[2.5rem] flex flex-col overflow-hidden transform scale-95 transition-all duration-500 shadow-3xl bg-[#0a0a0c]">
            <!-- Terminal Header -->
            <div class="h-16 flex items-center justify-between px-8 shrink-0 bg-white/[0.02] border-b border-white/10">
                <div class="flex gap-2">
                    <button @click="closeLogs()" class="w-3.5 h-3.5 rounded-full bg-rose-500/80 hover:bg-rose-500 transition-all shadow-lg" onclick="closeLogs()"></button>
                    <div class="w-3.5 h-3.5 rounded-full bg-zinc-700"></div>
                    <div class="w-3.5 h-3.5 rounded-full bg-zinc-700"></div>
                </div>
                <div class="text-[10px] font-black text-rose-500 uppercase tracking-[0.4em] ml-4">Actor Bot Runtime <span class="text-white/20 font-mono">:: RUN <span id="modalRunId"></span></span></div>
                <div class="w-6"></div>
            </div>
            
            <!-- Terminal Body -->
            <div id="terminalBody" class="h-full w-full overflow-y-auto custom-scrollbar p-8 font-mono text-[11px] md:text-xs text-white/60 leading-relaxed" style="overscroll-behavior: contain;">
                <div id="logsTableBody" class="space-y-1">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            @if($currentRun)
                let statusInterval = setInterval(function() {
                    // We call 'process' instead of 'status' to keep the bot moving in the browser
                    fetch('{{ route('admin.bot.process') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.running) {
                                let percent = data.total > 0 ? (data.processed / data.total) * 100 : 0;
                                document.getElementById('bot-progress-text').innerText = data.processed + ' / ' + data.total + ' Stars';
                                document.getElementById('bot-progress-bar').style.width = Math.min(percent, 100) + '%';
                            } else {
                                clearInterval(statusInterval);
                                location.reload();
                            }
                        })
                        .catch(() => {});
                }, 4000); // Poll/Process every 4 seconds
            @endif

            const modal = document.getElementById('logsModal');
            const modalContent = modal.querySelector('div[class*="transform"]');
            
            let terminalPoller = null;
            let currentRenderedCount = 0;

            function showLogs(runId) {
                document.getElementById('modalRunId').innerText = '#' + runId;
                const tbody = document.getElementById('logsTableBody');
                tbody.innerHTML = '<div class="text-rose-500/40 font-black animate-pulse uppercase tracking-widest text-[9px] mb-4">> [SYSTEM] Initialisiere Terminal-Verbindung...</div>';
                currentRenderedCount = 0;
                
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    modalContent.classList.remove('scale-95');
                }, 50);

                fetchLogs(runId);
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
                                tbody.innerHTML = '<div class="text-white/20 italic tracking-widest">> Warten auf Datenpakete...</div>';
                                return;
                            }
                        }

                        if (data.logs.length > currentRenderedCount) {
                            let isAtBottom = Math.abs((terminalBody.scrollHeight - terminalBody.clientHeight) - terminalBody.scrollTop) < 100;
                            if (currentRenderedCount === 0) isAtBottom = true;

                            for (let i = currentRenderedCount; i < data.logs.length; i++) {
                                let log = data.logs[i];
                                let statusColor = 'text-white/20';
                                
                                if (log.status === 'success') {
                                    statusColor = 'text-emerald-500 font-black';
                                } else if (log.status === 'error') {
                                    statusColor = 'text-rose-600 font-black animate-pulse';
                                } else if (log.status === 'skipped') {
                                    statusColor = 'text-rose-500/40 font-bold';
                                }

                                let actorName = log.actor ? log.actor.first_name + ' ' + (log.actor.last_name || '') : 'ID: ' + log.actor_id;
                                let rawDate = log.created_at;
                                if(rawDate && rawDate.length > 18) {
                                    rawDate = rawDate.substring(11, 19);
                                }

                                let div = document.createElement('div');
                                div.className = 'hover:bg-white/[0.03] px-3 py-1.5 rounded-lg transition-colors flex flex-col md:flex-row md:gap-6 border border-transparent hover:border-white/5';
                                div.innerHTML = `
                                    <div class="flex gap-4 shrink-0 font-black uppercase text-[10px] tracking-widest">
                                        <span class="text-white/20">${rawDate}</span>
                                        <span class="${statusColor} w-24">[${log.status}]</span>
                                    </div>
                                    <div class="flex-1 font-medium">
                                        <span class="text-white font-black mr-4">${actorName}</span>
                                        <span class="text-white/40 italic">${log.message}</span>
                                    </div>
                                `;
                                tbody.appendChild(div);
                            }
                            
                            currentRenderedCount = data.logs.length;
                            if (isAtBottom) terminalBody.scrollTop = terminalBody.scrollHeight;
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
                setTimeout(() => modal.classList.add('hidden'), 500);
            }
        </script>
    @endpush
</x-admin-layout>
