<x-admin-layout>
    @section('header_title', 'Filme verwalten')

    <div class="space-y-8">
        <!-- Top Actions & Search Bar -->
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.movies.create') }}" class="px-8 py-3.5 bg-rose-600 hover:bg-rose-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-rose-500/20 flex items-center gap-3 group">
                    <i class="bi bi-plus-lg text-lg group-hover:scale-125 transition-transform"></i>
                    Neuer Film
                </a>
                <a href="{{ route('admin.tmdb.index') }}" class="px-8 py-3.5 bg-white/5 hover:bg-white/10 text-white/70 rounded-2xl font-black text-xs uppercase tracking-widest border border-white/10 transition-all flex items-center gap-3">
                    <i class="bi bi-cloud-arrow-down-fill text-lg"></i>
                    TMDb Import
                </a>
                <form action="{{ route('admin.movies.smart-trailer') }}" method="POST">
                    @csrf
                    <div class="flex flex-col gap-2">
                        <button type="submit" class="px-8 py-3.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-2xl font-black text-xs uppercase tracking-widest border border-rose-500/20 transition-all flex items-center gap-3">
                            <i class="bi bi-play-circle-fill text-lg"></i>
                            Smart Trailer Sync
                        </button>
                        @if($lastRun = \App\Models\Setting::get('smart_trailer_last_run'))
                            @php
                                $status = \App\Models\Setting::get('smart_trailer_last_status', 'success');
                                $results = json_decode(\App\Models\Setting::get('smart_trailer_last_results', '[]'), true);
                                $error = \App\Models\Setting::get('smart_trailer_last_error');
                            @endphp
                            <div class="flex flex-col px-2">
                                <span class="text-[9px] text-white/20 font-black uppercase tracking-[0.2em] flex items-center gap-2">
                                    @if($status === 'success')
                                        <i class="bi bi-check-circle-fill text-emerald-500"></i>
                                    @else
                                        <i class="bi bi-exclamation-circle-fill text-rose-500" title="{{ $error }}"></i>
                                    @endif
                                    Zuletzt: {{ \Carbon\Carbon::parse($lastRun)->format('d.m.Y H:i') }}
                                </span>
                                @if(isset($results['updated']))
                                    <span class="text-[8px] text-white/10 font-bold uppercase tracking-widest mt-0.5">
                                        {{ $results['updated'] }} Trailer aktualisiert
                                    </span>
                                @endif
                                <a href="{{ route('admin.movies.sync-logs') }}" class="text-[8px] text-rose-500/60 hover:text-rose-400 font-black uppercase tracking-widest mt-1.5 flex items-center gap-1 transition-colors">
                                    <i class="bi bi-list-ul"></i>
                                    Protokoll & Verlauf
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-6 w-full xl:w-auto">
                <form action="{{ route('admin.movies.index') }}" method="GET" class="relative w-full md:w-[30rem] group">
                    <i class="bi bi-search absolute left-6 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-rose-400 transition-colors"></i>
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Filme durchsuchen..." 
                           class="w-full bg-white/5 border border-white/10 rounded-[1.5rem] py-4 pl-14 pr-6 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all">
                    @if(request('filter'))
                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                    @endif
                </form>

                @if(request('filter') || request('q'))
                    <a href="{{ route('admin.movies.index') }}" class="px-4 py-2 bg-rose-500/10 text-rose-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-500/20 transition-all flex items-center gap-2">
                        <i class="bi bi-x-circle"></i>
                        Filter löschen
                    </a>
                @endif
            </div>
        </div>

        @if(request('filter'))
            <div class="p-6 bg-amber-500/5 border border-amber-500/10 rounded-3xl flex items-center gap-4 text-amber-400/80">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-xl">
                    <i class="bi bi-funnel-fill"></i>
                </div>
                <span class="text-xs font-black uppercase tracking-widest">
                    Aktivierter Filter: 
                    <span class="text-white">{{ request('filter') === 'missing_tmdb' ? 'Fehlende TMDb-Verknüpfung' : 'Fehlende Cover' }}</span>
                </span>
            </div>
        @endif

        <!-- Premium Movies Table -->
        <div class="glass rounded-[3rem] border-white/5 shadow-3xl overflow-hidden">
            <div class="overflow-x-auto overflow-y-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/[0.02] border-b border-white/5">
                            <th class="px-10 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em]">Sammlung & Titel</th>
                            <th class="px-8 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em] hidden lg:table-cell">Details</th>
                            <th class="px-8 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em] hidden md:table-cell">Jahr</th>
                            <th class="px-8 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em] text-right">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($movies as $movie)
                            <tr class="hover:bg-white/[0.03] transition-colors group">
                                <td class="px-10 py-6">
                                    <div class="flex items-center gap-6">
                                        <div class="w-14 h-20 bg-gray-800 rounded-2xl overflow-hidden flex-shrink-0 border border-white/10 shadow-2xl relative group-hover:scale-105 transition-transform duration-500">
                                            @if($movie->cover_url)
                                                <img src="{{ $movie->cover_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-700 to-gray-900 text-white/10">
                                                    <i class="bi bi-film"></i>
                                                </div>
                                            @endif
                                            @if(!$movie->cover_id)
                                                <div class="absolute inset-x-0 bottom-0 py-1 bg-amber-500/90 text-black text-[8px] font-black text-center uppercase">No Cover</div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-base font-black text-white group-hover:text-rose-400 transition-colors truncate">
                                                {{ $movie->title }}
                                            </div>
                                            <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                                                <span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded-md text-[9px] font-black uppercase tracking-widest">
                                                    {{ $movie->collection_type }}
                                                </span>
                                                @if($movie->boxsetChildren->count() > 0)
                                                    <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest flex items-center gap-1.5">
                                                        <i class="bi bi-stack"></i>
                                                        {{ $movie->boxsetChildren->count() }} Filme im Set
                                                    </span>
                                                @endif
                                                @if(!$movie->tmdb_id)
                                                    <span class="text-[9px] font-black text-rose-400 uppercase tracking-widest flex items-center gap-1.5">
                                                        <i class="bi bi-link-45deg"></i>
                                                        No TMDb
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 hidden lg:table-cell">
                                    <div class="text-xs text-white/40 font-medium truncate max-w-[15rem] italic">
                                        {{ $movie->genre ?: 'Kein Genre' }}
                                    </div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $movie->is_deleted ? 'bg-rose-500' : 'bg-emerald-500' }}"></div>
                                        <span class="text-[10px] text-white/20 font-black uppercase tracking-widest">Status: {{ $movie->is_deleted ? 'Gesperrt' : 'Aktiv' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 hidden md:table-cell">
                                    <div class="text-lg font-black text-white/60 tracking-wider font-mono">{{ $movie->year ?: '-' }}</div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-3 opacity-20 group-hover:opacity-100 transition-opacity duration-300">
                                        <a href="{{ route('admin.movies.edit', $movie) }}" class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-white/50 hover:bg-rose-500 hover:text-white hover:shadow-lg hover:shadow-rose-500/20 transition-all shadow-rose-500/30" title="Bearbeiten">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST" onsubmit="return confirm('Möchtest du diesen Film wirklich löschen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-rose-500/40 hover:bg-rose-500 hover:text-white hover:shadow-lg hover:shadow-rose-500/20 transition-all shadow-rose-500/30" title="Löschen">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-10 py-24 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-20 h-20 rounded-3xl bg-white/5 flex items-center justify-center text-4xl text-white/10">
                                            <i class="bi bi-search"></i>
                                        </div>
                                        <div>
                                            <div class="text-lg font-black text-white/40">Keine Filme gefunden</div>
                                            <p class="text-sm text-white/20 font-medium">Verändere deinen Filter oder füge neue Filme hinzu.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination -->
            @if($movies->hasPages())
                <div class="px-10 py-8 bg-white/[0.01] border-t border-white/5">
                    {{ $movies->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>