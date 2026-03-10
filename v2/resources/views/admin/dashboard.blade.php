<x-admin-layout>
    @section('header_title', 'Admin Dashboard')

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Stat Card 1 -->
        <div class="glass p-6 rounded-3xl border-white/5 relative overflow-hidden group hover:glass-strong transition-all">
            <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:text-blue-500/10 transition-colors">
                <i class="bi bi-film"></i>
            </div>
            <div class="relative z-10">
                <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 block">Filme Gesamt</span>
                <div class="text-3xl font-black text-white mb-2">{{ number_format($stats['totalMovies'], 0, ',', '.') }}</div>
                <div class="flex items-center gap-2 text-xs text-blue-400 font-bold">
                    <i class="bi bi-arrow-up-short"></i>
                    <span>Aktiv im Katalog</span>
                </div>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="glass p-6 rounded-3xl border-white/5 relative overflow-hidden group hover:glass-strong transition-all">
            <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:text-purple-500/10 transition-colors">
                <i class="bi bi-people"></i>
            </div>
            <div class="relative z-10">
                <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 block">Schauspieler</span>
                <div class="text-3xl font-black text-white mb-2">{{ number_format($stats['totalActors'], 0, ',', '.') }}</div>
                <div class="flex items-center gap-2 text-xs text-purple-400 font-bold">
                    <i class="bi bi-person-check-fill"></i>
                    <span>Verknüpfte Profile</span>
                </div>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="glass p-6 rounded-3xl border-white/5 relative overflow-hidden group hover:glass-strong transition-all">
            <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:text-amber-500/10 transition-colors">
                <i class="bi bi-clock"></i>
            </div>
            <div class="relative z-10">
                <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 block">Gesamtlaufzeit</span>
                <div class="text-3xl font-black text-white mb-2">{{ number_format($stats['totalRuntime'] / 60, 0, ',', '.') }}h</div>
                <div class="flex items-center gap-2 text-xs text-amber-400 font-bold">
                    <i class="bi bi-play-circle"></i>
                    <span>Entertainment pur</span>
                </div>
            </div>
        </div>

        <!-- Stat Card 4 -->
        <div class="glass p-6 rounded-3xl border-white/5 relative overflow-hidden group hover:glass-strong transition-all">
            <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:text-emerald-500/10 transition-colors">
                <i class="bi bi-hdd-network"></i>
            </div>
            <div class="relative z-10">
                <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 block">Sammlungstypen</span>
                <div class="text-3xl font-black text-white mb-2">{{ $stats['collectionTypes']->count() }}</div>
                <div class="flex items-center gap-2 text-xs text-emerald-400 font-bold">
                    <i class="bi bi-tags"></i>
                    <span>Verschiedene Medientypen</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Distribution Chart Placeholder -->
        <div class="glass p-8 rounded-3xl border-white/5">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                <i class="bi bi-pie-chart text-blue-400"></i>
                Verteilung nach Medientyp
            </h3>
            <div class="space-y-4">
                @foreach($stats['collectionTypes'] as $type)
                    @php 
                        $percentage = ($type->count / $stats['totalMovies']) * 100;
                    @endphp
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-gray-200">{{ $type->collection_type ?: 'Unbekannt' }}</span>
                            <span class="text-xs text-gray-500">{{ $type->count }} ({{ round($percentage, 1) }}%)</span>
                        </div>
                        <div class="w-full bg-white/5 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-500 h-full rounded-full transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="glass p-8 rounded-3xl border-white/5 bg-gradient-to-br from-blue-500/5 to-purple-600/5">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                <i class="bi bi-lightning-charge text-amber-400"></i>
                Schnellzugriff
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="#" class="p-4 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-blue-500/30 transition-all flex flex-col items-center justify-center text-center gap-2 group">
                    <i class="bi bi-plus-circle text-2xl text-blue-400 group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs font-bold text-white">Neuer Film</span>
                </a>
                <a href="#" class="p-4 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-purple-500/30 transition-all flex flex-col items-center justify-center text-center gap-2 group">
                    <i class="bi bi-download text-2xl text-purple-400 group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs font-bold text-white">Import (TMDb)</span>
                </a>
                <a href="#" class="p-4 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-emerald-500/30 transition-all flex flex-col items-center justify-center text-center gap-2 group">
                    <i class="bi bi-people text-2xl text-emerald-400 group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs font-bold text-white">Schauspieler</span>
                </a>
                <a href="#" class="p-4 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-gray-500/30 transition-all flex flex-col items-center justify-center text-center gap-2 group">
                    <i class="bi bi-gear text-2xl text-gray-400 group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs font-bold text-white">Settings</span>
                </a>
            </div>
        </div>
    </div>
</x-admin-layout>
