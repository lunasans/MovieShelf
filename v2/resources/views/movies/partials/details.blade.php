<div class="animate-in fade-in slide-in-from-right-4 duration-500" 
     x-data="{ 
        showTrailer: false,
        get youtubeId() {
            const url = '{{ $movie->trailer_url }}';
            const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:v\/|u\/\w\/|embed\/|watch\?v=))([^#\&\?]*)/);
            return (match && match[1].length == 11) ? match[1] : null;
        }
     }">
    <!-- Header Area -->
    <div class="relative rounded-[2.5rem] overflow-hidden glass-strong mb-10 aspect-[21/9] group shadow-2xl border border-white/5">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-950 flex items-center justify-center">
             @if($movie->backdrop_id)
                <img src="{{ Storage::url($movie->backdrop_id) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover opacity-60">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/40 to-transparent"></div>
            @elseif($movie->cover_id)
                <img src="{{ Storage::url($movie->cover_id) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-30">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent"></div>
            @else
                <i class="bi bi-film text-6xl text-gray-800"></i>
            @endif
        </div>

        <!-- Poster Overlay -->
        <div class="absolute left-8 bottom-8 flex items-end gap-8 z-20">
            <div class="relative w-32 md:w-40 aspect-[2/3] rounded-2xl overflow-hidden glass border-2 border-white/10 shadow-2xl group/poster">
                @if($movie->cover_id)
                    <img src="{{ Storage::url($movie->cover_id) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover">
                    @if($movie->trailer_url)
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover/poster:opacity-100 transition-opacity cursor-pointer" @click="showTrailer = true">
                            <div class="w-12 h-12 bg-rose-600 rounded-full flex items-center justify-center shadow-lg shadow-rose-600/40 transform scale-75 group-hover/poster:scale-100 transition-transform">
                                <i class="bi bi-play-fill text-2xl text-white ml-0.5"></i>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="w-full h-full bg-white/5 flex items-center justify-center">
                        <i class="bi bi-camera-video text-white/20 text-3xl"></i>
                    </div>
                @endif
            </div>
            
            <div class="pb-2">
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-3 py-1 bg-blue-600/80 backdrop-blur-md rounded-lg text-[10px] font-black tracking-widest uppercase border border-white/20 shadow-lg">
                        {{ $movie->collection_type }}
                    </span>
                    <span class="px-3 py-1 bg-white/10 backdrop-blur-md rounded-lg text-[10px] font-black tracking-widest uppercase border border-white/10 shadow-lg">
                        {{ $movie->year }}
                    </span>
                    @if($movie->rating_age !== null)
                        <img src="{{ asset('img/fsk/fsk-' . $movie->rating_age . '.svg') }}" 
                             alt="FSK {{ $movie->rating_age }}" 
                             class="h-8 w-auto drop-shadow-lg"
                             onerror="this.style.display='none'">
                    @endif
                </div>
                <h2 class="text-4xl font-black text-white leading-tight mb-2 drop-shadow-2xl tracking-tighter">
                    {{ $movie->title }}
                </h2>
                <div class="flex items-center gap-6 text-gray-300 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-clock text-blue-400"></i>
                        <span>{{ $movie->runtime }} Min.</span>
                    </div>
                    @if($movie->rating)
                        <div class="flex items-center gap-2">
                            <i class="bi bi-star-fill text-yellow-400"></i>
                            <span class="font-bold text-white">{{ number_format($movie->rating, 1) }} / 10</span>
                        </div>
                    @endif
                    @if($movie->director)
                        <div class="flex items-center gap-2">
                            <i class="bi bi-megaphone text-purple-400"></i>
                            <span class="text-white">{{ $movie->director }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="glass p-4 rounded-2xl flex flex-col items-center justify-center text-center">
            <span class="text-gray-500 text-[10px] font-bold uppercase tracking-widest mb-1">{{ __('Gesehen') }}</span>
            <div class="text-xl font-bold text-white">{{ $movie->view_count }}x</div>
        </div>
        <div class="glass p-4 rounded-2xl flex flex-col items-center justify-center text-center">
            <span class="text-gray-500 text-[10px] font-bold uppercase tracking-widest mb-1">{{ __('FSK') }}</span>
            <div class="flex items-center justify-center">
                @if($movie->rating_age !== null)
                    <img src="{{ asset('img/fsk/fsk-' . $movie->rating_age . '.svg') }}" 
                         alt="FSK {{ $movie->rating_age }}" 
                         class="h-10 w-auto">
                @else
                    <div class="text-xl font-bold text-gray-500">0</div>
                @endif
            </div>
        </div>
        <div class="glass p-4 rounded-2xl flex flex-col items-center justify-center text-center">
            <span class="text-gray-500 text-[10px] font-bold uppercase tracking-widest mb-1">{{ __('Medientyp') }}</span>
            <div class="text-lg font-bold text-blue-400 uppercase tracking-tighter">{{ $movie->collection_type }}</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="space-y-8">
        <!-- Overview -->
        @if($movie->overview)
            <div class="glass p-6 rounded-3xl border-white/5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2 underline decoration-blue-500/50 underline-offset-8">
                    <i class="bi bi-text-left text-blue-400"></i>
                    {{ __('Beschreibung') }}
                </h3>
                <p class="text-gray-300 leading-relaxed text-sm">
                    {{ $movie->overview }}
                </p>
            </div>
        @endif

        <!-- Actors -->
        <div class="glass p-6 rounded-3xl border-white/5">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2 underline decoration-blue-500/50 underline-offset-8">
                <i class="bi bi-people text-blue-400"></i>
                {{ __('Besetzung') }}
            </h3>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($movie->actors->take(9) as $actor)
                    <div class="flex items-center gap-3 group/actor cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center flex-shrink-0 group-hover/actor:border-blue-500/50 transition-colors shadow-lg">
                            <i class="bi bi-person text-lg text-gray-500 group-hover/actor:text-blue-400 transition-colors"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-white truncate group-hover/actor:text-blue-400 transition-colors">
                                {{ $actor->name }}
                            </div>
                            <div class="text-[10px] text-gray-500 truncate italic">
                                {{ $actor->pivot->role ?: 'Unbekannte Rolle' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-gray-600 text-xs italic">
                        Keine Besetzung hinterlegt.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Boxset -->
        @if($movie->boxsetChildren->count() > 0)
            <div class="glass p-6 rounded-3xl border-white/5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="bi bi-collection-play text-blue-400"></i>
                    {{ __('Filme in diesem Set') }} ({{ $movie->boxsetChildren->count() }})
                </h3>
                <div class="space-y-3">
                    @foreach($movie->boxsetChildren as $child)
                        <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/5 transition-colors cursor-pointer group/child">
                            <div class="w-8 h-12 bg-gray-800 rounded-lg flex items-center justify-center flex-shrink-0 border border-white/5 group-hover/child:border-blue-500/30 overflow-hidden">
                                <i class="bi bi-film text-gray-700"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-white truncate">{{ $child->title }}</div>
                                <div class="text-[10px] text-gray-500 uppercase">{{ $child->year }} • {{ $child->collection_type }}</div>
                            </div>
                            <i class="bi bi-chevron-right text-gray-700 text-xs mr-2"></i>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="mt-8 flex gap-3">
        @if($movie->trailer_url)
            <button @click="showTrailer = true" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white px-6 py-3 rounded-2xl font-bold text-sm transition-all shadow-lg shadow-rose-600/20 flex items-center justify-center gap-2">
                <i class="bi bi-youtube text-xl"></i>
                {{ __('Trailer ansehen') }}
            </button>
            
            <!-- Trailer Modal -->
            <template x-teleport="body">
                <div x-show="showTrailer" 
                     class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-10"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/95 backdrop-blur-2xl" @click="showTrailer = false"></div>
                    
                    <!-- Modal Content -->
                    <div class="relative w-full max-w-5xl aspect-video bg-black rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/10"
                         x-show="showTrailer"
                         x-transition:enter="transition ease-out duration-500 delay-100"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         @keydown.window.escape="showTrailer = false">
                        
                        <button @click="showTrailer = false" class="absolute top-6 right-6 z-50 w-12 h-12 bg-white/10 hover:bg-rose-600 backdrop-blur-md rounded-full flex items-center justify-center text-white transition-all shadow-lg">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>

                        <div class="w-full h-full">
                            <template x-if="showTrailer">
                                <iframe 
                                    :src="'https://www.youtube.com/embed/' + youtubeId + '?autoplay=1&modestbranding=1&rel=0'" 
                                    class="w-full h-full" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                                </iframe>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        @else
            <button class="flex-1 bg-white/5 cursor-not-allowed text-gray-500 px-6 py-3 rounded-2xl font-bold text-sm border border-white/10 flex items-center justify-center gap-2">
                <i class="bi bi-slash-circle text-xl"></i>
                {{ __('Kein Trailer verfügbar') }}
            </button>
        @endif
        <button class="w-14 h-14 bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl flex items-center justify-center transition-colors">
            <i class="bi bi-heart text-xl text-gray-400 hover:text-rose-500 transition-colors"></i>
        </button>
    </div>
</div>
