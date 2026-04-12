<x-admin-layout>
    @section('header_title', 'Dashboard')

    @php
        $runtimeHours = intdiv($stats['totalRuntime'], 60);
        $runtimeDays  = intdiv($runtimeHours, 24);
        $runtimeRest  = $runtimeHours % 24;
        $runtimeLabel = $runtimeDays > 0 ? "{$runtimeDays}d {$runtimeRest}h" : "{$runtimeHours}h";
        $topGenreCount = $stats['genres']->first()?->count ?: 1;
    @endphp

    <div class="space-y-8">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black text-white tracking-tight">
                    Willkommen, <span class="text-rose-400">{{ Auth::user()->name }}</span>
                </h2>
                <p class="text-white/30 text-sm font-medium mt-1">{{ now()->isoFormat('dddd, D. MMMM YYYY') }}</p>
            </div>
            <a href="{{ route('dashboard') }}" target="_blank"
               class="flex items-center gap-2 text-xs font-black text-white/30 uppercase tracking-widest hover:text-white transition-colors">
                <i class="bi bi-box-arrow-up-right"></i> Frontend
            </a>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-rose-500/20 transition-all group">
                <i class="bi bi-collection-play-fill text-rose-400 text-lg"></i>
                <div class="text-3xl font-black text-white mt-2">{{ number_format($stats['totalMovies']) }}</div>
                <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Filme</div>
            </div>
            <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-rose-500/20 transition-all group">
                <i class="bi bi-person-hearts text-rose-400 text-lg"></i>
                <div class="text-3xl font-black text-white mt-2">{{ number_format($stats['totalActors']) }}</div>
                <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Schauspieler</div>
            </div>
            <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-amber-500/20 transition-all group">
                <i class="bi bi-people-fill text-amber-400 text-lg"></i>
                <div class="text-3xl font-black text-white mt-2">{{ number_format($stats['totalUsers']) }}</div>
                <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Benutzer</div>
            </div>
            <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-indigo-500/20 transition-all group">
                <i class="bi bi-clock-history text-indigo-400 text-lg"></i>
                <div class="text-3xl font-black text-white mt-2">{{ $runtimeLabel }}</div>
                <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Laufzeit</div>
            </div>
            <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-emerald-500/20 transition-all group">
                <i class="bi bi-graph-up-arrow text-emerald-400 text-lg"></i>
                <div class="text-3xl font-black text-white mt-2">{{ number_format($stats['visitsToday']) }}</div>
                <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Besuche heute</div>
            </div>
            <div class="glass p-6 rounded-3xl border-white/5 flex flex-col gap-1 hover:border-emerald-500/20 transition-all group">
                <i class="bi bi-bar-chart-fill text-emerald-400 text-lg"></i>
                <div class="text-3xl font-black text-white mt-2">{{ number_format($stats['visitsTotal']) }}</div>
                <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Besuche gesamt</div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-3 md:grid-cols-5 gap-3">
            <a href="{{ route('admin.tmdb.index') }}"
               class="glass p-4 rounded-2xl border-white/5 flex items-center gap-3 hover:border-rose-500/40 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform shrink-0">
                    <i class="bi bi-cloud-download"></i>
                </div>
                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">TMDb Import</span>
            </a>
            <a href="{{ route('admin.movies.index') }}"
               class="glass p-4 rounded-2xl border-white/5 flex items-center gap-3 hover:border-rose-500/40 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-rose-500/20 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform shrink-0">
                    <i class="bi bi-film"></i>
                </div>
                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Alle Filme</span>
            </a>
            <a href="{{ route('admin.users.index') }}"
               class="glass p-4 rounded-2xl border-white/5 flex items-center gap-3 hover:border-amber-500/40 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform shrink-0">
                    <i class="bi bi-people"></i>
                </div>
                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Benutzer</span>
            </a>
            <a href="{{ route('admin.stats.index') }}"
               class="glass p-4 rounded-2xl border-white/5 flex items-center gap-3 hover:border-indigo-500/40 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform shrink-0">
                    <i class="bi bi-graph-up"></i>
                </div>
                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Statistiken</span>
            </a>
            <a href="{{ route('admin.settings.index') }}"
               class="glass p-4 rounded-2xl border-white/5 flex items-center gap-3 hover:border-white/20 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-white/40 group-hover:scale-110 transition-transform shrink-0">
                    <i class="bi bi-sliders"></i>
                </div>
                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Einstellungen</span>
            </a>
        </div>

        {{-- Main Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Left: Genre + Collection Types --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- Genre Chart --}}
                <div class="glass p-8 rounded-[2.5rem] border-white/5">
                    <h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
                        <i class="bi bi-tags-fill text-rose-400"></i> Genre-Verteilung
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-5">
                        @foreach($stats['genres'] as $genre)
                            @php $pct = round(($genre->count / $topGenreCount) * 100); @endphp
                            <div class="space-y-1.5 group">
                                <div class="flex justify-between text-[11px] font-black uppercase tracking-widest">
                                    <span class="text-white/70 group-hover:text-white transition-colors">{{ $genre->genre }}</span>
                                    <span class="text-rose-400">{{ $genre->count }}</span>
                                </div>
                                <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-rose-600 to-rose-400 rounded-full transition-all duration-700"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Collection Types + Top Actors --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    {{-- Collection Types --}}
                    <div class="glass p-8 rounded-[2.5rem] border-white/5">
                        <h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
                            <i class="bi bi-collection text-indigo-400"></i> Kollektion
                        </h3>
                        <div class="space-y-3">
                            @foreach($stats['collectionTypes'] as $type)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 hover:bg-white/8 transition-colors">
                                    <span class="text-xs font-black text-white/70 uppercase tracking-widest">
                                        {{ $type->collection_type ?: 'Unbekannt' }}
                                    </span>
                                    <span class="text-sm font-black text-white">{{ number_format($type->count) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Top Actors --}}
                    <div class="glass p-8 rounded-[2.5rem] border-white/5">
                        <h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
                            <i class="bi bi-star-fill text-amber-400"></i> Top Schauspieler
                        </h3>
                        <div class="space-y-3">
                            @foreach($stats['topActors'] as $i => $actor)
                                <div class="flex items-center gap-3 group">
                                    <span class="text-[10px] font-black text-white/20 w-4 shrink-0">{{ $i + 1 }}</span>
                                    @if($actor->profile_path)
                                        <img src="/storage/{{ $actor->profile_path }}"
                                             class="w-8 h-8 rounded-full object-cover bg-white/5 shrink-0"
                                             alt="{{ $actor->first_name }}">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center shrink-0">
                                            <i class="bi bi-person text-white/20 text-xs"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-bold text-white/80 truncate group-hover:text-white transition-colors">
                                            {{ $actor->first_name }} {{ $actor->last_name }}
                                        </div>
                                        <div class="text-[10px] text-white/30 font-bold">{{ $actor->movies_count }} Filme</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- Latest Movies --}}
                <div class="glass p-8 rounded-[2.5rem] border-white/5">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] flex items-center gap-3">
                            <i class="bi bi-clock text-rose-400"></i> Zuletzt hinzugefügt
                        </h3>
                        <a href="{{ route('admin.movies.index') }}"
                           class="text-[10px] font-black text-white/20 uppercase tracking-widest hover:text-rose-400 transition-colors">
                            Alle →
                        </a>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                        @foreach($stats['latestMovies'] as $movie)
                            <a href="{{ route('admin.movies.edit', $movie) }}"
                               class="group flex flex-col gap-2">
                                @if($movie->cover_id)
                                    <img src="/storage/{{ $movie->cover_id }}"
                                         class="w-full aspect-[2/3] object-cover rounded-xl bg-white/5 group-hover:scale-105 transition-transform duration-300"
                                         alt="{{ $movie->title }}">
                                @else
                                    <div class="w-full aspect-[2/3] rounded-xl bg-white/5 flex items-center justify-center">
                                        <i class="bi bi-film text-white/10 text-2xl"></i>
                                    </div>
                                @endif
                                <div class="text-[10px] font-bold text-white/60 truncate group-hover:text-white transition-colors leading-tight">
                                    {{ $movie->title }}
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- Right: Data Quality + Activity --}}
            <div class="space-y-8">

                {{-- Data Quality --}}
                <div class="glass p-8 rounded-[2.5rem] border-white/5">
                    <h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
                        <i class="bi bi-shield-check text-rose-400"></i> Datenqualität
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/5 hover:border-rose-500/30 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-rose-500/20 flex items-center justify-center text-rose-400 shrink-0">
                                    <i class="bi bi-link-45deg text-lg"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-white">{{ $stats['missingTmdbCount'] }}</div>
                                    <div class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Ohne TMDb</div>
                                </div>
                            </div>
                            <a href="{{ route('admin.movies.index', ['filter' => 'missing_tmdb']) }}"
                               class="text-white/20 hover:text-white transition-colors">
                                <i class="bi bi-arrow-right-short text-2xl"></i>
                            </a>
                        </div>
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/5 hover:border-amber-500/30 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                                    <i class="bi bi-image text-lg"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-white">{{ $stats['missingCoverCount'] }}</div>
                                    <div class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Ohne Cover</div>
                                </div>
                            </div>
                            <a href="{{ route('admin.movies.index', ['filter' => 'missing_cover']) }}"
                               class="text-white/20 hover:text-white transition-colors">
                                <i class="bi bi-arrow-right-short text-2xl"></i>
                            </a>
                        </div>
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/5 hover:border-indigo-500/30 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                                    <i class="bi bi-play-btn text-lg"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <div class="text-sm font-black text-white">{{ $stats['missingTrailerCount'] }}</div>
                                        @if($lastStatus = \App\Models\Setting::get('smart_trailer_last_status'))
                                            <i class="bi {{ $lastStatus === 'success' ? 'bi-check-circle-fill text-emerald-500' : 'bi-exclamation-circle-fill text-rose-500' }} text-[10px]"></i>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-white/30 font-bold uppercase tracking-widest">Ohne Trailer</div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.movies.sync-logs') }}"
                                       class="text-white/20 hover:text-white transition-colors" title="Sync-Verlauf">
                                        <i class="bi bi-list-ul text-lg"></i>
                                    </a>
                                    <a href="{{ route('admin.movies.index', ['filter' => 'missing_trailer']) }}"
                                       class="text-white/20 hover:text-white transition-colors">
                                        <i class="bi bi-arrow-right-short text-2xl"></i>
                                    </a>
                                </div>
                                @if($lastRun = \App\Models\Setting::get('smart_trailer_last_run'))
                                    <span class="text-[8px] text-white/20 font-bold uppercase tracking-widest">
                                        {{ \Carbon\Carbon::parse($lastRun)->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Activity --}}
                <div class="glass p-8 rounded-[2.5rem] border-white/5">
                    <h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
                        <i class="bi bi-lightning-charge-fill text-rose-400"></i> Aktivität
                    </h3>
                    <div class="space-y-5 relative">
                        <div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-white/5"></div>
                        @forelse($stats['recentActivity'] as $log)
                            @php
                                $details = json_decode($log->details, true);
                                $actionInfo = match($log->action) {
                                    'MOVIE_IMPORT'    => ['label' => 'Importiert',  'icon' => 'bi-plus-circle-fill',  'color' => 'bg-emerald-500/20 text-emerald-400'],
                                    'MOVIE_UPDATE'    => ['label' => 'Bearbeitet',  'icon' => 'bi-pencil-fill',       'color' => 'bg-rose-500/20 text-rose-400'],
                                    'MOVIE_DELETE'    => ['label' => 'Gelöscht',    'icon' => 'bi-trash-fill',        'color' => 'bg-rose-500/20 text-rose-400'],
                                    'SETTINGS_UPDATE' => ['label' => 'Einstellungen','icon' => 'bi-sliders',          'color' => 'bg-indigo-500/20 text-indigo-400'],
                                    default           => ['label' => $log->action,  'icon' => 'bi-info-circle-fill', 'color' => 'bg-white/10 text-white/50'],
                                };
                            @endphp
                            <div class="relative pl-10 group">
                                <div class="absolute left-0 top-1 w-6 h-6 rounded-lg {{ $actionInfo['color'] }} flex items-center justify-center text-[10px] z-10 shadow-lg shadow-black/20 group-hover:scale-110 transition-transform">
                                    <i class="bi {{ $actionInfo['icon'] }}"></i>
                                </div>
                                <div class="text-[10px] font-black text-white/20 uppercase tracking-widest mb-0.5">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                                <div class="text-xs font-bold text-white/80 group-hover:text-rose-400 transition-colors">
                                    {{ $actionInfo['label'] }}
                                </div>
                                @if(isset($details['title']))
                                    <div class="text-[10px] text-white/30 font-bold truncate italic mt-0.5">
                                        {{ $details['title'] }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-6 text-white/20 text-sm italic">Keine Aktivitäten.</div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-admin-layout>
