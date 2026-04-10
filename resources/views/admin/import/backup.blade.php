<x-admin-layout>
    @section('header_title', 'Full Backup Import')

    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="mb-12 flex flex-col md:flex-row md:items-center justify-between gap-8 animate-in fade-in slide-in-from-top-4 duration-700">
            <div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">Backup Import</h1>
                <p class="text-emerald-500/60 mt-2 uppercase text-[10px] font-black tracking-[0.3em]">Full System Synchronization</p>
            </div>
            <a href="{{ route('admin.settings.index') }}" class="glass px-8 py-4 rounded-2xl flex items-center gap-3 group text-xs font-black uppercase tracking-widest text-white/40 hover:text-white transition-all border-white/10 hover:border-emerald-500/30">
                <i class="bi bi-arrow-left transition-transform group-hover:-translate-x-1 text-lg"></i>
                Zurück zu Einstellungen
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- 1. Browser Upload (For smaller files) -->
            <div class="space-y-8 animate-in fade-in slide-in-from-left-4 duration-700 delay-100">
                <div class="glass p-10 rounded-[3.5rem] border-white/5 relative overflow-hidden h-full flex flex-col">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/5 to-transparent pointer-events-none"></div>
                    
                    <h2 class="text-lg font-black text-white mb-8 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                            <i class="bi bi-upload"></i>
                        </div>
                        Direkter Browser-Upload
                    </h2>

                    <form action="{{ route('admin.import.backup.upload') }}" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col">
                        @csrf
                        <div class="group relative border-2 border-dashed border-emerald-500/10 rounded-[2.5rem] p-12 text-center hover:border-emerald-500/50 hover:bg-emerald-500/5 transition-all cursor-pointer flex-1 flex flex-col justify-center">
                            <input type="file" name="backup_file" id="backup_file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="this.form.submit()">
                            <div class="space-y-6">
                                <div class="w-20 h-20 bg-emerald-500/10 rounded-3xl flex items-center justify-center mx-auto group-hover:scale-110 transition-transform shadow-xl shadow-emerald-500/5">
                                    <i class="bi bi-file-earmark-zip text-4xl text-emerald-500"></i>
                                </div>
                                <div>
                                    <p class="text-lg text-white font-black tracking-tight">ZIP-Datei wählen</p>
                                    <p class="text-white/20 text-[10px] mt-2 uppercase tracking-[0.2em] font-black italic">Max. 500MB (Cloudflare Limit 100MB beachten)</p>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="mt-8 p-6 bg-emerald-500/5 rounded-[1.5rem] border border-emerald-500/10">
                        <p class="text-[10px] text-emerald-400 font-black uppercase tracking-widest mb-2">Hinweis</p>
                        <p class="text-[10px] text-white/40 leading-relaxed font-medium">Dies funktioniert nur, wenn Cloudflare oder dein Proxy den Upload zulassen. Für größere Dateien nutze bitte die FTP-Option rechts.</p>
                    </div>
                </div>
            </div>

            <!-- 2. Server-Side Scan (FTP Uploads) -->
            <div class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-700 delay-200">
                <div class="glass p-10 rounded-[3.5rem] border-white/5 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                    
                    <h2 class="text-lg font-black text-white mb-2 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400">
                            <i class="bi bi-server"></i>
                        </div>
                        Dateien auf dem Server (FTP)
                    </h2>
                    <p class="text-[10px] text-white/20 font-black uppercase tracking-widest mb-8 ml-14 italic">Verzeichnis: storage/app/backups/import/</p>

                    <div class="space-y-4">
                        @forelse($zipFiles as $file)
                            <div class="p-6 rounded-[2rem] bg-white/[0.02] border border-white/5 group hover:border-emerald-500/30 transition-all flex items-center justify-between">
                                <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                                        <i class="bi bi-archive-fill text-emerald-500/50 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-white tracking-wide mb-1">{{ $file['name'] }}</p>
                                        <p class="text-[9px] text-white/20 uppercase tracking-[0.2em] font-black">
                                            {{ date('d.m.Y H:i', $file['modified']) }} <span class="mx-2 text-emerald-500/20">|</span> {{ number_format($file['size'] / 1024 / 1024, 2) }} MB
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('admin.import.backup.destroy', $file['name']) }}" method="POST" onsubmit="return confirm('Datei löschen?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-10 h-10 flex items-center justify-center text-white/10 hover:text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all">
                                            <i class="bi bi-trash3 text-sm"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.import.backup.local') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="filename" value="{{ $file['name'] }}">
                                        <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-[10px] uppercase tracking-widest transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-3">
                                            <i class="bi bi-play-fill text-lg"></i>
                                            Importieren
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-20 bg-black/10 rounded-[2.5rem] border border-dashed border-white/5">
                                <i class="bi bi-cloud-slash text-5xl text-white/5 mb-6 block"></i>
                                <p class="text-white/20 text-[10px] font-black uppercase tracking-widest italic">Keine Backup-Dateien im Import-Ordner</p>
                                <p class="text-[9px] text-white/10 mt-2">Lade deine Datei via FTP nach <code>storage/app/backups/import/</code></p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Global Information -->
            <div class="lg:col-span-2">
                <div class="glass p-10 rounded-[3.5rem] border-white/5 bg-gradient-to-r from-emerald-600/5 to-transparent">
                    <h3 class="text-lg font-black text-white mb-6 uppercase tracking-tight">Sicherheits-Informationen</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-emerald-500">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">Achtung: Überschreiben</span>
                            </div>
                            <p class="text-xs text-white/30 leading-relaxed font-medium">Ein Import löscht deine **aktive Filmsammlung** inklusive aller Metadaten und Schauspieler in diesem Account.</p>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-emerald-500">
                                <i class="bi bi-images"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">Medien-Relocation</span>
                            </div>
                            <p class="text-xs text-white/30 leading-relaxed font-medium">Alle Bilder (Covers, Backdrops, Schauspieler) werden automatisch in deinen Mandanten-Speicher verschoben.</p>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-emerald-500">
                                <i class="bi bi-database-fill-check"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">Kompatibilität</span>
                            </div>
                            <p class="text-xs text-white/30 leading-relaxed font-medium">Unterstützt nur MovieShelf-ZIP-Backup Dateien ab Version 2.11.0. Frühere Versionen sind nicht kompatibel.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
