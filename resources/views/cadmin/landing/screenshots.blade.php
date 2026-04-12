@extends('cadmin.layout')

@section('header_title', 'Screenshots')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000" x-data="screenshotManager()">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white uppercase tracking-tight">Screenshot Galerie</h2>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mt-1">Bilder für den Slider auf der Landingpage</p>
        </div>
        <button @click="showUpload = !showUpload"
                class="px-6 py-3 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-rose-900/20 transition-all active:scale-95 flex items-center gap-2">
            <i class="bi bi-cloud-upload-fill"></i>
            Bilder hochladen
        </button>
    </div>

    <!-- Upload Panel -->
    <div x-show="showUpload" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="glass rounded-[2rem] border border-white/10 overflow-hidden">
        <div class="px-8 py-6 border-b border-white/10 bg-white/5 flex items-center gap-4">
            <div class="w-10 h-10 bg-rose-500/20 rounded-xl flex items-center justify-center border border-rose-500/30">
                <i class="bi bi-image text-rose-500"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-white uppercase tracking-tight">Neue Screenshots</h3>
                <p class="text-[10px] text-gray-500">JPG, PNG, WebP bis 5 MB pro Bild</p>
            </div>
        </div>

        <form action="{{ route('cadmin.landing.screenshots.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            @csrf

            <!-- Drop Zone -->
            <div class="border-2 border-dashed border-white/10 rounded-2xl p-8 text-center hover:border-rose-500/30 transition-colors cursor-pointer"
                 @click="$refs.fileInput.click()"
                 @dragover.prevent="dragover = true"
                 @dragleave="dragover = false"
                 @drop.prevent="handleDrop($event)"
                 :class="dragover ? 'border-rose-500/50 bg-rose-500/5' : ''">
                <input type="file" name="images[]" multiple accept="image/*" class="hidden" x-ref="fileInput" @change="handleFiles($event.target.files)">
                <i class="bi bi-cloud-arrow-up text-4xl text-gray-600 mb-3 block"></i>
                <p class="text-sm font-bold text-gray-400">Klicken oder Bilder hier ablegen</p>
                <p class="text-[10px] text-gray-600 mt-1">Mehrere Bilder gleichzeitig möglich</p>
            </div>

            <!-- Preview Grid -->
            <template x-if="previews.length > 0">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <template x-for="(preview, i) in previews" :key="i">
                        <div class="relative group">
                            <img :src="preview.url" class="w-full aspect-video object-cover rounded-xl border border-white/10">
                            <div class="mt-2">
                                <input type="text" :name="`alt_texts[${i}]`" placeholder="Bildbeschreibung (optional)"
                                       class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-xs text-white placeholder-gray-600 outline-none focus:border-rose-500/50">
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <div class="flex justify-end gap-3">
                <button type="button" @click="showUpload = false; previews = []" class="px-6 py-3 bg-white/5 hover:bg-white/10 text-gray-400 text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                    Abbrechen
                </button>
                <button type="submit" x-show="previews.length > 0" x-cloak
                        class="px-8 py-3 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all active:scale-95 flex items-center gap-2">
                    <i class="bi bi-save2-fill"></i>
                    <span x-text="`${previews.length} Bild(er) speichern`"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Screenshots Grid -->
    <div class="glass rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl">
        @if($screenshots->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 px-8 text-center">
                <div class="w-20 h-20 rounded-full bg-white/5 flex items-center justify-center mb-6 border border-white/10">
                    <i class="bi bi-images text-gray-600 text-3xl"></i>
                </div>
                <h3 class="text-lg font-black text-white uppercase tracking-tight mb-2">Keine Screenshots</h3>
                <p class="text-gray-500 max-w-sm font-medium">Lade Bilder hoch, die im Slider der Landingpage angezeigt werden sollen.</p>
            </div>
        @else
            <div class="px-8 py-6 border-b border-white/10 bg-white/[0.02] flex items-center justify-between">
                <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.3em]">{{ $screenshots->count() }} Screenshot(s) &bull; Reihenfolge per Drag & Drop ändern</p>
                <button id="saveOrder" onclick="saveOrder()" class="hidden px-4 py-2 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-white border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all">
                    <i class="bi bi-check2 me-1"></i> Reihenfolge speichern
                </button>
            </div>

            <div class="p-8">
                <div id="screenshotGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($screenshots as $screenshot)
                    <div class="group glass border border-white/10 rounded-2xl overflow-hidden relative" data-id="{{ $screenshot->id }}">
                        <!-- Drag handle -->
                        <div class="absolute top-3 left-3 z-10 cursor-grab active:cursor-grabbing p-2 bg-black/40 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity" title="Reihenfolge ändern">
                            <i class="bi bi-grip-vertical text-white/60"></i>
                        </div>

                        <!-- Active badge -->
                        <div class="absolute top-3 right-3 z-10">
                            @if($screenshot->is_active)
                                <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-500/20">Aktiv</span>
                            @else
                                <span class="px-2 py-1 bg-white/10 text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-full border border-white/10">Inaktiv</span>
                            @endif
                        </div>

                        <!-- Image -->
                        <div class="aspect-video bg-white/5 overflow-hidden">
                            <img src="{{ $screenshot->url }}" alt="{{ $screenshot->alt_text }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-gray-600\'><i class=\'bi bi-image text-3xl\'></i></div>'">
                        </div>

                        <!-- Edit form -->
                        <form action="{{ route('cadmin.landing.screenshots.update', $screenshot) }}" method="POST" class="p-4 space-y-3">
                            @csrf @method('PATCH')
                            <input type="text" name="alt_text" value="{{ $screenshot->alt_text }}" placeholder="Bildbeschreibung..."
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-xs text-white placeholder-gray-600 outline-none focus:border-rose-500/50 transition-colors">
                            <div class="flex items-center justify-between gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ $screenshot->is_active ? 'checked' : '' }}
                                           class="opacity-0 absolute peer" style="width:1px;height:1px;">
                                    <div class="w-9 h-5 bg-white/10 rounded-full p-0.5 transition-all peer-checked:bg-emerald-500 flex-shrink-0">
                                        <div class="w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-4"></div>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-bold">Sichtbar</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="px-3 py-1.5 bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white border border-white/10 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                    <button type="button"
                                            onclick="if(confirm('Screenshot wirklich löschen?')) { document.getElementById('del-{{ $screenshot->id }}').submit(); }"
                                            class="px-3 py-1.5 bg-white/5 hover:bg-rose-500/10 text-gray-500 hover:text-rose-500 border border-white/10 hover:border-rose-500/30 rounded-lg text-[10px] font-black transition-all">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <form id="del-{{ $screenshot->id }}" action="{{ route('cadmin.landing.screenshots.destroy', $screenshot) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
function screenshotManager() {
    return {
        showUpload: false,
        dragover: false,
        previews: [],
        handleFiles(files) {
            this.previews = [];
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => this.previews.push({ url: e.target.result, name: file.name });
                reader.readAsDataURL(file);
            });
        },
        handleDrop(e) {
            this.dragover = false;
            const dt = new DataTransfer();
            Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
            this.handleFiles(e.dataTransfer.files);
        }
    }
}

const grid = document.getElementById('screenshotGrid');
if (grid) {
    Sortable.create(grid, {
        animation: 150,
        handle: '[title="Reihenfolge ändern"]',
        onEnd() { document.getElementById('saveOrder').classList.remove('hidden'); }
    });
}

function saveOrder() {
    const ids = Array.from(document.querySelectorAll('#screenshotGrid [data-id]')).map(el => el.dataset.id);
    fetch('{{ route('cadmin.landing.screenshots.reorder') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ order: ids })
    }).then(() => {
        document.getElementById('saveOrder').classList.add('hidden');
    });
}
</script>
@endpush
@endsection
