<x-admin-layout>
    @section('header_title', 'System-Update')

    <div class="max-w-4xl mx-auto space-y-8">
        <div class="glass p-10 rounded-[3rem] border-white/5 bg-gradient-to-br from-blue-500/10 to-transparent relative overflow-hidden">
            <div class="absolute top-0 right-0 p-10 opacity-10">
                <i class="bi bi-github text-[12rem]"></i>
            </div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 bg-blue-500/20 rounded-[2rem] flex items-center justify-center text-blue-400 text-4xl mb-8 shadow-2xl shadow-blue-500/20">
                    <i class="bi bi-cloud-arrow-down"></i>
                </div>
                
                @if($needsUpdate)
                    <h1 class="text-4xl font-black text-white mb-2">Update verfügbar</h1>
                    <p class="text-blue-400 font-bold uppercase tracking-[0.2em] text-xs">Ein neuer Release steht bereit</p>
                @else
                    <h1 class="text-4xl font-black text-white mb-2">System Aktuell</h1>
                    <p class="text-white/40 font-bold uppercase tracking-[0.2em] text-xs">Keine Updates verfügbar</p>
                @endif
                
                <div class="grid grid-cols-2 gap-8 mt-12">
                    <div class="space-y-1">
                        <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Installierte Version</div>
                        <div class="text-2xl font-black text-white tracking-tight">{{ $currentBranch }} ({{ $currentCommit }})</div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-[10px] font-black text-white/30 uppercase tracking-widest">Letzte Prüfung</div>
                        <div class="text-2xl font-black text-white tracking-tight">{{ date('d.m.Y H:i') }}</div>
                    </div>
                </div>

                <div class="mt-12 flex gap-4">
                    <form action="{{ route('admin.update.check') }}" method="POST">
                        @csrf
                        <button type="submit" class="glass-button bg-white/5 border-white/10 text-white/60 hover:text-white flex items-center gap-2">
                            <i class="bi bi-arrow-clockwise"></i>
                            Nach Updates suchen
                        </button>
                    </form>

                    @if($needsUpdate)
                        <form action="{{ route('admin.update.run') }}" method="POST">
                            @csrf
                            <button type="submit" class="glass-button bg-blue-600 border-blue-500 text-white hover:bg-blue-500 flex items-center gap-2">
                                <i class="bi bi- lightning-charge-fill"></i>
                                Jetzt Update installieren
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($formattedChanges) && count($formattedChanges) > 0)
        <!-- Release Notes -->
        <div class="glass p-8 rounded-[2.5rem] border-white/5">
            <h3 class="text-xs font-black text-white/40 uppercase tracking-widest mb-6 flex items-center gap-2">
                <i class="bi bi-file-text"></i> Letzte Änderungen (Git Log)
            </h3>
            <div class="space-y-6">
                @foreach($formattedChanges as $change)
                <div class="flex gap-6">
                    <div class="w-16 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center shrink-0 text-white/20 font-mono text-[10px] font-bold">
                        {{ $change['hash'] }}
                    </div>
                    <div class="space-y-1">
                        <div class="text-sm font-bold text-white">{{ $change['msg'] }}</div>
                        <p class="text-[10px] text-white/30 uppercase font-bold tracking-tighter">{{ $change['date'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="p-8 bg-amber-500/10 border border-amber-500/20 rounded-[2rem] flex items-start gap-4">
            <div class="w-10 h-10 bg-amber-500/20 rounded-xl flex items-center justify-center text-amber-500 shrink-0">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-amber-500 uppercase tracking-widest mb-1">Hinweis</h4>
                <p class="text-xs text-amber-500/60 leading-relaxed">Manuelle Updates via GitHub werden für die v2-Version demnächst wieder vollständig automatisiert zur Verfügung stehen. Aktuell empfehlen wir Updates über Git Pull durchzuführen.</p>
            </div>
        </div>
    </div>

    <style>
        .glass-button {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 1rem 2rem;
            border-radius: 1.5rem;
            font-size: 0.875rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            transition: all 0.3s ease;
        }
        .glass-button:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
    </style>
</x-admin-layout>
