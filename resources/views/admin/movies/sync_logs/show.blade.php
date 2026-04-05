<x-admin-layout>
    @section('header_title', 'Sync Run Details #' . $run->id)

    <div class="max-w-6xl mx-auto space-y-10">
        <!-- Run Summary Card -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="glass p-8 rounded-[2.5rem] border-white/5 shadow-2xl">
                <div class="text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-4">Lauf ID</div>
                <div class="text-3xl font-black text-white">#{{ $run->id }}</div>
            </div>
            <div class="glass p-8 rounded-[2.5rem] border-white/5 shadow-2xl">
                <div class="text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-4">Filme Gesamt</div>
                <div class="text-3xl font-black text-white">{{ $run->total_movies }}</div>
            </div>
            <div class="glass p-8 rounded-[2.5rem] border-white/5 shadow-2xl">
                <div class="text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-4">Aktualisiert</div>
                <div class="text-3xl font-black text-rose-500">{{ $run->updated_movies }}</div>
            </div>
            <div class="glass p-8 rounded-[2.5rem] border-white/5 shadow-2xl">
                <div class="text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-4">Dauer</div>
                <div class="text-xl font-bold text-white/60">
                    @if($run->completed_at)
                        {{ $run->started_at->diffInSeconds($run->completed_at) }} Sek.
                    @else
                        Aktiv...
                    @endif
                </div>
            </div>
        </div>

        <!-- Log Terminal -->
        <div class="glass rounded-[3.5rem] border-white/5 shadow-2xl overflow-hidden bg-[#0a0a0c]">
            <div class="h-16 flex items-center justify-between px-10 bg-white/[0.02] border-b border-white/10">
                <div class="flex items-center gap-4">
                    <div class="flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-rose-500/80 shadow-lg"></div>
                        <div class="w-3 h-3 rounded-full bg-white/10"></div>
                        <div class="w-3 h-3 rounded-full bg-white/10"></div>
                    </div>
                    <h2 class="text-[11px] font-black text-rose-500 uppercase tracking-[0.4em] ml-4">Trailer Sync Terminal Output</h2>
                </div>
                <a href="{{ route('admin.movies.sync-logs') }}" class="text-[10px] font-black text-white/30 hover:text-white uppercase tracking-widest transition-all">
                    <i class="bi bi-arrow-left mr-2"></i> Zurück
                </a>
            </div>

            <div class="p-8 font-mono text-xs md:text-sm text-white/60 leading-relaxed max-h-[70vh] overflow-y-auto custom-scrollbar">
                @forelse($logs as $log)
                    <div class="flex flex-col md:flex-row md:gap-8 py-2 border-b border-white/[0.03] hover:bg-white/[0.02] transition-colors px-4 rounded-lg">
                        <div class="flex gap-4 shrink-0 text-[10px] font-black uppercase tracking-widest">
                            <span class="text-white/20">{{ $log->created_at->format('H:i:s') }}</span>
                            @if($log->status === 'found')
                                <span class="text-emerald-500">[FOUND]</span>
                            @elseif($log->status === 'not_found')
                                <span class="text-white/20">[SKIP]</span>
                            @else
                                <span class="text-rose-500 animate-pulse">[ERROR]</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <span class="text-white font-black mr-4 uppercase text-[11px]">{{ $log->movie_title }}</span>
                            <span class="@if($log->status === 'found') text-emerald-400/80 @else text-white/40 @endif italic text-xs">
                                {{ $log->message }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-white/20 italic tracking-widest text-center py-20">
                        Keine detaillierten Log-Einträge für diesen Lauf gefunden.
                    </div>
                @endforelse
                
                <div class="mt-8 text-[10px] font-black text-rose-500/40 uppercase tracking-[0.5em] text-center italic">
                    --- END OF PROCESS LOG ---
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
