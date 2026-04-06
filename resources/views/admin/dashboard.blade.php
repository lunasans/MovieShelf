<x-admin-layout>
    @section('header_title', 'System Dashboard')

    <div class="space-y-10">
        <!-- Dashboard Hero -->
        <div class="relative overflow-hidden rounded-[3rem] p-10 md:p-16 text-white border border-white/10 glass shadow-2xl">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-600/30 via-red-600/10 to-transparent"></div>
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-rose-500/10 rounded-full blur-[100px]"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-red-500/10 rounded-full blur-[100px]"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8 text-center md:text-left">
                <div>
                    <h2 class="text-3xl md:text-5xl font-black tracking-tight mb-4">Willkommen zurück, <span class="text-rose-400">{{ Auth::user()->name }}</span>!</h2>
                    <p class="text-white/60 text-lg md:text-xl font-medium">Deine Mediathek umfasst aktuell <span class="text-white font-bold">{{ number_format($stats['totalMovies']) }} Filme</span>. Alles unter Kontrolle?</p>
                </div>
                <div class="flex gap-4">
                    <div class="glass p-6 rounded-3xl border-white/10 flex flex-col items-center">
                        <span class="text-3xl font-black text-white">{{ $stats['visitsToday'] }}</span>
                        <span class="text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mt-1">Visits Today</span>
                    </div>
                    <div class="glass p-6 rounded-3xl border-white/10 flex flex-col items-center">
                        <span class="text-3xl font-black text-white">{{ $stats['missingTmdbCount'] }}</span>
                        <span class="text-[10px] font-black text-rose-400 uppercase tracking-[0.2em] mt-1">Pending Link</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Action Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
            <a href="{{ route('admin.tmdb.index') }}" class="glass p-6 rounded-3xl border-white/5 bg-gradient-to-br from-rose-500/10 to-transparent flex flex-col items-center justify-center gap-3 group hover:border-rose-500/50 transition-all text-center hover:-translate-y-1">
                <div class="w-14 h-14 rounded-2xl bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform shadow-lg shadow-rose-500/10">
                    <i class="bi bi-cloud-download text-2xl"></i>
                </div>
                <span class="text-[10px] font-black text-white uppercase tracking-[0.2em]">TMDb Import</span>
            </a>
            <a href="{{ route('admin.movies.create') }}" class="glass p-6 rounded-3xl border-white/5 bg-gradient-to-br from-rose-500/10 to-transparent flex flex-col items-center justify-center gap-3 group hover:border-rose-500/50 transition-all text-center hover:-translate-y-1">
                <div class="w-14 h-14 rounded-2xl bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform shadow-lg shadow-rose-500/10">
                    <i class="bi bi-plus-lg text-2xl"></i>
                </div>
                <span class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Neuer Film</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="glass p-6 rounded-3xl border-white/5 bg-gradient-to-br from-amber-500/10 to-transparent flex flex-col items-center justify-center gap-3 group hover:border-amber-500/50 transition-all text-center hover:-translate-y-1">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform shadow-lg shadow-amber-500/10">
                    <i class="bi bi-people text-2xl"></i>
                </div>
                <span class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Benutzer</span>
            </a>
            <a href="{{ route('admin.stats.index') }}" class="glass p-6 rounded-3xl border-white/5 bg-gradient-to-br from-rose-500/10 to-transparent flex flex-col items-center justify-center gap-3 group hover:border-rose-500/50 transition-all text-center hover:-translate-y-1">
                <div class="w-14 h-14 rounded-2xl bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform shadow-lg shadow-rose-500/10">
                    <i class="bi bi-graph-up text-2xl"></i>
                </div>
                <span class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Analysen</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="glass p-6 rounded-3xl border-white/5 bg-gradient-to-br from-rose-500/10 to-transparent flex flex-col items-center justify-center gap-3 group hover:border-rose-500/50 transition-all text-center hover:-translate-y-1">
                <div class="w-14 h-14 rounded-2xl bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform shadow-lg shadow-rose-500/10">
                    <i class="bi bi-sliders text-2xl"></i>
                </div>
                <span class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Settings</span>
            </a>
            <a href="{{ route('dashboard') }}" target="_blank" class="glass p-6 rounded-3xl border-white/5 bg-gradient-to-br from-white/5 to-transparent flex flex-col items-center justify-center gap-3 group hover:border-white/20 transition-all text-center hover:-translate-y-1">
                <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center text-white/40 group-hover:scale-110 transition-transform">
                    <i class="bi bi-box-arrow-up-right text-2xl"></i>
                </div>
                <span class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Frontend</span>
            </a>
        </div>

        <!-- Stats & Insights -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 space-y-10">
                <!-- Advanced Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="glass p-8 rounded-[2.5rem] border-white/5 group hover:border-rose-500/20 transition-all relative overflow-hidden">
                        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-rose-500/5 rounded-full blur-3xl group-hover:bg-rose-500/10 transition-all"></div>
                        <div class="flex items-center gap-6">
                            <div class="w-20 h-20 bg-rose-500/10 rounded-3xl flex items-center justify-center text-rose-400 text-3xl group-hover:scale-110 transition-transform">
                                <i class="bi bi-collection-play-fill"></i>
                            </div>
                            <div>
                                <div class="text-4xl font-black text-white tracking-tight">{{ number_format($stats['totalMovies']) }}</div>
                                <div class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] mt-1">Filme Gesamt</div>
                            </div>
                        </div>
                    </div>
                    <div class="glass p-8 rounded-[2.5rem] border-white/5 group hover:border-rose-500/20 transition-all relative overflow-hidden">
                        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-rose-500/5 rounded-full blur-3xl group-hover:bg-rose-500/10 transition-all"></div>
                        <div class="flex items-center gap-6">
                            <div class="w-20 h-20 bg-rose-500/10 rounded-3xl flex items-center justify-center text-rose-400 text-3xl group-hover:scale-110 transition-transform">
                                <i class="bi bi-person-hearts"></i>
                            </div>
                            <div>
                                <div class="text-4xl font-black text-white tracking-tight">{{ number_format($stats['totalActors']) }}</div>
                                <div class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] mt-1">Schauspieler</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Genre Visualizer -->
                <div class="glass p-10 rounded-[3rem] border-white/5 relative overflow-hidden">
                    <div class="flex items-center justify-between mb-10">
                        <div>
                            <h3 class="text-2xl font-black text-white tracking-tight flex items-center gap-4">
                                <i class="bi bi-tags-fill text-rose-400"></i>
                                Genre-Verteilung
                            </h3>
                            <p class="text-[10px] text-white/30 uppercase tracking-[0.3em] font-black mt-2">Die Top 10 Kategorien deiner Sammlung</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-6">
                        @foreach($stats['genres'] as $genre)
                            @php $percentage = ($genre->count / $stats['totalMovies']) * 100; @endphp
                            <div class="space-y-2 group">
                                <div class="flex justify-between text-[11px] font-black uppercase tracking-widest">
                                    <span class="text-white/80 group-hover:text-white transition-colors">{{ $genre->genre }}</span>
                                    <span class="text-rose-400">{{ $genre->count }}</span>
                                </div>
                                <div class="h-2 bg-white/5 rounded-full overflow-hidden p-[2px]">
                                    <div class="h-full bg-gradient-to-r from-rose-600 via-red-500 to-rose-400 rounded-full shadow-[0_0_15px_rgba(225,29,72,0.3)] group-hover:shadow-[0_0_20px_rgba(225,29,72,0.5)] transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Side Cards -->
            <div class="space-y-10">
                <!-- System Activity -->
                <div class="glass p-10 rounded-[3rem] border-white/5 bg-gradient-to-b from-white/[0.03] to-transparent">
                    <h3 class="text-xl font-black text-white mb-8 flex items-center gap-4">
                        <i class="bi bi-lightning-charge-fill text-rose-400"></i>
                        Aktivität
                    </h3>
                    <div class="space-y-8 relative">
                        <div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-white/5"></div>
                        @forelse($stats['recentActivity'] as $log)
                            @php
                                $details = json_decode($log->details, true);
                                $actionInfo = match($log->action) {
                                    'MOVIE_IMPORT' => ['label' => 'Importiert', 'icon' => 'bi-plus-circle-fill', 'color' => 'bg-emerald-500/20 text-emerald-400'],
                                    'MOVIE_UPDATE' => ['label' => 'Update', 'icon' => 'bi-pencil-fill', 'color' => 'bg-rose-500/20 text-rose-400'],
                                    'MOVIE_DELETE' => ['label' => 'Gelöscht', 'icon' => 'bi-trash-fill', 'color' => 'bg-rose-500/20 text-rose-400'],
                                    default => ['label' => $log->action, 'icon' => 'bi-info-circle-fill', 'color' => 'bg-white/10 text-white/50']
                                };
                            @endphp
                            <div class="relative pl-12 group">
                                <div class="absolute left-0 top-1 w-6 h-6 rounded-lg {{ $actionInfo['color'] }} flex items-center justify-center text-[10px] z-10 shadow-lg shadow-black/20 group-hover:scale-110 transition-transform">
                                    <i class="bi {{ $actionInfo['icon'] }}"></i>
                                </div>
                                <div class="text-[10px] font-black text-white/20 uppercase tracking-widest mb-1">{{ $log->created_at->diffForHumans() }}</div>
                                <div class="text-xs font-bold text-white/90 leading-tight group-hover:text-rose-400 transition-colors">{{ $actionInfo['label'] }}</div>
                                <div class="text-[9px] text-white/30 font-bold truncate mt-1 italic">
                                    {{ isset($details['title']) ? $details['title'] : $log->details }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 text-white/20 text-sm italic font-medium">Keine aktuellen Aktivitäten.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Data Health Checklist -->
                <div class="glass p-10 rounded-[3rem] border-white/10 bg-gradient-to-br from-rose-500/5 to-transparent">
                    <h3 class="text-sm font-black text-white/40 mb-8 uppercase tracking-[0.3em]">Datenqualität</h3>
                    <div class="space-y-5">
                        <div class="flex items-center justify-between p-5 rounded-2xl bg-white/5 border border-white/5 group hover:border-rose-500/30 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-rose-500/20 flex items-center justify-center text-rose-400">
                                    <i class="bi bi-link-45deg text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-white">{{ $stats['missingTmdbCount'] }}</div>
                                    <div class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Ohne TMDb</div>
                                </div>
                            </div>
                            <a href="{{ route('admin.movies.index', ['filter' => 'missing_tmdb']) }}" class="text-white/20 hover:text-white transition-colors">
                                <i class="bi bi-arrow-right-short text-2xl"></i>
                            </a>
                        </div>
                        <div class="flex items-center justify-between p-5 rounded-2xl bg-white/5 border border-white/5 group hover:border-amber-500/30 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400">
                                    <i class="bi bi-image text-lg"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-white">{{ $stats['missingCoverCount'] }}</div>
                                    <div class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Ohne Cover</div>
                                </div>
                            </div>
                            <a href="{{ route('admin.movies.index', ['filter' => 'missing_cover']) }}" class="text-white/20 hover:text-white transition-colors">
                                <i class="bi bi-arrow-right-short text-2xl"></i>
                            </a>
                        </div>
                        <div class="flex items-center justify-between p-5 rounded-2xl bg-white/5 border border-white/5 group hover:border-indigo-500/30 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                                    <i class="bi bi-play-btn text-lg"></i>
                                </div>
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <div class="text-sm font-black text-white">{{ $stats['missingTrailerCount'] }}</div>
                                        @if($lastStatus = \App\Models\Setting::get('smart_trailer_last_status'))
                                            <i class="bi {{ $lastStatus === 'success' ? 'bi-check-circle-fill text-emerald-500' : 'bi-exclamation-circle-fill text-rose-500' }} text-[10px]" title="{{ $lastStatus === 'error' ? \App\Models\Setting::get('smart_trailer_last_error') : '' }}"></i>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Ohne Trailer</div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.movies.sync-logs') }}" class="text-white/10 hover:text-white transition-colors" title="Sync-Verlauf anzeigen">
                                        <i class="bi bi-list-ul text-lg"></i>
                                    </a>
                                    <a href="{{ route('admin.movies.index', ['filter' => 'missing_trailer']) }}" class="text-white/20 hover:text-white transition-colors">
                                        <i class="bi bi-arrow-right-short text-2xl"></i>
                                    </a>
                                </div>
                                @if($lastRun = \App\Models\Setting::get('smart_trailer_last_run'))
                                    <span class="text-[8px] text-white/10 font-bold uppercase tracking-widest">{{ \Carbon\Carbon::parse($lastRun)->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>