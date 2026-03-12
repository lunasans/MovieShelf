<x-admin-layout>
    @section('header_title', 'Schauspieler verwalten')

    <div class="flex flex-col gap-6">
        <!-- Top Actions & Search -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.actors.create') }}" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    Neuer Schauspieler
                </a>
            </div>

            <form action="{{ route('admin.actors.index') }}" method="GET" class="relative w-full md:w-96 group">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-blue-400 transition-colors"></i>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}"
                       placeholder="Schauspieler suchen..." 
                       class="w-full bg-white/5 border border-white/10 rounded-2xl py-2.5 pl-11 pr-4 text-sm text-gray-200 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/20 transition-all">
            </form>
        </div>

        <!-- Actors Table -->
        <div class="glass rounded-3xl overflow-hidden border border-white/5 shadow-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/5 border-b border-white/5">
                        <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Foto</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Name</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest hidden md:table-cell">Nationalität</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest text-center hidden lg:table-cell">Filme</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest text-right">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($actors as $actor)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-6 py-4">
                                <div class="w-10 h-10 rounded-full bg-gray-800 border border-white/5 overflow-hidden flex items-center justify-center">
                                    @if($actor->photo_path)
                                        <img src="/{{ $actor->photo_path }}" alt="{{ $actor->first_name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="bi bi-person text-gray-600"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-white">{{ $actor->first_name }} {{ $actor->last_name }}</div>
                                <div class="text-[10px] text-gray-500 uppercase mt-0.5">{{ $actor->slug }}</div>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell">
                                <span class="text-xs text-gray-400 font-medium">{{ $actor->nationality ?: '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center hidden lg:table-cell">
                                <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-md text-[10px] font-bold">
                                    {{ $actor->movies_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.actors.edit', $actor) }}" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-gray-400 hover:bg-blue-500 hover:text-white transition-all" title="Bearbeiten">
                                        <i class="bi bi-pencil-square text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.actors.destroy', $actor) }}" method="POST" onsubmit="return confirm('Soll dieser Schauspieler wirklich gelöscht werden?')">
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
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic text-sm">
                                Keine Schauspieler gefunden.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($actors->hasPages())
                <div class="px-6 py-4 bg-white/5 border-t border-white/5">
                    {{ $actors->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</x-admin-layout>
