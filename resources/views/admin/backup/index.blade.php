<x-admin-layout>
    @section('header_title', 'Backup')

    <div class="max-w-7xl mx-auto" x-data="{ isCreating: false, isRestoring: false }">

        <!-- Header -->
        <div class="mb-12 flex flex-col md:flex-row md:items-center justify-between gap-8 animate-in fade-in slide-in-from-top-4 duration-700">
            <div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">Backup</h1>
                <p class="text-rose-500/60 mt-2 uppercase text-[10px] font-black tracking-[0.3em]">MovieShelf .ms Sicherungsdateien</p>
            </div>
            <a href="{{ route('admin.settings.index') }}" class="glass px-8 py-4 rounded-2xl flex items-center gap-3 group text-xs font-black uppercase tracking-widest text-white/40 hover:text-white transition-all border-white/10 hover:border-rose-500/30">
                <i class="bi bi-arrow-left transition-transform group-hover:-translate-x-1 text-lg"></i>
                Zurück zu Einstellungen
            </a>
        </div>

        @if(session('success'))
            <div class="mb-8 p-6 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl flex items-center gap-4 animate-in fade-in duration-500">
                <i class="bi bi-check-circle-fill text-2xl text-emerald-400"></i>
                <p class="text-emerald-300 font-bold text-sm">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 p-6 bg-rose-500/10 border border-rose-500/30 rounded-2xl flex items-center gap-4 animate-in fade-in duration-500">
                <i class="bi bi-exclamation-triangle-fill text-2xl text-rose-400"></i>
                <p class="text-rose-300 font-bold text-sm">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

            <!-- Backup erstellen -->
            <div class="animate-in fade-in slide-in-from-left-4 duration-700 delay-100">
                <div class="glass p-10 rounded-[3.5rem] border-white/5 relative overflow-hidden h-full flex flex-col">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>

                    <h2 class="text-lg font-black text-white mb-2 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-500">
                            <i class="bi bi-archive-fill"></i>
                        </div>
                        Backup erstellen
                    </h2>
                    <p class="text-[10px] text-white/20 font-black uppercase tracking-widest mb-8 ml-14 italic">Exportiert Datenbank + Medien als .ms-Datei</p>

                    <div class="flex-1 flex flex-col justify-between gap-8">
                        <div class="space-y-4">
                            <div class="p-5 bg-white/[0.03] rounded-2xl border border-white/5 flex items-center gap-4">
                                <i class="bi bi-database-fill text-rose-500/60 text-xl shrink-0"></i>
                                <div>
                                    <p class="text-xs font-black text-white">Datenbank</p>
                                    <p class="text-[10px] text-white/30">Filme, Schauspieler, Einstellungen, Listen</p>
                                </div>
                            </div>
                            <div class="p-5 bg-white/[0.03] rounded-2xl border border-white/5 flex items-center gap-4">
                                <i class="bi bi-images text-rose-500/60 text-xl shrink-0"></i>
                                <div>
                                    <p class="text-xs font-black text-white">Medien</p>
                                    <p class="text-[10px] text-white/30">Cover, Backdrops, Schauspielerfotos</p>
                                </div>
                            </div>
                            <div class="p-5 bg-white/[0.03] rounded-2xl border border-white/5 flex items-center gap-4">
                                <i class="bi bi-file-earmark-zip-fill text-rose-500/60 text-xl shrink-0"></i>
                                <div>
                                    <p class="text-xs font-black text-white">Format</p>
                                    <p class="text-[10px] text-white/30">ZIP-Archiv mit .ms-Endung (MovieShelf)</p>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.backup.create') }}" method="POST" @submit="isCreating = true">
                            @csrf
                            <button type="submit" :disabled="isCreating"
                                class="w-full py-5 bg-rose-600 hover:bg-rose-500 disabled:opacity-50 disabled:cursor-wait text-white rounded-2xl font-black text-sm uppercase tracking-widest transition-all shadow-xl shadow-rose-500/20 flex items-center justify-center gap-3">
                                <i class="bi bi-cloud-download-fill text-lg" x-show="!isCreating" x-cloak></i>
                                <div class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" x-show="isCreating" x-cloak></div>
                                <span x-text="isCreating ? 'Wird erstellt...' : 'Backup jetzt erstellen'"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Backup wiederherstellen (Upload) -->
            <div class="animate-in fade-in slide-in-from-right-4 duration-700 delay-200">
                <div class="glass p-10 rounded-[3.5rem] border-white/5 relative overflow-hidden h-full flex flex-col">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-600/5 to-transparent pointer-events-none"></div>

                    <h2 class="text-lg font-black text-white mb-2 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400">
                            <i class="bi bi-upload"></i>
                        </div>
                        Backup wiederherstellen
                    </h2>
                    <p class="text-[10px] text-white/20 font-black uppercase tracking-widest mb-8 ml-14 italic">Lädt eine .ms-Datei hoch und stellt sie wieder her</p>

                    <form action="{{ route('admin.backup.restore') }}" method="POST" enctype="multipart/form-data"
                          class="flex-1 flex flex-col gap-6" @submit="isRestoring = true">
                        @csrf

                        <label class="group relative border-2 border-dashed border-amber-500/10 hover:border-amber-500/40 rounded-[2.5rem] p-10 text-center transition-all flex flex-col items-center justify-center gap-5 cursor-pointer hover:bg-amber-500/5 flex-1"
                               x-data="{ fileName: '' }">
                            <input type="file" name="backup_file" accept=".ms" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                   @change="fileName = $event.target.files[0]?.name ?? ''">
                            <div class="w-16 h-16 bg-amber-500/10 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="bi bi-file-earmark-arrow-up text-3xl text-amber-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-white" x-text="fileName || '.ms-Datei wählen'"></p>
                                <p class="text-[10px] text-white/20 uppercase tracking-widest font-black mt-1">Nur MovieShelf Backup-Dateien</p>
                            </div>
                        </label>

                        <div class="p-5 bg-amber-500/5 rounded-2xl border border-amber-500/10">
                            <p class="text-[10px] text-amber-400 font-black uppercase tracking-widest mb-1 flex items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill"></i> Achtung
                            </p>
                            <p class="text-[10px] text-white/30 leading-relaxed">Die aktuelle Sammlung wird überschrieben. Erstelle vorher ein Backup!</p>
                        </div>

                        <button type="submit" :disabled="isRestoring"
                            class="py-5 bg-amber-600 hover:bg-amber-500 disabled:opacity-50 disabled:cursor-wait text-white rounded-2xl font-black text-sm uppercase tracking-widest transition-all flex items-center justify-center gap-3 shadow-xl shadow-amber-500/10">
                            <i class="bi bi-arrow-counterclockwise text-lg" x-show="!isRestoring" x-cloak></i>
                            <div class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" x-show="isRestoring" x-cloak></div>
                            <span x-text="isRestoring ? 'Wird wiederhergestellt...' : 'Wiederherstellen'"></span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Gespeicherte Backups auf dem Server -->
            <div class="lg:col-span-2 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-300">
                <div class="glass p-10 rounded-[3.5rem] border-white/5 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/5 to-transparent pointer-events-none"></div>

                    <h2 class="text-lg font-black text-white mb-2 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400">
                            <i class="bi bi-server"></i>
                        </div>
                        Gespeicherte Backups
                    </h2>
                    <p class="text-[10px] text-white/20 font-black uppercase tracking-widest mb-8 ml-14 italic">Auf dem Server liegende .ms-Dateien</p>

                    <div class="space-y-4">
                        @forelse($msFiles as $file)
                            <div class="p-6 rounded-[2rem] bg-white/[0.02] border border-white/5 group hover:border-rose-500/20 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                                 x-data="{ restoring: false }">
                                <div class="flex items-center gap-5 min-w-0 flex-1">
                                    <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-500">
                                        <i class="bi bi-file-earmark-zip-fill text-rose-500/50 text-xl"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-white tracking-wide truncate">{{ $file['name'] }}</p>
                                        <p class="text-[9px] text-white/20 uppercase tracking-[0.2em] font-black mt-1">
                                            {{ date('d.m.Y H:i', $file['modified']) }}
                                            <span class="mx-2 text-rose-500/20">|</span>
                                            {{ number_format($file['size'] / 1024 / 1024, 2) }} MB
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <!-- Download -->
                                    <a href="{{ route('admin.backup.create') }}"
                                       class="w-10 h-10 flex items-center justify-center text-white/20 hover:text-blue-400 hover:bg-blue-500/10 rounded-xl transition-all"
                                       title="Herunterladen">
                                        <i class="bi bi-download text-sm"></i>
                                    </a>

                                    <!-- Löschen -->
                                    <form action="{{ route('admin.backup.destroy', $file['name']) }}" method="POST"
                                          onsubmit="return confirm('Backup \'{{ $file['name'] }}\' wirklich löschen?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-10 h-10 flex items-center justify-center text-white/10 hover:text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all" title="Löschen">
                                            <i class="bi bi-trash3 text-sm"></i>
                                        </button>
                                    </form>

                                    <!-- Wiederherstellen -->
                                    <form action="{{ route('admin.backup.restore-local') }}" method="POST"
                                          onsubmit="return confirm('Warnung: Die aktuelle Sammlung wird durch dieses Backup ÜBERSCHRIEBEN. Fortfahren?')"
                                          @submit="restoring = true">
                                        @csrf
                                        <input type="hidden" name="filename" value="{{ $file['name'] }}">
                                        <button type="submit" :disabled="restoring"
                                            class="px-6 py-3 bg-rose-600 hover:bg-rose-500 disabled:opacity-50 disabled:cursor-wait text-white rounded-xl font-black text-[10px] uppercase tracking-widest transition-all shadow-lg shadow-rose-500/20 flex items-center gap-2">
                                            <i class="bi bi-arrow-counterclockwise" x-show="!restoring" x-cloak></i>
                                            <div class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin" x-show="restoring" x-cloak></div>
                                            <span x-text="restoring ? 'Läuft...' : 'Wiederherstellen'"></span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-20 bg-black/10 rounded-[2.5rem] border border-dashed border-white/5">
                                <i class="bi bi-archive text-5xl text-white/5 mb-6 block"></i>
                                <p class="text-white/20 text-[10px] font-black uppercase tracking-widest italic">Keine Backups vorhanden</p>
                                <p class="text-[9px] text-white/10 mt-2">Erstelle oben dein erstes Backup.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>
