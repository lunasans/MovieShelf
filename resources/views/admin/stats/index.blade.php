<x-admin-layout>
    @section('header_title', 'Besucher Statistiken')

    <div class="space-y-8">
        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-blue-500/10 to-transparent flex flex-col gap-2">
                <div class="text-[10px] font-black text-white/30 uppercase tracking-widest pl-1">Heute</div>
                <div class="flex items-end gap-3 px-1">
                    <span class="text-3xl font-black text-white leading-none">{{ number_format($todayCount) }}</span>
                    @php
                        $diff = $todayCount - $yesterdayCount;
                        $isUp = $diff >= 0;
                    @endphp
                    <span class="text-xs font-bold {{ $isUp ? 'text-emerald-400' : 'text-rose-400' }} mb-1">
                        <i class="bi bi-caret-{{ $isUp ? 'up' : 'down' }}-fill"></i>
                        {{ $diff != 0 ? number_format(abs($diff)) : '0' }}
                    </span>
                </div>
            </div>

            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-purple-500/10 to-transparent flex flex-col gap-2">
                <div class="text-[10px] font-black text-white/30 uppercase tracking-widest pl-1">Letzte 7 Tage</div>
                <div class="flex items-end gap-3 px-1">
                    <span class="text-3xl font-black text-white leading-none">{{ number_format($totalLast7Days) }}</span>
                </div>
            </div>

            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-emerald-500/10 to-transparent flex flex-col gap-2">
                <div class="text-[10px] font-black text-white/30 uppercase tracking-widest pl-1">Ø Letzte 30 Tage</div>
                <div class="flex items-end gap-3 px-1">
                    <span class="text-3xl font-black text-white leading-none">{{ number_format($avgLast30Days) }}</span>
                </div>
            </div>

            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-amber-500/10 to-transparent flex flex-col gap-2">
                <div class="text-[10px] font-black text-white/30 uppercase tracking-widest pl-1">Peak (30 Tage)</div>
                <div class="flex items-end gap-2 px-1">
                    <span class="text-3xl font-black text-white leading-none">{{ number_format($peak) }}</span>
                    <span class="text-[10px] font-bold text-white/30 mb-1">am {{ $peakDate }}</span>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="glass p-8 rounded-[2.5rem] border-white/5 relative overflow-hidden">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-white flex items-center gap-3">
                        <i class="bi bi-graph-up text-blue-400"></i>
                        Besucher-Trend (Letzte 30 Tage)
                    </h3>
                    <p class="text-[10px] text-white/30 uppercase tracking-widest font-bold mt-1">Eindeutige tägliche Zugriffe</p>
                </div>
                <div class="bg-white/5 px-4 py-2 rounded-xl text-[10px] font-black text-white/40 uppercase tracking-widest">
                    Gesamt: {{ number_format($allTimeTotal) }}
                </div>
            </div>

            <div class="h-[400px] w-full">
                <canvas id="visitorChart"></canvas>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="glass p-8 rounded-[2.5rem] border-white/5">
            <h3 class="text-lg font-black text-white mb-6 flex items-center gap-3">
                <i class="bi bi-table text-emerald-400"></i>
                Tagesübersicht
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-black text-white/30 uppercase tracking-widest border-b border-white/5">
                            <th class="py-4 px-4 font-black">Datum</th>
                            <th class="py-4 px-4 font-black text-right">Besucher</th>
                            <th class="py-4 px-4 font-black text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($stats->reverse()->take(10) as $stat)
                            <tr class="group hover:bg-white/5 transition-all">
                                <td class="py-4 px-4 text-sm font-bold text-white/70">{{ $stat['label'] }}</td>
                                <td class="py-4 px-4 text-sm font-black text-white text-right">{{ number_format($stat['count']) }}</td>
                                <td class="py-4 px-4 text-right">
                                    <div class="h-1.5 w-full max-w-[100px] ml-auto bg-white/5 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500/50" style="width: {{ ($stat['count'] / ($peak ?: 1)) * 100 }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.js" integrity="sha256-x+LL+wNI+ZAd7MSX8xbB/qhCAgm2EEEv+4j9PVFvnTA=" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('visitorChart').getContext('2d');
            
            // Create gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [{
                        label: 'Besucher',
                        data: {!! json_encode($data) !!},
                        borderColor: '#3b82f6',
                        borderWidth: 4,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6,
                        pointHoverBorderWidth: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { family: 'Inter', weight: 'black', size: 12 },
                            bodyFont: { family: 'Inter', weight: 'bold', size: 14 },
                            padding: 12,
                            cornerRadius: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toLocaleString() + ' Besucher';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                            ticks: { color: 'rgba(255, 255, 255, 0.3)', font: { family: 'Inter', weight: 'bold', size: 10 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: 'rgba(255, 255, 255, 0.3)', font: { family: 'Inter', weight: 'bold', size: 10 } }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-admin-layout>