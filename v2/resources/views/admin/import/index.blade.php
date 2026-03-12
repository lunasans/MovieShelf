<x-admin-layout>
    <div class="p-6 md:p-10">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-white tracking-tight">DVD Profiler Import</h1>
                <p class="text-white/50 mt-1 uppercase text-xs font-bold tracking-widest">XML-Dateien importieren</p>
            </div>
            <a href="{{ route('admin.movies.index') }}" class="glass-button flex items-center gap-2 group">
                <i class="bi bi-arrow-left transition-transform group-hover:-translate-x-1"></i>
                Zurück zur Liste
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Upload Section -->
            <div class="space-y-6">
                <div class="glass p-8 rounded-[2.5rem] border-white/5 bg-gradient-to-br from-white/5 to-transparent">
                    <h2 class="text-lg font-black text-white mb-6 flex items-center gap-3">
                        <i class="bi bi-upload text-blue-400"></i>
                        XML oder ZIP hochladen
                    </h2>
                    
                    <form action="{{ route('admin.import.post') }}" method="POST" enctype="multipart/form-data" class="upload-area group relative border-2 border-dashed border-white/10 rounded-3xl p-10 text-center hover:border-blue-500/50 hover:bg-white/5 transition-all cursor-pointer">
                        @csrf
                        <input type="file" name="xml_file" id="xml_file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="this.form.submit()">
                        <div class="space-y-4">
                            <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                                <i class="bi bi-file-earmark-code text-3xl text-blue-400"></i>
                            </div>
                            <div>
                                <p class="text-white font-bold">Klicke zum Auswählen oder Drag & Drop</p>
                                <p class="text-white/30 text-xs mt-2 uppercase tracking-widest font-bold">XML oder ZIP (max. 50MB)</p>
                            </div>
                        </div>
                    </form>

                    <div class="mt-8 p-6 bg-white/5 rounded-2xl border border-white/5">
                        <h3 class="text-xs font-black text-white/40 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="bi bi-info-circle"></i> Hinweise
                        </h3>
                        <ul class="text-xs text-white/60 space-y-3">
                            <li class="flex gap-2">
                                <i class="bi bi-check2 text-emerald-400"></i>
                                <span>Exportiere deine Sammlung aus DVD Profiler als XML.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="bi bi-check2 text-emerald-400"></i>
                                <span>BoxSets werden automatisch anhand der IDs gruppiert.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="bi bi-check2 text-emerald-400"></i>
                                <span>Bestehende Filme werden aktualisiert.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- History Section -->
            <div class="space-y-6">
                <div class="glass p-8 rounded-[2.5rem] border-white/5">
                    <h2 class="text-lg font-black text-white mb-6 flex items-center gap-3">
                        <i class="bi bi-clock-history text-purple-400"></i>
                        Import Historie
                    </h2>

                    @if(count($xmlFiles) > 0)
                        <div class="space-y-4">
                            @foreach($xmlFiles as $file)
                                <div class="p-4 rounded-2xl bg-white/5 border border-white/5 flex items-center justify-between group hover:border-white/20 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center">
                                            <i class="bi bi-filetype-xml text-white/40"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-white mb-1">{{ $file['name'] }}</p>
                                            <p class="text-[10px] text-white/30 uppercase tracking-widest font-black">
                                                {{ date('d.m.Y H:i', $file['modified']) }} • {{ number_format($file['size'] / 1024 / 1024, 2) }} MB
                                            </p>
                                        </div>
                                    </div>
                                    <form action="{{ route('admin.import.destroy', $file['name']) }}" method="POST" onsubmit="return confirm('Datei wirklich löschen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-white/20 hover:text-rose-500 transition-colors">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="bi bi-folder-x text-4xl text-white/10 mb-4 block"></i>
                            <p class="text-white/30 text-sm italic">Noch keine Import-Dateien vorhanden.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .glass-button {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        .glass-button:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }
    </style>
</x-admin-layout>
