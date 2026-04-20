@extends('cadmin.layout')

@section('header_title', $release->exists ? 'Release bearbeiten' : 'Neues Release')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000">
    <div class="flex flex-col gap-2">
        <h2 class="text-3xl font-black text-white tracking-tight uppercase">{{ $release->exists ? 'Release editieren' : 'Neues Release erstellen' }}</h2>
        <p class="text-gray-400 font-medium">Konfiguriere das App-Update für deine Nutzer.</p>
    </div>

    @if($errors->any())
    <div class="bg-rose-500/10 border border-rose-500/30 rounded-2xl px-6 py-4">
        <ul class="text-sm text-rose-400 font-bold space-y-1">
            @foreach($errors->all() as $error)
                <li><i class="bi bi-exclamation-circle-fill mr-2"></i>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="glass rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl p-10"
         x-data="chunkUploader()">

        <form id="release-form"
              action="{{ $release->exists ? route('cadmin.desktop.update', $release) : route('cadmin.desktop.store') }}"
              method="POST"
              class="space-y-10"
              @submit.prevent="handleSubmit($event)">
            @csrf
            @if($release->exists) @method('PUT') @endif

            {{-- Version + Status --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Versionsnummer</label>
                    <input type="text" name="version" id="version"
                           value="{{ old('version', $release->version) }}" placeholder="v0.1.0"
                           class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/20 focus:outline-none focus:border-rose-500/50 transition-all font-bold" required>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Verfügbarkeit</label>
                    <select name="is_public" id="is_public" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-rose-500/50 transition-all font-bold cursor-pointer">
                        <option value="1" {{ old('is_public', $release->is_public ?? 0) == '1' ? 'selected' : '' }}>Öffentlich (Sichtbar für App)</option>
                        <option value="0" {{ old('is_public', $release->is_public ?? 0) != '1' ? 'selected' : '' }}>Entwurf (Versteckt)</option>
                    </select>
                </div>
            </div>

            {{-- Platform Upload-Zonen --}}
            <div class="space-y-3">
                <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Dateien hochladen</label>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    @foreach([
                        ['key' => 'win',      'label' => 'Windows',        'icon' => 'bi-windows',  'accept' => '.exe,.msi,.zip',  'hint' => '.exe · .msi · .zip'],
                        ['key' => 'appimage', 'label' => 'Linux AppImage', 'icon' => 'bi-ubuntu',   'accept' => '.AppImage',       'hint' => '.AppImage'],
                        ['key' => 'deb',      'label' => 'Linux Debian',   'icon' => 'bi-terminal', 'accept' => '.deb',            'hint' => '.deb'],
                    ] as $p)
                    <div class="space-y-3">
                        {{-- Drop Zone --}}
                        <div class="relative">
                            <input type="file" id="file_{{ $p['key'] }}" accept="{{ $p['accept'] }}" class="hidden"
                                   @change="onFile('{{ $p['key'] }}', $event)">
                            <label for="file_{{ $p['key'] }}"
                                   class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed rounded-3xl transition-all cursor-pointer"
                                   :class="files.{{ $p['key'] }} ? 'border-emerald-500/40 bg-emerald-500/5' : 'border-white/10 hover:border-rose-500/30 hover:bg-rose-500/5'">
                                <template x-if="!files.{{ $p['key'] }}">
                                    <div class="text-center">
                                        <i class="bi {{ $p['icon'] }} text-2xl text-gray-500"></i>
                                        <p class="mt-1 text-[10px] text-gray-500 font-bold uppercase tracking-widest">{{ $p['label'] }}</p>
                                        <p class="text-[9px] text-gray-600 mt-0.5">{{ $p['hint'] }}</p>
                                    </div>
                                </template>
                                <template x-if="files.{{ $p['key'] }}">
                                    <div class="text-center px-3">
                                        <i class="bi bi-file-earmark-check text-2xl text-emerald-400"></i>
                                        <p class="mt-1 text-[10px] text-emerald-400 font-bold truncate max-w-full" x-text="files.{{ $p['key'] }}?.name"></p>
                                        <p class="text-[9px] text-gray-500 mt-0.5" x-text="sizes.{{ $p['key'] }}"></p>
                                    </div>
                                </template>
                            </label>
                        </div>

                        {{-- Fortschrittsbalken --}}
                        <div x-show="progress.{{ $p['key'] }} > 0" x-cloak class="space-y-1.5">
                            <div class="flex justify-between text-[9px] font-bold uppercase tracking-widest px-1">
                                <span class="text-gray-400" x-text="status.{{ $p['key'] }}"></span>
                                <span :class="done.{{ $p['key'] }} ? 'text-emerald-400' : 'text-white'" x-text="progress.{{ $p['key'] }} + '%'"></span>
                            </div>
                            <div class="w-full h-1.5 bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300"
                                     :class="done.{{ $p['key'] }} ? 'bg-gradient-to-r from-emerald-600 to-emerald-400' : 'bg-gradient-to-r from-rose-600 to-rose-400'"
                                     :style="'width:' + progress.{{ $p['key'] }} + '%'"></div>
                            </div>
                        </div>

                        {{-- URL --}}
                        <input type="text"
                               name="{{ $p['key'] === 'win' ? 'download_url' : 'download_url_linux_' . $p['key'] }}"
                               id="{{ $p['key'] === 'win' ? 'download_url' : 'download_url_linux_' . $p['key'] }}"
                               value="{{ old($p['key'] === 'win' ? 'download_url' : 'download_url_linux_' . $p['key'], $release->{$p['key'] === 'win' ? 'download_url' : 'download_url_linux_' . $p['key']} ?? '') }}"
                               placeholder="URL (wird automatisch gefüllt)"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/15 focus:outline-none focus:border-rose-500/50 transition-all font-medium italic text-xs">

                        {{-- SHA-256 --}}
                        <div class="relative">
                            <input type="text"
                                   name="{{ $p['key'] === 'win' ? 'file_hash' : 'file_hash_linux_' . $p['key'] }}"
                                   id="{{ $p['key'] === 'win' ? 'file_hash' : 'file_hash_linux_' . $p['key'] }}"
                                   value="{{ old($p['key'] === 'win' ? 'file_hash' : 'file_hash_linux_' . $p['key'], $release->{$p['key'] === 'win' ? 'file_hash' : 'file_hash_linux_' . $p['key']} ?? '') }}"
                                   placeholder="SHA-256 (automatisch berechnet)"
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 pr-14 text-white placeholder-white/15 focus:outline-none focus:border-rose-500/50 transition-all font-mono text-[10px]">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[8px] font-black text-white/15 uppercase tracking-widest">SHA256</span>
                        </div>
                    </div>
                    @endforeach

                </div>

                {{-- Globale Fehlermeldung --}}
                <p x-show="errorMsg" x-cloak class="text-xs text-rose-400 font-bold px-2 pt-1">
                    <i class="bi bi-exclamation-triangle-fill"></i> <span x-text="errorMsg"></span>
                </p>
            </div>

            {{-- Changelog --}}
            <div class="space-y-2">
                <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Changelog / Release Notes</label>
                <textarea name="changelog" rows="6" placeholder="Was ist neu in dieser Version?"
                          class="w-full bg-white/5 border border-white/10 rounded-3xl px-6 py-4 text-white placeholder-white/20 focus:outline-none focus:border-rose-500/50 transition-all font-medium custom-scrollbar">{{ old('changelog', $release->changelog) }}</textarea>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center gap-4">
                <button type="submit"
                        :disabled="uploading"
                        :class="uploading ? 'opacity-50 cursor-not-allowed' : ''"
                        class="w-full sm:w-auto px-10 py-4 bg-rose-600 hover:bg-rose-500 text-white font-black uppercase tracking-widest text-xs rounded-2xl shadow-xl shadow-rose-600/20 transition-all flex items-center justify-center gap-2">
                    <i class="bi bi-arrow-repeat text-lg animate-spin" x-show="uploading" x-cloak></i>
                    <i class="bi bi-check2-circle text-lg" x-show="!uploading"></i>
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
function chunkUploader() {
    const CHUNK_SIZE = 2 * 1024 * 1024; // 2 MB
    const PLATFORMS  = ['win', 'appimage', 'deb'];

    function makeState() {
        const o = {};
        PLATFORMS.forEach(p => o[p] = null);
        return o;
    }

    return {
        files:    makeState(),
        sizes:    makeState(),
        progress: { win: 0, appimage: 0, deb: 0 },
        status:   { win: '', appimage: '', deb: '' },
        done:     { win: false, appimage: false, deb: false },
        uploading: false,
        errorMsg:  '',

        onFile(platform, event) {
            const file = event.target.files[0];
            if (!file) return;
            this.files[platform]    = file;
            this.sizes[platform]    = (file.size / 1024 / 1024).toFixed(1) + ' MB';
            this.progress[platform] = 0;
            this.done[platform]     = false;
            this.status[platform]   = '';
            this.errorMsg           = '';
        },

        generateId() {
            return Math.random().toString(36).slice(2, 18) + Math.random().toString(36).slice(2, 18);
        },

        async uploadPlatform(platform, token, version) {
            const file = this.files[platform];
            if (!file) return null;

            const uploadId    = this.generateId();
            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);

            // Chunks hochladen
            for (let i = 0; i < totalChunks; i++) {
                const fd = new FormData();
                fd.append('_token',       token);
                fd.append('chunk',        file.slice(i * CHUNK_SIZE, (i + 1) * CHUNK_SIZE), file.name);
                fd.append('upload_id',    uploadId);
                fd.append('chunk_index',  i);
                fd.append('total_chunks', totalChunks);
                fd.append('filename',     file.name);

                this.status[platform]   = `Chunk ${i + 1}/${totalChunks}`;
                this.progress[platform] = Math.round((i / totalChunks) * 85);

                const res = await fetch('{{ route("cadmin.desktop.upload-chunk") }}', { method: 'POST', body: fd });
                if (!res.ok) throw new Error(`[${platform}] Chunk ${i + 1}: ` + await res.text());
            }

            // Zusammensetzen
            this.status[platform]   = 'Zusammensetzen…';
            this.progress[platform] = 90;

            const fd = new FormData();
            fd.append('_token',       token);
            fd.append('upload_id',    uploadId);
            fd.append('total_chunks', totalChunks);
            fd.append('filename',     file.name);
            fd.append('version',      version);
            fd.append('platform',     platform);

            const res  = await fetch('{{ route("cadmin.desktop.assemble-file") }}', {
                method: 'POST', headers: { Accept: 'application/json' }, body: fd,
            });
            const data = await res.json();
            if (!data.ok) throw new Error(`[${platform}] ` + (data.error || 'Unbekannter Fehler'));

            this.progress[platform] = 100;
            this.status[platform]   = 'Fertig';
            this.done[platform]     = true;

            return { url: data.url, hash: data.hash };
        },

        async handleSubmit(event) {
            this.errorMsg = '';
            const form    = event.target;
            const hasFiles = PLATFORMS.some(p => this.files[p] !== null);

            if (!hasFiles) {
                form.submit();
                return;
            }

            this.uploading = true;
            const token   = form.querySelector('[name="_token"]').value;
            const version = form.querySelector('[name="version"]').value;

            try {
                // Alle Plattformen parallel hochladen + zusammensetzen
                const results = await Promise.all(
                    PLATFORMS.map(p => this.uploadPlatform(p, token, version))
                );

                // FormData aus dem Formular bauen und URLs/Hashes eintragen
                const map     = { win: 'download_url', appimage: 'download_url_linux_appimage', deb: 'download_url_linux_deb' };
                const mapHash = { win: 'file_hash', appimage: 'file_hash_linux_appimage', deb: 'file_hash_linux_deb' };

                const fd = new FormData(form);
                PLATFORMS.forEach((p, i) => {
                    if (!results[i]) return;
                    if (results[i].url)  fd.set(map[p],     results[i].url);
                    if (results[i].hash) fd.set(mapHash[p], results[i].hash);
                });
                // _method (PUT) aus dem Formular übernehmen, _token ist schon drin
                fd.delete('exe_file'); // keine leere Datei mitsenden

                const res  = await fetch(form.action, {
                    method:  'POST',
                    headers: { 'Accept': 'application/json' },
                    body:    fd,
                });
                const data = await res.json();

                if (data.ok) {
                    this.uploading = false;
                    window.location.href = data.redirect;
                } else {
                    throw new Error(data.message || JSON.stringify(data.errors || data));
                }
            } catch (e) {
                this.uploading = false;
                this.errorMsg  = e.message;
            }
        },
    };
}
</script>
@endpush
