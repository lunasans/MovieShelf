<x-admin-layout>
    @section('header_title', 'Stars verwalten')

    <div class="space-y-8">
        <!-- Top Actions & Search Bar -->
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.actors.create') }}" class="px-8 py-3.5 bg-rose-600 hover:bg-rose-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-rose-500/20 flex items-center gap-3 group">
                    <i class="bi bi-person-plus-fill text-lg group-hover:scale-125 transition-transform"></i>
                    Neuer Star
                </a>
            </div>

            <form action="{{ route('admin.actors.index') }}" method="GET" class="relative w-full md:w-[30rem] group">
                <i class="bi bi-search absolute left-6 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-rose-400 transition-colors"></i>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Stars suchen (Name)..." 
                       class="w-full bg-white/5 border border-white/10 rounded-[1.5rem] py-4 pl-14 pr-6 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-rose-500/50 focus:ring-4 focus:ring-rose-500/10 transition-all">
            </form>
        </div>

        <!-- Premium Actors Table -->
        <div class="glass rounded-[3rem] border-white/5 shadow-3xl overflow-hidden">
            <div class="overflow-x-auto overflow-y-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/[0.02] border-b border-white/5">
                            <th class="px-10 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em]">Profil</th>
                            <th class="px-8 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em] hidden md:table-cell">Nationalität</th>
                            <th class="px-8 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em] text-center hidden lg:table-cell">Filmografie</th>
                            <th class="px-8 py-6 text-[10px] font-black text-white/30 uppercase tracking-[0.3em] text-right">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($actors as $actor)
                            <tr class="hover:bg-white/[0.03] transition-colors group">
                                <td class="px-10 py-6">
                                    <div class="flex items-center gap-6">
                                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-700 to-gray-900 overflow-hidden border-2 border-white/10 shadow-xl group-hover:scale-110 group-hover:border-rose-500/40 transition-all duration-500 flex items-center justify-center relative">
                                            @if($actor->profile_url)
                                                <img src="{{ $actor->profile_url }}" alt="{{ $actor->first_name }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="bi bi-person text-white/10 text-2xl"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-base font-black text-white group-hover:text-rose-400 transition-colors truncate">
                                                {{ $actor->first_name }} {{ $actor->last_name }}
                                            </div>
                                            <div class="text-[10px] text-white/20 font-black uppercase tracking-widest mt-1 italic">
                                                {{ $actor->slug }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 hidden md:table-cell">
                                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/5 border border-white/5 rounded-full">
                                        <i class="bi bi-geo-alt text-rose-400/60 text-[10px]"></i>
                                        <span class="text-[10px] text-white/70 font-black uppercase tracking-widest">{{ $actor->nationality ?: 'Unbekannt' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center hidden lg:table-cell">
                                    <div class="flex flex-col items-center">
                                        <div class="text-xl font-black text-white/80">{{ $actor->movies_count }}</div>
                                        <div class="text-[9px] font-black text-white/20 uppercase tracking-widest">Filme</div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-3 opacity-20 group-hover:opacity-100 transition-opacity duration-300">
                                        <a href="{{ route('admin.actors.edit', $actor) }}" class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-white/50 hover:bg-rose-500 hover:text-white hover:shadow-lg hover:shadow-rose-500/20 transition-all shadow-rose-500/30" title="Bearbeiten">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.actors.destroy', $actor) }}" method="POST" onsubmit="return confirm('Soll dieser Star wirklich aus dem Archiv gelöscht werden?')">
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
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <div>
                                            <div class="text-lg font-black text-white/40">Keine Stars gefunden</div>
                                            <p class="text-sm text-white/20 font-medium">Verändere deine Suche oder füge neue Schauspieler hinzu.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination -->
            @if($actors->hasPages())
                <div class="px-10 py-8 bg-white/[0.01] border-t border-white/5">
                    {{ $actors->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>