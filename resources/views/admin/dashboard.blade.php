<x-admin-layout>
    @section('header_title', 'Dashboard Overview')

    <div class="space-y-8 animate-in fade-in duration-700">
        
        <!-- Quick Action Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <a href="{{ route('admin.tmdb.index') }}" class="glass p-4 rounded-2xl border-white/5 bg-gradient-to-br from-indigo-500/10 to-transparent flex flex-col items-center justify-center gap-2 group hover:border-indigo-500/50 transition-all text-center">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
                    <i class="bi bi-cloud-download"></i>
                </div>
                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">TMDb Import</span>
            </a>
            <a href="{{ route('admin.movies.create') }}" class="glass p-4 rounded-2xl border-white/5 bg-gradient-to-br from-emerald-500/10 to-transparent flex flex-col items-center justify-center gap-2 group hover:border-emerald-500/50 transition-all text-center">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                    <i class="bi bi-plus-lg"></i>
                </div>
                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Film Add</span>
            </a>
            <a href="{{ route('admin.update.index') }}" class="glass p-4 rounded-2xl border-white/5 bg-gradient-to-br from-amber-500/10 to-transparent flex flex-col items-center justify-center gap-2 group hover:border-amber-500/50 transition-all text-center">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Update</span>
            </a>
             <a href="{{ route('admin.stats.index') }}" class="glass p-4 rounded-2xl border-white/5 bg-gradient-to-br from-blue-500/10 to-transparent flex flex-col items-center justify-center gap-2 group hover:border-blue-500/50 transition-all text-center">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-400 group-hover:scale-110 transition-transform">
                    <i class="bi bi-graph-up"></i>
                </div>
                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Stats</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="glass p-4 rounded-2xl border-white/5 bg-gradient-to-br from-rose-500/10 to-transparent flex flex-col items-center justify-center gap-2 group hover:border-rose-500/50 transition-all text-center">
                <div class="w-10 h-10 rounded-xl bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform">
                    <i class="bi bi-gear"></i>
                </div>
                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Settings</span>
            </a>
            <a href="{{ route('dashboard') }}" target="_blank" class="glass p-4 rounded-2xl border-white/5 bg-gradient-to-br from-white/10 to-transparent flex flex-col items-center justify-center gap-2 group hover:border-white/30 transition-all text-center">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white/60 group-hover:scale-110 transition-transform">
                    <i class="bi bi-box-arrow-up-right"></i>
                </div>
                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Frontend</span>
            </a>
        </div>

        <!-- Main Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-blue-500/10 to-transparent flex items-center gap-5 group hover:border-blue-500/30 transition-all duration-500">
                <div class="w-14 h-14 bg-blue-500/20 rounded-2xl flex items-center justify-center text-blue-400 text-2xl group-hover:scale-110 transition-transform shadow-lg shadow-blue-500/10">
                    <i class="bi bi-film"></i>
                </div>
                <div>
                    <div class="text-3xl font-black text-white">{{ number_format($stats['totalMovies']) }}</div>
                    <div class="text-[10px] font-black text-white/30 uppercase tracking-widest mt-1">Filme</div>
                </div>
            </div>

            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-purple-500/10 to-transparent flex items-center gap-5 group hover:border-purple-500/30 transition-all duration-500">
                <div class="w-14 h-14 bg-purple-500/20 rounded-2xl flex items-center justify-center text-purple-400 text-2xl group-hover:scale-110 transition-transform shadow-lg shadow-purple-500/10">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="text-3xl font-black text-white">{{ number_format($stats['totalActors']) }}</div>
                    <div class="text-[10px] font-black text-white/30 uppercase tracking-widest mt-1">Stars</div>
                </div>
            </div>

            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-emerald-500/10 to-transparent flex items-center gap-5 group hover:border-emerald-500/30 transition-all duration-500">
                <div class="w-14 h-14 bg-emerald-500/20 rounded-2xl flex items-center justify-center text-emerald-400 text-2xl group-hover:scale-110 transition-transform shadow-lg shadow-emerald-500/10">
                    <i class="bi bi-eye"></i>
                </div>
                <div>
                    <div class="text-3xl font-black text-white">{{ number_format($stats['visitsToday']) }}</div>
                    <div class="text-[10px] font-black text-white/30 uppercase tracking-widest mt-1">Besucher Heute</div>
                </div>
            </div>

            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-amber-500/10 to-transparent flex items-center gap-5 group hover:border-amber-500/30 transition-all duration-500">
                <div class="w-14 h-14 bg-amber-500/20 rounded-2xl flex items-center justify-center text-amber-400 text-2xl group-hover:scale-110 transition-transform shadow-lg shadow-amber-500/10">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div>
                    <div class="text-3xl font-black text-white">{{ number_format($stats['totalUsers']) }}</div>
                    <div class="text-[10px] font-black text-white/30 uppercase tracking-widest mt-1">Benutzer</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Action Required / Data Health -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="glass p-6 rounded-[2rem] border-white/5 relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-500/10 rounded-full blur-2xl group-hover:bg-rose-500/20 transition-colors"></div>
                        <h4 class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Fehlende Verknüpfungen
                        </h4>
                        <div class="flex items-end justify-between">
                            <div class="text-4xl font-black text-white">{{ $stats['missingTmdbCount'] }}</div>
                            <a href="{{ route('admin.movies.index', ['filter' => 'missing_tmdb']) }}" class="text-[10px] font-black text-white/30 hover:text-white uppercase tracking-widest underline decoration-rose-500/40 underline-offset-4 transition-all">Details ansehen</a>
                        </div>
                        <p class="text-[10px] text-white/20 mt-2 font-bold italic">Diese Filme haben noch keine TMDb ID-Verknüpfung.</p>
                    </div>

                    <div class="glass p-6 rounded-[2rem] border-white/5 relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-colors"></div>
                        <h4 class="text-[10px] font-black text-amber-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="bi bi-image"></i>
                            Fehlende Cover
                        </h4>
                        <div class="flex items-end justify-between">
                            <div class="text-4xl font-black text-white">{{ $stats['missingCoverCount'] }}</div>
                            <a href="{{ route('admin.movies.index', ['filter' => 'missing_cover']) }}" class="text-[10px] font-black text-white/30 hover:text-white uppercase tracking-widest underline decoration-amber-500/40 underline-offset-4 transition-all">Details ansehen</a>
                        </div>
                        <p class="text-[10px] text-white/20 mt-2 font-bold italic">Filme ohne hinterlegte Poster-Datei.</p>
                    </div>
                </div>

                <!-- Genre Distribution -->
                <div class="glass p-8 rounded-[2.5rem] border-white/5 overflow-hidden relative">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-black text-white flex items-center gap-3">
                                <i class="bi bi-tags text-blue-400"></i>
                                Top Genres
                            </h3>
                            <p class="text-[10px] text-white/30 uppercase tracking-widest font-bold mt-1">Verteilung in deiner Sammlung</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                        @foreach($stats['genres'] as $genre)
                            @php
                                $percentage = ($genre->count / $stats['totalMovies']) * 100;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between text-[10px] font-black text-white/70 uppercase tracking-wider">
                                    <span>{{ $genre->genre }}</span>
                                    <span class="text-blue-400">{{ $genre->count }}</span>
                                </div>
                                <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-blue-600 to-indigo-500 shadow-[0_0_10px_rgba(59,130,246,0.3)] transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Recent Activity & Latest -->
            <div class="space-y-8">
                <!-- Recent Activity Log -->
                 <div class="glass p-8 rounded-[2.5rem] border-white/5 bg-gradient-to-b from-white/[0.02] to-transparent">
                    <h3 class="text-lg font-black text-white mb-6 flex items-center gap-3">
                        <i class="bi bi-activity text-indigo-400"></i>
                        System-Aktivität
                    </h3>
                    <div class="space-y-6">
                        @forelse($stats['recentActivity'] as $log)
                            <div class="relative pl-6 border-l border-white/10 group">
                                <div class="absolute left-[-5px] top-1 w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.5)] group-hover:scale-125 transition-transform"></div>
                                <div class="text-[10px] font-black text-white/30 uppercase tracking-widest mb-1">{{ $log->created_at->diffForHumans() }}</div>
                                <div class="text-xs font-bold text-white/90 leading-tight">{{ $log->action }}</div>
                                <div class="text-[9px] text-white/20 mt-1 truncate">{{ $log->details }}</div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-white/20 text-xs italic">Keine Aktivitäten gefunden.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Latest Movies -->
                <div class="glass p-8 rounded-[2.5rem] border-white/5">
                    <h3 class="text-sm font-black text-white mb-6 flex items-center gap-3 uppercase tracking-widest">
                        <i class="bi bi-clock-history text-amber-400"></i>
                        Zuletzt Hinzugefügt
                    </h3>
                    <div class="space-y-3">
                        @foreach($stats['latestMovies'] as $movie)
                            <a href="{{ route('movies.show', $movie->id) }}" class="flex items-center gap-4 p-2 rounded-2xl hover:bg-white/5 transition-all group">
                                <div class="w-10 h-14 bg-white/5 rounded-xl overflow-hidden border border-white/10 flex-shrink-0">
                                    @if($movie->cover_id)
                                        <img src="{{ Storage::url($movie->cover_id) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="flex items-center justify-center h-full text-white/10 text-xs">
                                            <i class="bi bi-film"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-white truncate group-hover:text-amber-400 transition-colors">{{ $movie->title }}</div>
                                    <div class="text-[9px] text-white/30 uppercase tracking-widest font-black mt-1">{{ $movie->year }} • {{ Str::limit($movie->genre, 20) }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Decade Distribution - Full Width -->
        <div class="glass p-8 rounded-[2.5rem] border-white/5 relative overflow-hidden">
             <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full blur-[100px] pointer-events-none"></div>
            <h3 class="text-lg font-black text-white mb-8 flex items-center gap-3">
                <i class="bi bi-calendar-range text-emerald-400"></i>
                Die Zeitreise <span class="text-emerald-500/40 font-light ml-2 uppercase text-[10px] tracking-[0.4em]">Decades</span>
            </h3>
            <div class="flex items-end gap-3 h-48 px-4">
                @php
                    $maxDecadeCount = $stats['decades']->max('count') ?: 1;
                @endphp
                @foreach($stats['decades'] as $decade)
                    @php
                        $height = ($decade->count / $maxDecadeCount) * 100;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-3 group">
                        <div class="text-[10px] text-emerald-400 font-black opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0">{{ $decade->count }}</div>
                        <div class="w-full bg-gradient-to-t from-emerald-500/10 to-emerald-500/40 rounded-t-xl group-hover:from-emerald-500/20 group-hover:to-emerald-500/60 transition-all cursor-help relative shadow-lg shadow-emerald-500/5" style="height: {{ $height }}%" title="{{ $decade->count }} Filme">
                            <div class="absolute inset-x-0 top-0 h-1 bg-emerald-400 opacity-50 rounded-full"></div>
                        </div>
                        <div class="text-[9px] font-black text-white/40 uppercase tracking-widest">{{ $decade->decade }}s</div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-admin-layout>
