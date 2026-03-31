<div class="relative min-h-screen text-white overflow-x-hidden"
     x-data="{
        showTrailer: false,
        get youtubeId() {
            const url = '{{ $movie->trailer_url }}';
            const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:v\/|u\/\w\/|embed\/|watch\?v=))([^#\&\?]*)/);
            return (match && match[1].length == 11) ? match[1] : null;
        }
     }">
    {{-- Fullscreen Backdrop --}}
    <div class="fixed inset-0 z-0">
        <img src="{{ $movie->backdrop_url ?: $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0c0c0e] via-[#0c0c0e]/60 to-[#0c0c0e]/30"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#0c0c0e] via-transparent to-transparent"></div>
    </div>

    {{-- Content --}}
    <div class="relative z-10 pt-32 pb-20 px-4 md:px-12 lg:px-24">
        {{-- Header / Back Button --}}
        <div class="mb-12">
            <a href="{{ route('dashboard') }}" class="group inline-flex items-center gap-4 text-white/60 hover:text-white transition-all">
                <div class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center bg-white/5 backdrop-blur-xl group-hover:border-white/30 group-hover:scale-110 transition-all">
                    <i class="bi bi-arrow-left text-xl"></i>
                </div>
                <span class="font-black uppercase tracking-widest text-sm italic">{{ __('Back to Library') }}</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            {{-- Left column: Info --}}
            <div class="lg:col-span-8 animate-in slide-in-from-left duration-700">
                <div class="mb-6 flex items-center gap-3">
                    <span class="bg-red-600 text-white text-[10px] font-black px-2 py-1 rounded shadow-lg uppercase tracking-widest">{{ $movie->collection_type }}</span>
                    @if($movie->rating)
                        <span class="bg-yellow-500/20 text-yellow-500 border border-yellow-500/30 text-[10px] font-black px-2 py-1 rounded uppercase tracking-widest">
                            <i class="bi bi-star-fill mr-1"></i> {{ $movie->rating }}/10
                        </span>
                    @endif
                    @php
                        $runtime = $movie->runtime ?: ($movie->tmdb_json['runtime'] ?? ($movie->tmdb_json['episode_run_time'][0] ?? null));
                    @endphp
                    <span class="text-white/40 text-xs font-bold">{{ $movie->year }} • {{ $runtime ?: 'N/A' }} min</span>
                    @if($movie->rating_age !== null && file_exists(public_path('img/fsk/fsk-' . $movie->rating_age . '.svg')))
                        <img src="{{ asset('img/fsk/fsk-' . $movie->rating_age . '.svg') }}"
                             alt="FSK {{ $movie->rating_age }}"
                             class="h-7 w-auto drop-shadow-md ml-2">
                    @endif
                </div>

                <h1 class="text-6xl md:text-8xl font-black text-white tracking-tighter mb-8 drop-shadow-2xl">
                    {{ $movie->title }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 mb-12">
                    <button @click="showTrailer = true" 
                            @if(!$movie->trailer_url) disabled @endif
                            class="px-10 py-5 bg-white text-black rounded-2xl font-black text-xl flex items-center gap-3 hover:scale-105 transition-all shadow-2xl active:scale-95 shadow-white/10 {{ !$movie->trailer_url ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <i class="bi bi-play-fill text-3xl"></i>
                        {{ $movie->trailer_url ? __('Watch Trailer') : __('No Trailer') }}
                    </button>
                </div>

                <div class="glass p-8 md:p-12 rounded-[3rem] border border-white/10 backdrop-blur-3xl shadow-2xl relative overflow-hidden mb-12">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <i class="bi bi-quote text-9xl"></i>
                    </div>
                    <h3 class="text-2xl font-black text-white mb-6 uppercase tracking-tight flex items-center gap-4">
                        {{ __('Storyline') }}
                        <div class="h-1 w-12 bg-red-600 rounded-full"></div>
                    </h3>
                    <div class="text-xl text-white/80 leading-relaxed font-medium">
                        {!! \App\Services\ShortcodeService::parse($movie->overview) ?: __('No description available for this cinematic piece.') !!}
                    </div>
                    
                    <div class="mt-12 grid grid-cols-2 md:grid-cols-3 gap-8 border-t border-white/5 pt-8">
                         @php
                            $director = $movie->director;
                            if (!$director && !empty($movie->tmdb_json['credits']['crew'])) {
                                foreach ($movie->tmdb_json['credits']['crew'] as $person) {
                                    if ($person['job'] === 'Director') {
                                        $director = $person['name'];
                                        break;
                                    }
                                }
                            }
                         @endphp

                         @if($director)
                         <div>
                            <p class="text-white/40 text-[10px] font-black uppercase tracking-widest mb-1">{{ __('Director') }}</p>
                            <p class="text-white font-bold">{{ $director }}</p>
                         </div>
                         @endif
                         @php
                            $genre = $movie->genre;
                            if (!$genre && !empty($movie->tmdb_json['genres'])) {
                                $genre = implode(', ', array_column($movie->tmdb_json['genres'], 'name'));
                            }
                         @endphp
                         @if($genre)
                         <div>
                            <p class="text-white/40 text-[10px] font-black uppercase tracking-widest mb-1">{{ __('Genre') }}</p>
                            <p class="text-white font-bold">{{ $genre }}</p>
                         </div>
                         @endif
                         
                         <div>
                            <p class="text-white/40 text-[10px] font-black uppercase tracking-widest mb-1">{{ __('Added') }}</p>
                            <p class="text-white font-bold">{{ $movie->created_at->format('d.m.Y') }}</p>
                         </div>
                    </div>
                </div>

                {{-- Cast Carousel --}}
                @if($movie->actors->isNotEmpty())
                <div class="animate-in slide-in-from-bottom duration-1000 delay-300" 
                     x-data="{ 
                         scrollAmount: 400,
                         scroll(dir) { 
                             this.$refs.castContainer.scrollBy({ left: dir * this.scrollAmount, behavior: 'smooth' }); 
                         } 
                     }">
                    <div class="flex items-center justify-between mb-8 pl-4 pr-4">
                        <h3 class="text-xl font-black text-white uppercase tracking-tight flex items-center gap-4">
                            {{ __('Cast Members') }}
                            <div class="h-1 w-8 bg-blue-600 rounded-full"></div>
                        </h3>
                        
                        <!-- Navigation Arrows -->
                        <div class="flex items-center gap-3">
                            <button @click="scroll(-1)" class="w-12 h-12 rounded-full border border-white/10 bg-white/5 hover:bg-white/10 hover:border-red-600/50 flex items-center justify-center transition-all active:scale-90 text-white/40 hover:text-white">
                                <i class="bi bi-chevron-left text-xl"></i>
                            </button>
                            <button @click="scroll(1)" class="w-12 h-12 rounded-full border border-white/10 bg-white/5 hover:bg-white/10 hover:border-red-600/50 flex items-center justify-center transition-all active:scale-90 text-white/40 hover:text-white">
                                <i class="bi bi-chevron-right text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex gap-6 overflow-x-auto no-scrollbar pb-8 px-4 scroll-smooth" x-ref="castContainer">
                        @foreach($movie->actors as $actor)
                        <a href="{{ route('actors.show', $actor) }}" class="group min-w-[140px] md:min-w-[170px] text-center">
                            <div class="w-full aspect-square rounded-full overflow-hidden border-2 border-white/10 group-hover:border-red-600 group-hover:shadow-[0_0_30px_rgba(220,38,38,0.3)] transition-all mb-4 relative shadow-2xl">
                                <img src="{{ $actor->profile_url ?: asset('img/default-actor.png') }}" alt="{{ $actor->full_name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                <div class="absolute inset-0 bg-gradient-to-t from-red-600/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </div>
                            <p class="text-sm font-black text-white truncate px-2 group-hover:text-red-400 transition-colors uppercase tracking-tight italic">{{ $actor->full_name }}</p>
                            <p class="text-[10px] font-black text-white/30 truncate uppercase italic tracking-widest mt-1">{{ $actor->pivot->role ?? '' }}</p>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Right column: Poster --}}
            <div class="lg:col-span-4 hidden lg:block animate-in zoom-in duration-1000">
                 <div class="sticky top-32">
                     <div class="relative group">
                         <div class="absolute -inset-4 bg-gradient-to-r from-red-600 to-blue-600 rounded-[3.5rem] blur-2xl opacity-20 group-hover:opacity-40 transition-opacity duration-700"></div>
                         <div class="relative rounded-[3rem] overflow-hidden border border-white/10 shadow-2xl">
                             <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-auto shadow-2xl">
                         </div>
                     </div>

                     {{-- Bonus Details Card --}}
                     <div class="mt-8 glass p-6 rounded-3xl border border-white/10 flex items-center justify-between">
                         <div class="text-center flex-1 border-r border-white/5">
                             <p class="text-[10px] font-black text-white/40 uppercase mb-1">{{ __('Runtime') }}</p>
                             <p class="text-sm font-black text-white">{{ $movie->runtime ?: 'N/A' }}m</p>
                         </div>
                         <div class="text-center flex-1 border-r border-white/5">
                             <p class="text-[10px] font-black text-white/40 uppercase mb-1">{{ __('Format') }}</p>
                             <p class="text-sm font-black text-white">4K UHD</p> {{-- Conceptual placeholder --}}
                         </div>
                         <div class="text-center flex-1">
                             <p class="text-[10px] font-black text-white/40 uppercase mb-1">{{ __('Language') }}</p>
                             <p class="text-sm font-black text-white">DE/EN</p> {{-- Conceptual placeholder --}}
                         </div>
                     </div>
                 </div>
            </div>
        </div>
    {{-- Trailer Modal --}}
    <template x-if="showTrailer && youtubeId">
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-12">
            <div class="absolute inset-0 bg-black/90 backdrop-blur-2xl" @click="showTrailer = false"></div>
            
            <div class="relative w-full max-w-6xl aspect-video rounded-[2rem] overflow-hidden shadow-2xl border border-white/10 animate-in zoom-in duration-300">
                <button @click="showTrailer = false" class="absolute top-6 right-6 z-20 w-12 h-12 rounded-full bg-black/50 backdrop-blur-md border border-white/10 text-white flex items-center justify-center hover:bg-white/10 transition-all">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>

                <iframe
                    :src="'https://www.youtube-nocookie.com/embed/' + youtubeId + '?autoplay=1&mute=0&rel=0'"
                    class="w-full h-full border-0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>
        </div>
    </template>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
