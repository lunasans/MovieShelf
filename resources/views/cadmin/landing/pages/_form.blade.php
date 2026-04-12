@php $model = $model ?? null; @endphp

<!-- Title -->
<div class="space-y-2">
    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ms-1">Titel</label>
    <input type="text" name="title" id="title" value="{{ old('title', $model?->title) }}" required
           placeholder="z.B. Datenschutzerklärung"
           class="block w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500/50 text-white transition-all outline-none placeholder:text-gray-600 font-medium"
           @input="updateSlug">
    @error('title') <p class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mt-2 ms-1">{{ $message }}</p> @enderror
</div>

<!-- Slug -->
<div class="space-y-2">
    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ms-1">Slug <span class="text-gray-600 normal-case font-normal">(URL-Pfad: /p/…)</span></label>
    <div class="flex items-center bg-white/5 border border-white/10 rounded-2xl overflow-hidden focus-within:ring-2 focus-within:ring-rose-500/50 focus-within:border-rose-500/50 transition-all">
        <span class="px-4 py-4 text-gray-600 text-sm font-mono border-r border-white/10 shrink-0">/p/</span>
        <input type="text" name="slug" id="slug" x-model="slug" value="{{ old('slug', $model?->slug) }}"
               placeholder="dein-seitenname"
               class="flex-1 bg-transparent px-4 py-4 text-white outline-none font-mono text-sm placeholder:text-gray-600">
    </div>
    @error('slug') <p class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mt-2 ms-1">{{ $message }}</p> @enderror
</div>

<!-- Content (Quill) -->
<div class="space-y-2">
    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ms-1">Inhalt</label>
    <div class="rounded-2xl overflow-hidden border border-white/10">
        <div id="content-editor" style="min-height: 300px; background: rgba(5,5,5,0.4); color: #fff;"></div>
    </div>
    <input type="hidden" name="content" id="content" x-model="formData.content">
    @error('content') <p class="text-rose-500 text-[10px] font-bold uppercase tracking-widest mt-2 ms-1">{{ $message }}</p> @enderror
</div>

<!-- Options Row -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-4">
    <!-- Sort Order -->
    <div class="space-y-2">
        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ms-1">Sortierung</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $model?->sort_order ?? 0) }}"
               class="block w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:ring-2 focus:ring-rose-500/50 focus:border-rose-500/50 text-white transition-all outline-none font-bold">
    </div>

    <!-- Active -->
    <div class="space-y-4">
        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ms-1">Status</label>
        <label class="relative flex items-center p-4 cursor-pointer glass border border-white/10 rounded-2xl group hover:bg-white/5 transition-all">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $model?->is_active ?? true) ? 'checked' : '' }}
                   class="opacity-0 absolute inset-0 w-full h-full cursor-pointer z-10 peer">
            <div class="w-12 h-6 bg-white/10 rounded-full p-1 transition-all peer-checked:bg-emerald-500 flex-shrink-0">
                <div class="w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-6"></div>
            </div>
            <span class="ms-4 text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Öffentlich sichtbar</span>
        </label>
    </div>

    <!-- Show in Nav -->
    <div class="space-y-4">
        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 ms-1">Navigation</label>
        <label class="relative flex items-center p-4 cursor-pointer glass border border-white/10 rounded-2xl group hover:bg-white/5 transition-all">
            <input type="hidden" name="show_in_nav" value="0">
            <input type="checkbox" name="show_in_nav" value="1" {{ old('show_in_nav', $model?->show_in_nav ?? false) ? 'checked' : '' }}
                   class="opacity-0 absolute inset-0 w-full h-full cursor-pointer z-10 peer">
            <div class="w-12 h-6 bg-white/10 rounded-full p-1 transition-all peer-checked:bg-blue-500 flex-shrink-0">
                <div class="w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-6"></div>
            </div>
            <span class="ms-4 text-sm font-bold text-white group-hover:text-blue-400 transition-colors">Im Footer anzeigen</span>
        </label>
    </div>
</div>
