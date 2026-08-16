<x-admin-layout>
    @section('header_title', 'Duplikate erkennen')

    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.movies.index') }}" class="flex items-center gap-2 text-xs font-black text-white/40 hover:text-white uppercase tracking-widest transition-colors">
                <i class="bi bi-arrow-left"></i> Zurück zur Filmliste
            </a>
            <span class="px-4 py-2 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-xl text-xs font-black uppercase tracking-widest">
                {{ $duplicateGroups->count() }} Duplikat-Gruppen gefunden
            </span>
        </div>

        @if($duplicateGroups->isEmpty())
            <div class="glass rounded-[3rem] border border-white/5 p-24 flex flex-col items-center gap-4 text-center">
                <div class="w-20 h-20 rounded-3xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-4xl text-emerald-400">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="text-xl font-black text-white">Keine Duplikate gefunden</div>
                    <p class="text-sm text-white/30 font-medium mt-1">Alle Filme haben eindeutige Titel-Jahr-Kombinationen.</p>
                </div>
            </div>
        @else
            <div class="space-y-6">
                @foreach($duplicateGroups as $group)
                    <div class="glass rounded-[2rem] border border-amber-500/20 overflow-hidden">
                        <div class="px-8 py-5 bg-amber-500/5 border-b border-amber-500/10 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 font-black text-sm">
                                    {{ $group->count }}x
                                </div>
                                <div>
                                    <div class="text-base font-black text-white">{{ $group->title }}</div>
                                    <div class="text-xs text-amber-400/70 font-bold uppercase tracking-widest">{{ $group->year }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="divide-y divide-white/5">
                            @foreach($group->movies as $movie)
                                <div class="px-8 py-5 flex items-center gap-6 hover:bg-white/[0.02] transition-colors">
                                    <div class="w-10 h-14 bg-gray-800 rounded-xl overflow-hidden flex-shrink-0 border border-white/10">
                                        @if($movie->cover_url)
                                            <img src="{{ $movie->cover_url }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-white/10"><i class="bi bi-film"></i></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-bold text-white">{{ $movie->title }}</div>
                                        <div class="flex items-center gap-4 mt-1 text-[10px] text-white/30 font-bold uppercase tracking-widest">
                                            <span>ID: {{ $movie->id }}</span>
                                            <span>{{ $movie->collection_type }}</span>
                                            <span>{{ $movie->genre ?: '—' }}</span>
                                            <span>Hinzugefügt: {{ $movie->created_at?->format('d.m.Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.movies.edit', $movie) }}"
                                           class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-white/40 hover:bg-indigo-500 hover:text-white transition-all" title="Bearbeiten">
                                            <i class="bi bi-pencil-square text-sm"></i>
                                        </a>
                                        <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST"
                                              onsubmit="return confirm('Film ID {{ $movie->id }} „{{ $movie->title }}" löschen?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-rose-500/40 hover:bg-rose-500 hover:text-white transition-all" title="Löschen">
                                                <i class="bi bi-trash-fill text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-admin-layout>
