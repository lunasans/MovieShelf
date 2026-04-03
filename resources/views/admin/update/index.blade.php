<x-admin-layout>
    @section('header_title', 'System-Update')
    <div class="max-w-4xl mx-auto space-y-10">
        <div class="glass p-12 rounded-[3.5rem] border-white/5 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-600/10 to-transparent pointer-events-none"></div>
            <div class="absolute top-0 right-0 p-12 opacity-5">
                <i class="bi bi-github text-[14rem]"></i>
            </div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 bg-rose-600/20 rounded-[2rem] flex items-center justify-center text-rose-500 text-4xl mb-10 shadow-2xl shadow-rose-600/30 ring-2 ring-white/10">
                    <i class="bi bi-cloud-arrow-down-fill"></i>
                </div>

                @if(isset($error))
                <div class="mb-8 p-6 bg-rose-500/10 border border-rose-500/20 rounded-[1.5rem] text-rose-400 text-xs flex gap-4 items-center">
                    <i class="bi bi-exclamation-octagon-fill text-2xl"></i>
                    <div>
                        <div class="font-black uppercase tracking-[0.2em] mb-1">Git Runtime Error</div>
                        <span class="font-medium italic opacity-80">{{ $error }}</span>
                    </div>
                </div>
                @endif

                @if($needsUpdate)
                    <h1 class="text-4xl font-black text-white tracking-tight uppercase mb-2">Update Verfügbar</h1>
                    <p class="text-rose-500 font-black uppercase tracking-[0.3em] text-[10px]">Ein neuer Release steht zur Installation bereit</p>
                @else
                    <h1 class="text-4xl font-black text-white tracking-tight uppercase mb-2">System Aktuell</h1>
                    <p class="text-white/20 font-black uppercase tracking-[0.3em] text-[10px]">Keine ausstehenden Aktualisierungen</p>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mt-12 pt-10 border-t border-white/10">
                    <div class="space-y-1">
                        <div class="text-[10px] font-black text-white/20 uppercase tracking-[0.2em]">Build-Version</div>
                        <div class="text-2xl font-black text-white tracking-tight">
                            v{{ config('app.version') }} 
                            <span class="text-rose-500/40 text-xs ml-2 font-mono">({{ $currentBranch }} @ {{ $currentCommit }})</span>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-[10px] font-black text-white/20 uppercase tracking-[0.2em]">Letzter Check</div>
                        <div class="text-2xl font-black text-white tracking-tight">{{ date('d. M Y') }} <span class="text-white/20 text-xs ml-2">{{ date('H:i') }}</span></div>
                    </div>
                </div>

                @if(!empty($formattedChanges))
                <div class="mt-12 pt-10 border-t border-white/10">
                    <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.2em] mb-6">Letzte Änderungen</h3>
                    <div class="space-y-4">
                        @foreach($formattedChanges as $change)
                        <div class="flex items-center gap-4 group">
                            <div class="font-mono text-[10px] text-rose-500/50 bg-rose-500/5 px-2 py-1 rounded-lg border border-rose-500/10">{{ $change['hash'] }}</div>
                            <div class="text-xs text-white/60 font-medium tracking-tight group-hover:text-white transition-colors capitalize">{{ $change['msg'] }}</div>
                            <div class="ml-auto text-[10px] text-white/10 font-bold uppercase tracking-widest whitespace-nowrap">{{ $change['date'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-12 flex flex-wrap gap-4">
                    <form action="{{ route('admin.update.check') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-8 py-4 bg-white/5 hover:bg-white/10 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition-all border border-white/10 flex items-center gap-3">
                            <i class="bi bi-arrow-clockwise text-base"></i>
                            Prüfen
                        </button>
                    </form>
                    @if($needsUpdate)
                    <form action="{{ route('admin.update.run') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-10 py-4 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.3em] transition-all shadow-xl shadow-rose-600/30 flex items-center gap-4 transform hover:scale-[1.03] active:scale-[0.98]">
                            <i class="bi bi-lightning-charge-fill text-lg"></i>
                            Update jetzt ausführen
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Update Settings -->
        <div class="glass p-10 rounded-[3.5rem] border-white/5 relative overflow-hidden">
            <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] mb-8 flex items-center gap-3 px-1">
                <i class="bi bi-shield-lock-fill text-rose-500/40"></i> Dateischutz & Persistence
            </h3>
            <form action="{{ route('admin.update.settings.save') }}" method="POST" class="space-y-8">
                @csrf
                <div class="space-y-3">
                    <label for="ignored_update_files" class="block text-[10px] font-black text-white/30 uppercase tracking-[0.2em] mb-3 px-2">Zu schützende Pfade (Eigene Anpassungen)</label>
                    <textarea name="ignored_update_files" id="ignored_update_files" rows="4"
                        placeholder=".env&#10;resources/css/custom.css"
                        class="w-full bg-black/20 border border-white/10 rounded-[2rem] py-6 px-8 text-white focus:outline-none focus:border-rose-500/40 transition-all font-mono text-[11px] leading-relaxed shadow-inner">{{ old('ignored_update_files', $ignoredUpdateFiles ?? '') }}</textarea>
                    
                    <div class="p-6 bg-white/[0.02] rounded-3xl border border-white/5 flex items-start gap-4 mt-6">
                        <i class="bi bi-info-circle-fill text-rose-500/40 text-lg mt-0.5"></i>
                        <p class="text-xs text-white/30 leading-relaxed font-medium italic">
                            Hier gelistete Pfade werden vor der Aktualisierung temporär gesichert und im Anschluss wiederhergestellt. 
                            Ideal für die <span class="text-rose-400/60 font-black">.env</span> oder Code-Anpassungen, die nicht überschrieben werden dürfen. 
                            <span class="block mt-2">Nutze relative Pfade, einen pro Zeile.</span>
                        </p>
                    </div>
                </div>
                <div class="flex justify-end pt-4">
                    <button type="submit" class="px-10 py-4 bg-white/5 hover:bg-white/10 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition-all border border-white/10 flex items-center gap-3 shadow-lg transform hover:scale-[1.02] active:scale-[0.98]">
                        <i class="bi bi-save2 text-rose-500"></i>
                        Schutzliste sichern
                    </button>
                </div>
            </form>
        </div>

        <!-- Release Notes placeholder / info -->
        <div class="p-10 bg-amber-500/5 border border-amber-500/10 rounded-[3rem] flex items-center gap-6">
            <div class="w-14 h-14 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-500/60 shrink-0 shadow-lg shadow-amber-500/5">
                <i class="bi bi-git text-2xl"></i>
            </div>
            <div>
                <h4 class="text-[10px] font-black text-amber-500/60 uppercase tracking-[0.2em] mb-1">Git-Automation</h4>
                <p class="text-xs text-white/30 font-medium italic leading-relaxed">Systeminterne Updates via GitHub werden für die v2-Architektur fortlaufend optimiert. Wir empfehlen manuelle Bestätigungen für Produktionssysteme.</p>
            </div>
        </div>
    </div>
</x-admin-layout>