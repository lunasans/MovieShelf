<x-admin-layout>
    <div class="p-6 md:p-12 max-w-7xl mx-auto">
        <div class="mb-12 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">Legacy XML Import</h1>
                <p class="text-rose-500/60 mt-2 uppercase text-[10px] font-black tracking-[0.3em]">DVD Profiler Datensynchronisation</p>
            </div>
            <a href="{{ route('admin.movies.index') }}" class="glass px-8 py-4 rounded-2xl flex items-center gap-3 group text-xs font-black uppercase tracking-widest text-white/40 hover:text-white transition-all border-white/10 hover:border-rose-500/30">
                <i class="bi bi-arrow-left transition-transform group-hover:-translate-x-1 text-lg"></i>
                Zurück zur Liste
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Upload Section -->
            <div class="space-y-8">
                <div class="glass p-10 rounded-[3.5rem] border-white/5 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                    
                    <h2 class="text-lg font-black text-white mb-8 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-500">
                            <i class="bi bi-upload"></i>
                        </div>
                        XML / ZIP Hochladen
                    </h2>

                    <form action="{{ route('admin.import.post') }}" method="POST" enctype="multipart/form-data" class="group relative border-2 border-dashed border-white/10 rounded-[2.5rem] p-12 text-center hover:border-rose-500/50 hover:bg-rose-500/5 transition-all cursor-pointer">
                        @csrf
                        <input type="file" name="xml_file" id="xml_file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="this.form.submit()">
                        <div class="space-y-6">
                            <div class="w-20 h-20 bg-rose-500/10 rounded-3xl flex items-center justify-center mx-auto group-hover:scale-110 transition-transform shadow-xl shadow-rose-500/5">
                                <i class="bi bi-file-earmark-code text-4xl text-rose-500"></i>
                            </div>
                            <div>
                                <p class="text-lg text-white font-black tracking-tight">Datei auswählen</p>
                                <p class="text-white/20 text-[10px] mt-2 uppercase tracking-[0.2em] font-black italic">Oder via Drag & Drop (Max. 50MB)</p>
                            </div>
                        </div>
                    </form>

                    <div class="mt-10 p-8 bg-black/20 rounded-[2rem] border border-white/5">
                        <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
                            <i class="bi bi-info-circle-fill text-rose-500/40"></i> Wichtige Hinweise
                        </h3>
                        <ul class="text-xs text-white/40 space-y-4 font-medium italic">
                            <li class="flex gap-4">
                                <i class="bi bi-shield-check text-rose-500"></i>
                                <span>Exportiere deine Sammlung aus DVD Profiler als XML (UTF-8 empfohlen).</span>
                            </li>
                            <li class="flex gap-4">
                                <i class="bi bi-layers-half text-rose-500"></i>
                                <span>BoxSets werden automatisch anhand der Parent-IDs verschachtelt.</span>
                            </li>
                            <li class="flex gap-4">
                                <i class="bi bi-arrow-repeat text-rose-500"></i>
                                <span>Bestehende Filme (gleiche ID) werden mit neuen Metadaten überschrieben.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- History Section -->
            <div class="space-y-8">
                <div class="glass p-10 rounded-[3.5rem] border-white/5 relative overflow-hidden">
                    <h2 class="text-lg font-black text-white mb-8 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-500">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        Bereitgestellte Dateien
                    </h2>

                    @if(count($xmlFiles) > 0)
                        <div class="space-y-4">
                            @foreach($xmlFiles as $file)
                                <div class="p-6 rounded-[1.5rem] bg-white/[0.02] border border-white/5 flex items-center justify-between group hover:border-rose-500/30 transition-all">
                                    <div class="flex items-center gap-5">
                                        <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform">
                                            <i class="bi bi-filetype-xml text-rose-500/40 text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-white tracking-wide mb-1">{{ $file['name'] }}</p>
                                            <p class="text-[9px] text-white/20 uppercase tracking-[0.2em] font-black">
                                                {{ date('d. M Y • H:i', $file['modified']) }} <span class="mx-2 text-rose-500/20">|</span> {{ number_format($file['size'] / 1024 / 1024, 2) }} MB
                                            </p>
                                        </div>
                                    </div>
                                    <form action="{{ route('admin.import.destroy', $file['name']) }}" method="POST" onsubmit="return confirm('Datei wirklich löschen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-10 h-10 flex items-center justify-center text-white/20 hover:text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all">
                                            <i class="bi bi-trash3 text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-20 bg-black/10 rounded-[2rem] border border-dashed border-white/5">
                            <i class="bi bi-folder2-open text-5xl text-white/5 mb-6 block"></i>
                            <p class="text-white/20 text-xs font-black uppercase tracking-widest italic">Keine Dateien im Cache-Verzeichnis</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>