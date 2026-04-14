@extends('cadmin.layout')

@section('header_title', $release->exists ? 'Release bearbeiten' : 'Neues Release')

@section('content')
<div class="max-w-4xl space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000">
    <div class="flex flex-col gap-2">
        <h2 class="text-3xl font-black text-white tracking-tight uppercase">{{ $release->exists ? 'Release editieren' : 'Neues Release erstellen' }}</h2>
        <p class="text-gray-400 font-medium">Konfiguriere das App-Update für deine Nutzer.</p>
    </div>

    <div class="glass rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl p-10"
         x-data="chunkUploader('{{ $release->exists ? route('cadmin.desktop.update', $release) : route('cadmin.desktop.store') }}', '{{ $release->exists ? 'PUT' : 'POST' }}')">

        {{-- Normales Formular für Metadaten (ohne Datei) --}}
        <form id="release-form" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Version -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Versionsnummer</label>
                    <input type="text" name="version" id="version"
                           value="{{ old('version', $release->version) }}" placeholder="v0.1.0"
                           class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/20 focus:outline-none focus:border-rose-500/50 transition-all font-bold">
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Verfügbarkeit</label>
                    <select name="is_public" id="is_public" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-rose-500/50 transition-all font-bold cursor-pointer">
                        <option value="1" {{ old('is_public', $release->is_public) ? 'selected' : '' }}>Öffentlich (Sichtbar für App)</option>
                        <option value="0" {{ !old('is_public', $release->is_public) ? 'selected' : '' }}>Entwurf (Versteckt)</option>
                    </select>
                </div>
            </div>

            <!-- Download URL -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Download URL (Extern, optional)</label>
                <input type="url" name="download_url" id="download_url"
                       value="{{ old('download_url', $release->download_url) }}" placeholder="https://github.com/..."
                       class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/20 focus:outline-none focus:border-rose-500/50 transition-all font-bold italic">
            </div>

            <!-- SHA-256 Hash -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">SHA-256 Hashwert (optional)</label>
                <div class="relative">
                    <input type="text" name="file_hash" id="file_hash"
                           value="{{ old('file_hash', $release->file_hash) }}"
                           placeholder="Wird beim Datei-Upload automatisch berechnet..."
                           class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 pr-14 text-white placeholder-white/20 focus:outline-none focus:border-rose-500/50 transition-all font-mono text-xs">
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-white/20 text-xs font-black uppercase tracking-widest">SHA256</div>
                </div>
                <p class="text-[10px] text-gray-600 px-2">Wird beim Datei-Upload automatisch berechnet. Manuell eintragen wenn du eine externe URL verwendest.</p>
            </div>

            <!-- EXE Upload (Chunked) -->
            <div class="space-y-3">
                <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Installer Upload (.exe / .msi / .zip)</label>

                <!-- Drop Zone -->
                <div class="relative">
                    <input type="file" id="exe_file" accept=".exe,.msi,.zip" class="hidden"
                           @change="onFileSelected($event)">
                    <label for="exe_file"
                           class="flex items-center justify-center w-full h-32 border-2 border-dashed border-white/10 rounded-3xl hover:border-rose-500/30 hover:bg-rose-500/5 transition-all cursor-pointer"
                           :class="selectedFile ? 'border-emerald-500/40 bg-emerald-500/5' : ''">
                        <div class="text-center" x-show="!selectedFile">
                            <i class="bi bi-cloud-arrow-up text-3xl text-gray-400"></i>
                            <p class="mt-2 text-xs text-gray-500 font-bold uppercase tracking-widest">Klicken zum Auswählen</p>
                            <p class="text-[10px] text-gray-600 mt-1">Erlaubt: .exe, .msi, .zip · Kein Größenlimit (Chunk-Upload)</p>
                        </div>
                        <div class="text-center" x-show="selectedFile" x-cloak>
                            <i class="bi bi-file-earmark-check text-3xl text-emerald-400"></i>
                            <p class="mt-2 text-xs text-emerald-400 font-bold" x-text="selectedFile?.name"></p>
                            <p class="text-[10px] text-gray-500 mt-1" x-text="fileSize"></p>
                        </div>
                    </label>
                </div>

                @if($release->file_path)
                    <p class="text-[10px] text-emerald-500/60 font-medium px-2">✓ Bereits hochgeladen: {{ basename($release->file_path) }}</p>
                @endif

                <!-- Fortschrittsbalken -->
                <div x-show="uploading || uploadDone" x-cloak class="space-y-2 pt-2">
                    <div class="flex justify-between text-[10px] font-bold uppercase tracking-widest px-1">
                        <span class="text-gray-400" x-text="statusText"></span>
                        <span class="text-white" x-text="progress + '%'"></span>
                    </div>
                    <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-rose-600 to-rose-400 rounded-full transition-all duration-300"
                             :style="'width:' + progress + '%'"></div>
                    </div>
                </div>

                <!-- Fehler -->
                <p x-show="errorMsg" x-cloak class="text-xs text-rose-400 font-bold px-2">
                    <i class="bi bi-exclamation-triangle-fill"></i> <span x-text="errorMsg"></span>
                </p>
            </div>

            <!-- Changelog -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Changelog / Release Notes</label>
                <textarea name="changelog" id="changelog" rows="6" placeholder="Was ist neu in dieser Version?"
                          class="w-full bg-white/5 border border-white/10 rounded-3xl px-6 py-4 text-white placeholder-white/20 focus:outline-none focus:border-rose-500/50 transition-all font-medium custom-scrollbar">{{ old('changelog', $release->changelog) }}</textarea>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center gap-4">
                <button type="button" @click="submit()"
                        :disabled="uploading"
                        :class="uploading ? 'opacity-50 cursor-not-allowed' : ''"
                        class="w-full sm:w-auto px-10 py-4 bg-rose-600 hover:bg-rose-500 text-white font-black uppercase tracking-widest text-xs rounded-2xl shadow-xl shadow-rose-600/20 transition-all flex items-center justify-center gap-2">
                    <i class="bi bi-check2-circle text-lg" x-show="!uploading"></i>
                    <i class="bi bi-arrow-repeat text-lg animate-spin" x-show="uploading" x-cloak></i>
                    <span x-text="uploading ? 'Wird hochgeladen...' : 'Release speichern'"></span>
                </button>
                <a href="{{ route('cadmin.desktop.index') }}" class="w-full sm:w-auto px-10 py-4 bg-white/5 hover:bg-white/10 text-white font-black uppercase tracking-widest text-xs rounded-2xl transition-all text-center">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function chunkUploader(submitUrl, submitMethod) {
    return {
        submitUrl: submitUrl,
        submitMethod: submitMethod,
        selectedFile: null,
        fileSize: '',
        uploading: false,
        uploadDone: false,
        progress: 0,
        statusText: '',
        errorMsg: '',

        CHUNK_SIZE: 2 * 1024 * 1024, // 2 MB pro Chunk

        onFileSelected(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.selectedFile = file;
            this.fileSize = (file.size / 1024 / 1024).toFixed(1) + ' MB';
            this.errorMsg = '';
            this.uploadDone = false;
            this.progress = 0;
        },

        generateId() {
            return Math.random().toString(36).substring(2, 18) +
                   Math.random().toString(36).substring(2, 18);
        },

        getFormData() {
            return {
                version:      document.getElementById('version').value.trim(),
                changelog:    document.getElementById('changelog').value.trim(),
                download_url: document.getElementById('download_url').value.trim(),
                is_public:    document.getElementById('is_public').value,
            };
        },

        async submit() {
            this.errorMsg = '';
            const meta = this.getFormData();

            if (!meta.version) {
                this.errorMsg = 'Bitte Versionsnummer angeben.';
                return;
            }

            // Falls keine Datei ausgewählt → nur Metadaten absenden
            if (!this.selectedFile) {
                await this.submitMetaOnly(meta);
                return;
            }

            await this.uploadInChunks(meta);
        },

        async submitMetaOnly(meta) {
            const fd = new FormData();
            fd.append('_token', document.querySelector('input[name="_token"]').value);
            if (this.submitMethod === 'PUT') {
                fd.append('_method', 'PUT');
            }
            Object.entries(meta).forEach(([k, v]) => fd.append(k, v));

            const res = await fetch(this.submitUrl, {
                method: 'POST', body: fd
            });

            if (res.redirected || res.ok) {
                window.location.href = '{{ route("cadmin.desktop.index") }}';
            } else {
                this.errorMsg = 'Fehler beim Speichern. Bitte alle Felder prüfen.';
            }
        },

        async uploadInChunks(meta) {
            this.uploading = true;
            const file = this.selectedFile;
            const uploadId = this.generateId();
            const totalChunks = Math.ceil(file.size / this.CHUNK_SIZE);
            const token = document.querySelector('input[name="_token"]').value;

            for (let i = 0; i < totalChunks; i++) {
                const start = i * this.CHUNK_SIZE;
                const chunk = file.slice(start, start + this.CHUNK_SIZE);

                const fd = new FormData();
                fd.append('_token', token);
                fd.append('chunk', chunk, file.name);
                fd.append('upload_id', uploadId);
                fd.append('chunk_index', i);
                fd.append('total_chunks', totalChunks);
                fd.append('filename', file.name);

                this.statusText = `Chunk ${i + 1} / ${totalChunks} wird hochgeladen...`;
                this.progress = Math.round(((i) / totalChunks) * 90);

                try {
                    const res = await fetch('{{ route("cadmin.desktop.upload-chunk") }}', {
                        method: 'POST', body: fd
                    });
                    if (!res.ok) throw new Error(await res.text());
                } catch (e) {
                    this.uploading = false;
                    this.errorMsg = 'Upload-Fehler bei Chunk ' + (i + 1) + ': ' + e.message;
                    return;
                }
            }

            // Finalisieren
            this.statusText = 'Datei wird zusammengesetzt...';
            this.progress = 95;

            const fd = new FormData();
            fd.append('_token', token);
            fd.append('upload_id', uploadId);
            fd.append('total_chunks', totalChunks);
            fd.append('filename', file.name);
            Object.entries(meta).forEach(([k, v]) => fd.append(k, v));

            try {
                const res = await fetch('{{ route("cadmin.desktop.finalize-upload") }}', {
                    method: 'POST', body: fd
                });
                const data = await res.json();
                if (data.ok) {
                    this.progress = 100;
                    this.statusText = 'Fertig! Weiterleitung...';
                    this.uploadDone = true;
                    this.uploading = false;
                    setTimeout(() => window.location.href = data.redirect, 800);
                } else {
                    throw new Error(data.error || 'Unbekannter Fehler');
                }
            } catch (e) {
                this.uploading = false;
                this.errorMsg = 'Fehler beim Finalisieren: ' + e.message;
            }
        }
    };
}
</script>
@endpush
