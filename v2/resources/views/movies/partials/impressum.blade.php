<div class="h-full flex flex-col transition-all duration-500 animate-in fade-in slide-in-from-right-10">
    <!-- Header Area -->
    <div class="p-8 pb-0 flex items-center justify-between pointer-events-none">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20 shadow-xl">
                <i class="bi bi-info-circle text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-white uppercase tracking-tighter leading-none">Impres<span class="text-blue-500">sum</span></h2>
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-1 italic">{{ __('Rechtliche Hinweise') }}</p>
            </div>
        </div>
        <button @click="selectedMovie = null; isStatsView = false" class="p-3 rounded-2xl bg-white/5 text-gray-400 hover:text-white hover:bg-white/10 transition-all pointer-events-auto">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Content Area -->
    <div class="flex-1 overflow-y-auto p-8 no-scrollbar space-y-8">
        <!-- Main Info -->
        <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                <i class="bi bi-person-badge text-8xl"></i>
            </div>

            <div class="space-y-6 relative z-10">
                <div>
                    <span class="text-[9px] font-black text-gray-500 uppercase tracking-[0.2em] block mb-1 italic">Betreiber / Verantwortlich</span>
                    <p class="text-xl font-black text-white leading-tight">{{ $name }}</p>
                </div>

                @if($email)
                    <div>
                        <span class="text-[9px] font-black text-gray-500 uppercase tracking-[0.2em] block mb-1 italic">Kontakt</span>
                        <a href="mailto:{{ $email }}" class="text-lg font-bold text-blue-400 hover:text-blue-300 transition-colors italic">
                            {{ $email }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        @if($content)
            <div class="glass p-8 rounded-[2rem] border border-white/10 shadow-xl">
                <h3 class="text-[9px] font-black text-gray-500 uppercase tracking-[0.2em] mb-4 italic flex items-center gap-4">
                    Weitere Angaben
                    <div class="h-[1px] bg-white/10 flex-1"></div>
                </h3>
                <div class="prose prose-invert prose-sm text-gray-400 italic leading-relaxed">
                    {!! $content !!}
                </div>
            </div>
        @endif

        <!-- Legal Blocks -->
        <div class="grid grid-cols-1 gap-4">
            <div class="glass p-6 rounded-2xl border border-white/10 hover:bg-white/[0.02] transition-colors">
                <h4 class="text-[10px] font-black text-white uppercase tracking-widest mb-2 flex items-center gap-2">
                    <i class="bi bi-shield-lock text-blue-500"></i>
                    Haftungsausschluss
                </h4>
                <p class="text-[11px] text-gray-500 italic leading-relaxed">
                    Diese Seite ist ein rein privates Projekt. Für die Richtigkeit der Inhalte wird keine Gewähr übernommen.
                </p>
            </div>
            
            <div class="glass p-6 rounded-2xl border border-white/10 hover:bg-white/[0.02] transition-colors">
                <h4 class="text-[10px] font-black text-white uppercase tracking-widest mb-2 flex items-center gap-2">
                    <i class="bi bi-c-circle text-blue-500"></i>
                    Urheberrecht
                </h4>
                <p class="text-[11px] text-gray-500 italic leading-relaxed">
                    Inhalte dieser Website unterliegen dem Urheberrecht. Nutzung nur mit Zustimmung.
                </p>
            </div>
        </div>

        <div class="text-center pt-8 opacity-30">
            <p class="text-[8px] font-black text-gray-500 uppercase tracking-[0.4em]">
                Build {{ date('Y') }} &bull; MovieShelf Laravel v2
            </p>
        </div>
    </div>
</div>
