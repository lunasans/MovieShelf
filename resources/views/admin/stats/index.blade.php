<x-admin-layout>
    @section('header_title', 'Besucher & Traffic')

    <div class="max-w-7xl mx-auto space-y-10">
        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="glass p-8 rounded-[2.5rem] border-white/5 bg-gradient-to-br from-rose-600/10 to-transparent flex flex-col gap-3 relative overflow-hidden">
                <div class="text-[10px] font-black text-rose-500/60 uppercase tracking-[0.2em] relative z-10">Zugriffe Heute</div>
                <div class="flex items-end gap-3 relative z-10">
                    <span class="text-4xl font-black text-white leading-none tracking-tighter">{{ number_format($todayCount) }}</span>
                    @php
                        $diff = $todayCount - $yesterdayCount;
                        $isUp = $diff >= 0;
                    @endphp
                    <span class="text-xs font-black {{ $isUp ? 'text-emerald-400' : 'text-rose-400' }} mb-1 flex items-center">
                        <i class="bi bi-caret-{{ $isUp ? 'up' : 'down' }}-fill mr-1"></i>
                        {{ $diff != 0 ? number_format(abs($diff)) : '0' }}
                    </span>
                </div>
            </div>

            <div class="glass p-8 rounded-[2.5rem] border-white/5 bg-gradient-to-br from-rose-600/5 to-transparent flex flex-col gap-3">
                <div class="text-[10px] font-black text-white/20 uppercase tracking-[0.2em]">Wochen-Schnitt</div>
                <div class="flex items-end gap-3">
                    <span class="text-4xl font-black text-white leading-none tracking-tighter">{{ number_format($totalLast7Days) }}</span>
                </div>
            </div>

            <div class="glass p-8 rounded-[2.5rem] border-white/5 bg-gradient-to-br from-rose-600/5 to-transparent flex flex-col gap-3">
                <div class="text-[10px] font-black text-white/20 uppercase tracking-[0.2em]">Monats-Schnitt</div>
                <div class="flex items-end gap-3">
                    <span class="text-4xl font-black text-white leading-none tracking-tighter">{{ number_format($avgLast30Days) }}</span>
                </div>
            </div>

            <div class="glass p-8 rounded-[2.5rem] border-white/5 bg-gradient-to-br from-rose-600/10 to-transparent flex flex-col gap-3">
                <div class="text-[10px] font-black text-rose-500/60 uppercase tracking-[0.2em]">Record (30d)</div>
                <div class="flex items-end gap-2">
                    <span class="text-4xl font-black text-white leading-none tracking-tighter">{{ number_format($peak) }}</span>
                </div>
                <span class="text-[9px] font-black text-white/20 uppercase tracking-widest">{{ $peakDate }}</span>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="glass p-10 rounded-[3.5rem] border-white/5 relative overflow-hidden shadow-2xl">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-12 gap-6 relative z-10">
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight uppercase flex items-center gap-4">
                        <i class="bi bi-graph-up-arrow text-rose-500"></i>
                        Besucher-Entwicklung
                    </h3>
                    <p class="text-[10px] text-white/30 uppercase tracking-[0.3em] font-black mt-2 italic">Analyse der letzten 30 Kalendertage</p>
                </div>
                <div class="bg-rose-500/10 border border-rose-500/20 px-6 py-3 rounded-2xl text-[10px] font-black text-rose-400 uppercase tracking-widest flex items-center gap-3">
                    <i class="bi bi-person-check-fill"></i>
                    All-Time: {{ number_format($allTimeTotal) }}
                </div>
            </div>

            <div class="h-[450px] w-full">
                <canvas id="visitorChart"></canvas>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="glass p-10 rounded-[3.5rem] border-white/5 shadow-2xl relative overflow-hidden">
            <div class="flex items-center gap-4 mb-10">
                <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-rose-500">
                    <i class="bi bi-list-task text-xl"></i>
                </div>
                <h3 class="text-xl font-black text-white tracking-tight uppercase">Tages-Protokoll</h3>
            </div>
            
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] border-b border-white/5">
                            <th class="py-6 px-4">Datum / Zeitspanne</th>
                            <th class="py-6 px-4 text-right">Eindeutige Besucher</th>
                            <th class="py-6 px-4 text-right">Relative Last</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.03]">
                        @foreach($stats->reverse()->take(10) as $stat)
                            <tr class="group hover:bg-white/[0.02] transition-all">
                                <td class="py-6 px-4 text-xs font-black text-white/40 group-hover:text-white transition-colors">{{ $stat['label'] }}</td>
                                <td class="py-6 px-4 text-base font-black text-white text-right">{{ number_format($stat['count']) }}</td>
                                <td class="py-6 px-4 text-right">
                                    <div class="h-2 w-full max-w-[120px] ml-auto bg-white/5 rounded-full overflow-hidden p-0.5 border border-white/5">
                                        <div class="h-full bg-gradient-to-r from-rose-600 to-red-400 rounded-full shadow-lg shadow-rose-600/20 transition-all duration-1000" style="width: {{ ($stat['count'] / ($peak ?: 1)) * 100 }}%"></div>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('visitorChart').getContext('2d');
            
            const gradient = ctx.createLinearGradient(0, 0, 0, 450);
            gradient.addColorStop(0, 'rgba(225, 29, 72, 0.2)');
            gradient.addColorStop(1, 'rgba(225, 29, 72, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [{
                        label: 'Besucher',
                        data: {!! json_encode($data) !!},
                        borderColor: '#e11d48',
                        borderWidth: 5,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: '#e11d48',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(10, 10, 12, 0.9)',
                            backdropFilter: 'blur(10px)',
                            titleFont: { family: 'Inter', weight: 'black', size: 10 },
                            bodyFont: { family: 'Inter', weight: 'black', size: 16 },
                            padding: 20,
                            cornerRadius: 20,
                            displayColors: false,
                            borderColor: 'rgba(225, 29, 72, 0.3)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255, 255, 255, 0.03)', drawBorder: false },
                            ticks: { 
                                color: 'rgba(255, 255, 255, 0.2)', 
                                font: { family: 'Inter', weight: 'black', size: 9 },
                                padding: 15
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { 
                                color: 'rgba(255, 255, 255, 0.2)', 
                                font: { family: 'Inter', weight: 'black', size: 9 },
                                padding: 15
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-admin-layout>