@forelse($groupedActors as $groupLetter => $groupActors)
    <div class="animate-in fade-in slide-in-from-bottom-12 duration-1000">
        <div class="flex items-center gap-8 mb-12">
            <div class="w-16 h-16 rounded-[1.5rem] bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center text-white text-3xl font-black shadow-2xl shadow-blue-500/30 uppercase italic tracking-tighter">
                {{ $groupLetter }}
            </div>
            <div class="flex-1 h-px bg-gradient-to-r from-white/20 via-white/5 to-transparent"></div>
            <span class="text-[10px] font-black text-white/20 uppercase tracking-[0.4em] italic leading-none">
                {{ $groupActors->count() }} {{ __('Actors') }}
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-8">
            @foreach($groupActors as $actor)
                <a href="{{ route('actors.show', $actor) }}" class="group relative aspect-[2.5/4] rounded-[2rem] overflow-hidden glass-streaming border border-white/5 transition-all duration-700 hover:scale-[1.05] hover:border-blue-500/50 shadow-2xl">
                    @if($actor->profile_url)
                        <img src="{{ $actor->profile_url }}" alt="{{ $actor->full_name }}" class="w-full h-full object-cover transition-transform duration-[2s] group-hover:scale-110">
                    @else
                        <div class="w-full h-full bg-[#1a1a1f] flex items-center justify-center">
                            <i class="bi bi-person-fill text-7xl text-white/5 group-hover:scale-110 transition-transform duration-700"></i>
                        </div>
                    @endif
                    
                    <div class="absolute inset-x-0 bottom-0 h-3/4 bg-gradient-to-t from-[#0c0c0e] via-[#0c0c0e]/60 to-transparent opacity-80 group-hover:opacity-100 transition-all duration-500"></div>
                    
                    <div class="absolute bottom-0 left-0 right-0 p-6 transform translate-y-2 group-hover:translate-y-0 transition-all duration-700">
                        <h3 class="text-white font-black text-[11px] leading-tight tracking-[0.05em] mb-3 uppercase group-hover:text-blue-400 transition-colors line-clamp-2">
                            {{ $actor->full_name }}
                        </h3>
                        <div class="flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity duration-700 delay-100">
                            <span class="text-[8px] font-black uppercase tracking-[0.3em] text-white/40 italic">
                                {{ $actor->movies_count }} {{ $actor->movies_count == 1 ? __('Film') : __('Films') }}
                            </span>
                            <i class="bi bi-arrow-right-short text-blue-500 text-lg"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@empty
@endforelse
