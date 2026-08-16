<h3 class="text-sm font-black text-white/40 uppercase tracking-[0.3em] mb-6 flex items-center gap-3">
    <i class="bi bi-collection text-indigo-400"></i> Kollektion
</h3>
<div class="space-y-3">
    @foreach($stats['collectionTypes'] as $type)
        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 hover:bg-white/8 transition-colors">
            <span class="text-xs font-black text-white/70 uppercase tracking-widest">
                {{ $type->collection_type ?: 'Unbekannt' }}
            </span>
            <span class="text-sm font-black text-white">{{ number_format($type->count) }}</span>
        </div>
    @endforeach
</div>
