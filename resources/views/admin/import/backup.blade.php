<x-admin-layout>
    @section('header_title', 'Full Backup Import')

    <div class="max-w-7xl mx-auto" x-data="chunkedUploader()">
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
            <!-- 1. Browser Upload (NOW WITH CHUNKING) -->
            <div class="space-y-8 animate-in fade-in slide-in-from-left-4 duration-700 delay-100">
                <div class="glass p-10 rounded-[3.5rem] border-white/5 relative overflow-hidden h-full flex flex-col">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/5 to-transparent pointer-events-none"></div>
                    
                    <h2 class="text-lg font-black text-white mb-8 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                        </div>
                        Premium Browser-Upload
                    </h2>

                    <div class="flex-1 flex flex-col">
                        <!-- Dropzone / Input Area -->
                        <div class="group relative border-2 border-dashed rounded-[2.5rem] p-12 text-center transition-all flex-1 flex flex-col justify-center overflow-hidden"
                             :class="isUploading ? 'border-emerald-500/50 bg-emerald-500/5' : 'border-emerald-500/10 hover:border-emerald-500/50 hover:bg-emerald-500/5 cursor-pointer'">
                            
                            <!-- Hidden File Input -->
                            <input type="file" 
                                   @change="handleFileSelect" 
                                   class="absolute inset-0 w-full h-full opacity-0 z-20 cursor-pointer" 
                                   :disabled="isUploading">

                            <div class="space-y-6 relative z-10" x-show="!isUploading">
                                <div class="w-20 h-20 bg-emerald-500/10 rounded-3xl flex items-center justify-center mx-auto group-hover:scale-110 transition-transform shadow-xl shadow-emerald-500/5">
                                    <i class="bi bi-file-earmark-zip text-4xl text-emerald-500"></i>
                                </div>
                                <div x-show="!selectedFile">
                                    <p class="text-lg text-white font-black tracking-tight">ZIP-Datei wählen</p>
                                    <p class="text-white/20 text-[10px] mt-2 uppercase tracking-[0.2em] font-black italic">Bypass Cloudflare (Chunked Upload)</p>
                                </div>
                                <div x-show="selectedFile" class="animate-in zoom-in duration-300">
                                    <p class="text-lg text-white font-black tracking-tight" x-text="selectedFile?.name"></p>
                                    <p class="text-emerald-500 text-[10px] mt-2 uppercase tracking-[0.2em] font-black italic" x-text="formatSize(selectedFile?.size)"></p>
                                </div>
                            </div>

                            <!-- Progress UI -->
                            <div class="space-y-8 animate-in fade-in duration-500" x-show="isUploading">
                                <div class="relative">
                                    <svg class="w-32 h-32 mx-auto rotate-[-90deg]">
                                        <circle cx="64" cy="64" r="60" stroke="currentColor" stroke-width="8" fill="transparent" class="text-white/5"></circle>
                                        <circle cx="64" cy="64" r="60" stroke="currentColor" stroke-width="8" fill="transparent" stroke-dasharray="377" :stroke-dashoffset="377 - (377 * progress / 100)" class="text-emerald-500 transition-all duration-300"></circle>
                                    </svg>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-3xl font-black text-white" x-text="Math.round(progress) + '%'"></span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-white tracking-widest uppercase mb-2" x-text="statusMessage"></p>
                                    <p class="text-[9px] text-white/20 uppercase tracking-[0.3em] font-black italic" x-text="chunkStatus"></p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 p-6 bg-emerald-500/5 rounded-[1.5rem] border border-emerald-500/10">
                            <p class="text-[10px] text-emerald-400 font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                                <i class="bi bi-shield-lock-fill"></i> SaaS-Übertragung aktiv
                            </p>
                            <p class="text-[10px] text-white/40 leading-relaxed font-medium">Große Dateien werden automatisch gestückelt. Dies umgeht das 100MB Cloudflare-Limit und ist sicherer als ein direkter Upload.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Server-Side List -->
            <div class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-700 delay-200">
                <div class="glass p-10 rounded-[3.5rem] border-white/5 relative overflow-hidden h-full flex flex-col">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-600/5 to-transparent pointer-events-none"></div>
                    
                    <h2 class="text-lg font-black text-white mb-2 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400">
                            <i class="bi bi-server"></i>
                        </div>
                        Bereitgestellte Backups
                    </h2>
                    <p class="text-[10px] text-white/20 font-black uppercase tracking-widest mb-8 ml-14 italic">Dateien im Import-Ordner</p>

                    <div class="space-y-4 flex-1">
                        @forelse($zipFiles as $file)
                            <div class="p-6 rounded-[2rem] bg-white/[0.02] border border-white/5 group hover:border-emerald-500/30 transition-all flex items-center justify-between">
                                <div class="flex items-center gap-5 min-w-0 flex-1">
                                    <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                                        <i class="bi bi-archive-fill text-emerald-500/50 text-xl"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-black text-white tracking-wide mb-1 truncate">{{ $file['name'] }}</p>
                                        <p class="text-[9px] text-white/20 uppercase tracking-[0.2em] font-black">
                                            {{ date('d.m.Y H:i', $file['modified']) }} <span class="mx-2 text-emerald-500/20">|</span> {{ number_format($file['size'] / 1024 / 1024, 2) }} MB
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2 shrink-0 ml-4">
                                    <form action="{{ route('admin.import.backup.destroy', $file['name']) }}" method="POST" onsubmit="return confirm('Datei löschen?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-10 h-10 flex items-center justify-center text-white/10 hover:text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all">
                                            <i class="bi bi-trash3 text-sm"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.import.backup.local') }}" method="POST" @submit="isImporting = true">
                                        @csrf
                                        <input type="hidden" name="filename" value="{{ $file['name'] }}">
                                        <button type="submit" :disabled="isImporting" 
                                                class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-wait text-white rounded-xl font-black text-[10px] uppercase tracking-widest transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-3">
                                            
                                            <!-- Standard Icon -->
                                            <i class="bi bi-play-fill text-lg" x-show="!isImporting" x-cloak></i>
                                            
                                            <!-- Loading Spinner -->
                                            <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" x-show="isImporting" x-cloak></div>
                                            
                                            <span x-text="isImporting ? 'Importiere...' : 'Importieren'"></span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-20 bg-black/10 rounded-[2.5rem] border border-dashed border-white/5">
                                <i class="bi bi-cloud-slash text-5xl text-white/5 mb-6 block"></i>
                                <p class="text-white/20 text-[10px] font-black uppercase tracking-widest italic">Keine Backup-Dateien gefunden</p>
                                <p class="text-[9px] text-white/10 mt-2">Wähle links eine Datei für den Chunk-Upload aus.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Global Information -->
            <div class="lg:col-span-2">
                <div class="glass p-10 rounded-[3.5rem] border-white/5 bg-gradient-to-r from-emerald-600/5 to-transparent">
                    <h3 class="text-lg font-black text-white mb-6 uppercase tracking-tight">Sicherheits & SaaS Hinweise</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-emerald-500">
                                <i class="bi bi-cloud-check-fill"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">Resiliente Übertragung</span>
                            </div>
                            <p class="text-xs text-white/30 leading-relaxed font-medium">Dank Chunking können auch Gigabyte-große Backups über normale Internetverbindungen hochgeladen werden.</p>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-emerald-500">
                                <i class="bi bi-shield-fill-check"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">Sicherer Import</span>
                            </div>
                            <p class="text-xs text-white/30 leading-relaxed font-medium">Alle Dateien werden erst auf dem Server validiert und gegen Bedrohungen wie Zip-Slip oder Manipulation geprüft.</p>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-emerald-500">
                                <i class="bi bi-database-fill-check"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">Exklusive Admin-Rechte</span>
                            </div>
                            <p class="text-xs text-white/30 leading-relaxed font-medium">Nur du als Hauptadministrator kannst Backups einspielen. Dieser Vorgang ist unumkehrbar.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Toast -->
        <template x-if="showSuccess">
            <div class="fixed bottom-12 right-12 bg-emerald-600 text-white px-8 py-4 rounded-2xl shadow-2xl animate-in fade-in slide-in-from-bottom-4 duration-500 z-[100] flex items-center gap-4 border border-white/20">
                <i class="bi bi-check-circle-fill text-2xl"></i>
                <div>
                    <p class="font-black text-xs uppercase tracking-widest">Upload abgeschlossen</p>
                    <p class="text-[10px] opacity-60">Die Datei wurde erfolgreich auf dem Server zusammengesetzt.</p>
                </div>
            </div>
        </template>
    </div>

    <script>
        function chunkedUploader() {
            return {
                selectedFile: null,
                isUploading: false,
                isImporting: false,
                progress: 0,
                statusMessage: '',
                chunkStatus: '',
                showSuccess: false,
                chunkSize: 5 * 1024 * 1024, // 5MB chunks
                
                handleFileSelect(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    this.selectedFile = file;
                    this.startUpload();
                },

                formatSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                async startUpload() {
                    this.isUploading = true;
                    this.progress = 0;
                    this.statusMessage = 'Bereite Übertragung vor...';
                    
                    const file = this.selectedFile;
                    const totalChunks = Math.ceil(file.size / this.chunkSize);
                    const uuid = Math.random().toString(36).substring(2) + Date.now().toString(36);
                    
                    try {
                        for (let index = 0; index < totalChunks; index++) {
                            const start = index * this.chunkSize;
                            const end = Math.min(start + this.chunkSize, file.size);
                            const chunk = file.slice(start, end);
                            
                            this.statusMessage = 'Übertrage Daten...';
                            this.chunkStatus = `Teil ${index + 1} von ${totalChunks}`;
                            
                            await this.uploadChunk(chunk, index, totalChunks, uuid, file.name);
                            this.progress = ((index + 1) / totalChunks) * 100;
                        }
                    } catch (error) {
                        alert('Fehler beim Upload: ' + error.message);
                        this.isUploading = false;
                        return;
                    }

                    this.statusMessage = 'Erfolgreich übertragen!';
                    this.isUploading = false;
                    this.showSuccess = true;
                    
                    setTimeout(() => {
                        window.location.reload(); // Reload to show the new file in the list
                    }, 2000);
                },

                async uploadChunk(chunk, index, totalChunks, uuid, filename) {
                    const formData = new FormData();
                    formData.append('chunk', chunk);
                    formData.append('index', index);
                    formData.append('total_chunks', totalChunks);
                    formData.append('uuid', uuid);
                    formData.append('filename', filename);
                    formData.append('_token', '{{ csrf_token() }}');

                    const response = await fetch('{{ route("admin.import.backup.chunk") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Server-Fehler beim Chunk-Upload');
                    }

                    return await response.json();
                }
            }
        }
    </script>
</x-admin-layout>
