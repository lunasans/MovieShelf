<x-admin-layout>
    @section('header_title', 'Trailer Sync History')

    <div class="max-w-6xl mx-auto space-y-10">
        <div class="glass p-10 rounded-[3.5rem] border-white/5 shadow-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
            
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-2xl font-black text-white tracking-tight uppercase flex items-center gap-4">
                    <i class="bi bi-clock-history text-rose-500"></i>
                    Sync-Verlauf
                </h2>
                <div class="flex items-center gap-4">
                    <form action="{{ route('admin.movies.smart-trailer') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-6 py-3 bg-rose-600 hover:bg-rose-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-rose-600/20">
                            <i class="bi bi-play-fill mr-2"></i> Sync Jetzt Starten
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm text-white/60">
                    <thead>
                        <tr class="text-[10px] uppercase font-black text-white/30 tracking-[0.2em] border-b border-white/5">
                            <th class="px-6 py-5">Datum / Uhrzeit</th>
                            <th class="px-6 py-5">Status</th>
                            <th class="px-6 py-5">Filme Gesamt</th>
                            <th class="px-6 py-5">Aktualisiert</th>
                            <th class="px-6 py-5 text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.03]">
                        @forelse($runs as $run)
                            <tr class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-6 font-bold">
                                    {{ $run->started_at?->format('d. M Y') ?? 'Unbekannt' }} 
                                    <span class="text-white/20 ml-2 text-xs font-medium">{{ $run->started_at?->format('H:i') }}</span>
                                </td>
                                <td class="px-6 py-6">
                                    @if($run->status === 'success')
                                        <span class="text-emerald-400 font-black text-[9px] uppercase tracking-widest bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-500/20">Erfolgreich</span>
                                    @elseif($run->status === 'error')
                                        <span class="text-rose-400 font-black text-[9px] uppercase tracking-widest bg-rose-500/10 px-3 py-1 rounded-full border border-rose-500/20">Fehler</span>
                                    @else
                                        <span class="text-rose-400 font-black text-[9px] uppercase tracking-widest flex items-center gap-2">
                                            <i class="bi bi-arrow-repeat animate-spin"></i> Aktiv
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-6 font-mono text-xs">{{ $run->total_movies }}</td>
                                <td class="px-6 py-6 font-mono text-xs text-rose-500 font-bold">+{{ $run->updated_movies }}</td>
                                <td class="px-6 py-6 text-right">
                                    <a href="{{ route('admin.movies.sync-logs.show', $run) }}" class="px-5 py-2 bg-white/5 hover:bg-rose-600 text-white/40 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border border-white/10 hover:border-rose-500/30">
                                        <i class="bi bi-list-ul mr-2"></i> Log ansehen
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-white/20 italic font-medium uppercase tracking-widest text-[10px]">
                                    Keine bisherigen Aufzeichnungen gefunden
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-10">
                {{ $runs->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
