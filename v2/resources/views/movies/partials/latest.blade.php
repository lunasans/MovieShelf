<div class="animate-in fade-in slide-in-from-right-4 duration-500">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-xl font-bold text-white flex items-center gap-3">
            <div class="h-10 w-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="bi bi-stars"></i>
            </div>
            <span>{{ __('Neu hinzugefügt') }}</span>
        </h2>
        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest bg-white/5 border border-white/10 px-3 py-1 rounded-full">
            {{ $latestMovies->count() }} {{ __('Filme') }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4">
        @forelse($latestMovies as $lat)
            <div class="group cursor-pointer" @click="fetchDetails({{ $lat->id }})">
                <div class="relative aspect-[2/3] rounded-2xl overflow-hidden glass border border-white/5 shadow-lg transition-all duration-500 group-hover:scale-[1.05] group-hover:shadow-blue-500/20 group-hover:border-blue-500/30">
                    <!-- Movie Cover Placeholder -->
                    <div class="absolute inset-0 bg-gray-800 flex items-center justify-center">
                        @if($lat->cover_id)
                            <img src="{{ Storage::url($lat->cover_id) }}" alt="{{ $lat->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            <div class="absolute inset-0 bg-blue-500/10 group-hover:bg-transparent transition-colors"></div>
                        @else
                            <i class="bi bi-film text-2xl text-gray-700"></i>
                        @endif
                    </div>
                    
                    <!-- New Badge -->
                    <div class="absolute top-2 left-2 z-20">
                        <span class="bg-blue-600 text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-lg uppercase tracking-tighter">
                            NEW
                        </span>
                    </div>
                </div>
                
                <div class="mt-2 px-1">
                    <h3 class="text-[11px] font-bold text-gray-200 truncate group-hover:text-blue-400 transition-colors">
                        {{ $lat->title }}
                    </h3>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-[9px] text-gray-500 font-medium">{{ $lat->year }}</span>
                        <span class="w-0.5 h-0.5 bg-gray-700 rounded-full"></span>
                        <span class="text-[9px] text-blue-500/80 font-bold uppercase tracking-tighter">{{ $lat->collection_type }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center glass rounded-3xl border-dashed">
                <i class="bi bi-inbox text-4xl text-gray-700 mb-4 block"></i>
                <p class="text-gray-500 text-sm">{{ __('Keine neuen Filme gefunden.') }}</p>
            </div>
        @endforelse
    </div>

    <!-- Stats Hint -->
    <div class="mt-10 p-6 rounded-3xl glass border-blue-500/10 bg-gradient-to-br from-blue-500/5 to-purple-600/5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 flex-shrink-0">
                <i class="bi bi-info-circle-fill text-xl"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-white mb-1 uppercase tracking-widest">{{ __('Wussten Sie schon?') }}</h4>
                <p class="text-[11px] text-gray-400 leading-relaxed">
                    {{ __('Sie können die Anzahl der hier angezeigten Filme in den Einstellungen im Adminpanel anpassen.') }}
                </p>
            </div>
        </div>
    </div>
</div>
