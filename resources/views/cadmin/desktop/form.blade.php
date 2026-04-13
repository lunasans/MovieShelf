@extends('cadmin.layout')

@section('header_title', $release->exists ? 'Release bearbeiten' : 'Neues Release')

@section('content')
<div class="max-w-4xl space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000">
    <div class="flex flex-col gap-2">
        <h2 class="text-3xl font-black text-white tracking-tight uppercase">{{ $release->exists ? 'Release editieren' : 'Neues Release erstellen' }}</h2>
        <p class="text-gray-400 font-medium">Konfiguriere das App-Update für deine Nutzer.</p>
    </div>

    <div class="glass rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl p-10">
        <form action="{{ $release->exists ? route('cadmin.desktop.update', $release) : route('cadmin.desktop.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @if($release->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Version -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Versionsnummer</label>
                    <input type="text" name="version" value="{{ old('version', $release->version) }}" placeholder="v0.1.0" 
                           class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/20 focus:outline-none focus:border-rose-500/50 transition-all font-bold">
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Verfügbarkeit</label>
                    <select name="is_public" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-rose-500/50 transition-all font-bold cursor-pointer">
                        <option value="1" {{ old('is_public', $release->is_public) ? 'selected' : '' }}>Öffentlich (Sichtbar für App)</option>
                        <option value="0" {{ !old('is_public', $release->is_public) ? 'selected' : '' }}>Entwurf (Versteckt)</option>
                    </select>
                </div>
            </div>

            <!-- Download URL -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Download URL (Extern)</label>
                <input type="url" name="download_url" value="{{ old('download_url', $release->download_url) }}" placeholder="https://github.com/..." 
                       class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/20 focus:outline-none focus:border-rose-500/50 transition-all font-bold italic">
            </div>

            <!-- EXE Upload -->
            <div class="space-y-2" x-data="{ fileName: null }">
                <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Installer Upload (.exe / .msi / .zip)</label>
                <div class="relative group">
                    <input type="file" name="exe_file" accept=".exe,.msi,.zip" class="hidden" id="exe_file"
                           @change="fileName = $event.target.files[0]?.name ?? null">
                    <label for="exe_file" class="flex items-center justify-center w-full h-32 border-2 border-dashed border-white/10 rounded-3xl hover:border-rose-500/30 hover:bg-rose-500/5 transition-all cursor-pointer">
                        <div class="text-center">
                            <template x-if="!fileName">
                                <div>
                                    <i class="bi bi-cloud-arrow-up text-3xl text-gray-400"></i>
                                    <p class="mt-2 text-xs text-gray-500 font-bold uppercase tracking-widest">Klicken zum Hochladen</p>
                                    <p class="text-[10px] text-gray-600 mt-1">Erlaubt: .exe, .msi, .zip · Max. 200MB</p>
                                </div>
                            </template>
                            <template x-if="fileName">
                                <div>
                                    <i class="bi bi-file-earmark-check text-3xl text-emerald-500"></i>
                                    <p class="mt-2 text-xs text-emerald-400 font-bold" x-text="fileName"></p>
                                    <p class="text-[10px] text-gray-600 mt-1">Klicken um Datei zu ändern</p>
                                </div>
                            </template>
                        </div>
                    </label>
                </div>
                @error('exe_file')
                    <p class="text-xs text-rose-400 font-bold px-2"><i class="bi bi-exclamation-triangle-fill"></i> {{ $message }}</p>
                @enderror
                @if($release->file_path)
                    <p class="text-[10px] text-emerald-500/60 font-medium px-2">✓ Bereits hochgeladen: {{ basename($release->file_path) }}</p>
                @endif
            </div>


            <!-- Changelog -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] px-2">Changelog / Release Notes</label>
                <textarea name="changelog" rows="6" placeholder="Was ist neu in dieser Version?" 
                          class="w-full bg-white/5 border border-white/10 rounded-3xl px-6 py-4 text-white placeholder-white/20 focus:outline-none focus:border-rose-500/50 transition-all font-medium custom-scrollbar">{{ old('changelog', $release->changelog) }}</textarea>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center gap-4">
                <button type="submit" class="w-full sm:w-auto px-10 py-4 bg-rose-600 hover:bg-rose-500 text-white font-black uppercase tracking-widest text-xs rounded-2xl shadow-xl shadow-rose-600/20 transition-all flex items-center justify-center gap-2">
                    <i class="bi bi-check2-circle text-lg"></i> Release speichern
                </button>
                <a href="{{ route('cadmin.desktop.index') }}" class="w-full sm:w-auto px-10 py-4 bg-white/5 hover:bg-white/10 text-white font-black uppercase tracking-widest text-xs rounded-2xl transition-all text-center">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
