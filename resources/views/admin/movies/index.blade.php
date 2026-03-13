<x-admin-layout>
    @section('header_title', 'Filme verwalten')

    <div class="flex flex-col gap-6">
        <!-- Top Actions & Search -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.movies.create') }}" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    Neuer Film
                </a>
                <a href="{{ route('admin.tmdb.index') }}" class="px-6 py-2.5 bg-white/5 hover:bg-white/10 text-gray-300 rounded-xl font-bold text-sm border border-white/10 transition-all flex items-center gap-2">
                    <i class="bi bi-download"></i>
                    TMDb Import
                </a>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                <form action="{{ route('admin.movies.index') }}" method="GET" class="relative w-full md:w-96 group">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-blue-400 transition-colors"></i>
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}"
                           placeholder="Filme suchen..." 
                           class="w-full bg-white/5 border border-white/10 rounded-2xl py-2.5 pl-11 pr-4 text-sm text-gray-200 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/20 transition-all">
                    @if(request('filter'))
                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                    @endif
                </form>

                @if(request('filter') || request('q'))
                    <a href="{{ route('admin.movies.index') }}" class="text-xs font-black text-rose-500 uppercase tracking-widest hover:text-rose-400 transition-colors flex items-center gap-1">
                        <i class="bi bi-x-circle"></i>
                        Filter löschen
                    </a>
                @endif
            </div>
        </div>

        @if(request('filter') === 'missing_tmdb')
            <div class="p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl flex items-center gap-3 text-rose-400">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span class="text-xs font-bold font-black uppercase tracking-widest">Filter aktiv: Fehlende TMDb-Verknüpfung</span>
            </div>
        @elseif(request('filter') === 'missing_cover')
            <div class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center gap-3 text-amber-400">
                <i class="bi bi-image"></i>
                <span class="text-xs font-bold font-black uppercase tracking-widest">Filter aktiv: Fehlende Cover</span>
            </div>
        @endif

        <!-- Movies Table -->
        <div class="glass rounded-3xl overflow-hidden border border-white/5 shadow-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/5 border-b border-white/5">
                        <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Film</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest hidden lg:table-cell">Typ</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest hidden md:table-cell">Jahr</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest text-right">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($movies as $movie)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-14 bg-gray-800 rounded-lg overflow-hidden flex-shrink-0 border border-white/5 shadow-md flex items-center justify-center relative">
                                        @if($movie->cover_url)
                                            <img src="{{ $movie->cover_url }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <i class="bi bi-film text-gray-700"></i>
                                        @endif
                                    </div>
                                     <div class="min-w-0">
                                        <div class="text-sm font-bold text-white truncate flex items-center gap-2">
                                            {{ $movie->title }}
                                            @if(!$movie->tmdb_id)
                                                <i class="bi bi-link-45deg text-rose-500" title="Keine TMDb Verknüpfung"></i>
                                            @endif
                                            @if(!$movie->cover_id)
                                                <i class="bi bi-image text-amber-500" title="Kein Cover"></i>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-gray-500 uppercase mt-0.5 truncate">{{ $movie->boxsetChildren->count() > 0 ? $movie->boxsetChildren->count() . ' Filme im Set' : 'Einzelfilm' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 hidden lg:table-cell">
                                <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-md text-[9px] font-bold uppercase tracking-wider">
                                    {{ $movie->collection_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell">
                                <span class="text-xs text-gray-400 font-medium">{{ $movie->year }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.movies.edit', $movie) }}" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-gray-400 hover:bg-blue-500 hover:text-white transition-all" title="Bearbeiten">
                                        <i class="bi bi-pencil-square text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST" onsubmit="return confirm('Soll dieser Film wirklich gelöscht werden?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-red-400/50 hover:bg-red-500 hover:text-white transition-all" title="Löschen">
                                            <i class="bi bi-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic text-sm">
                                Keine Filme gefunden.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($movies->hasPages())
                <div class="px-6 py-4 bg-white/5 border-t border-white/5">
                    {{ $movies->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
