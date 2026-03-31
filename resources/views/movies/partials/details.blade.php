<style>
    .prose-movie p, .prose-movie div { margin-bottom: 1rem; display: block; }
    .prose-movie p:last-child, .prose-movie div:last-child { margin-bottom: 0; }
    .prose-movie h1, .prose-movie h2, .prose-movie h3, .prose-movie h4 { 
        margin-top: 1.5rem; 
        margin-bottom: 0.75rem; 
        font-weight: bold; 
        display: block;
        color: white;
    }
    .prose-movie h1:first-child, .prose-movie h2:first-child, .prose-movie p:first-child { margin-top: 0; }
    .prose-movie ul, .prose-movie ol { margin-bottom: 1rem; padding-left: 1.5rem; display: block; }
    .prose-movie ul { list-style-type: disc; }
    .prose-movie ol { list-style-type: decimal; }
    .prose-movie li { margin-bottom: 0.25rem; }
    .prose-movie strong { font-weight: bold; color: white; }
    .prose-movie u { text-decoration: underline; text-underline-offset: 4px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div class="animate-in fade-in slide-in-from-right-4 duration-500"
     x-data="{
        showTrailer: false,
        isWatched: {{ Auth::check() && Auth::user()->watchedMovies()->where('movie_id', $movie->id)->exists() ? 'true' : 'false' }},
        watchedCount: {{ $movie->watchedByUsers()->count() }},
        get youtubeId() {
            const url = '{{ $movie->trailer_url }}';
            const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:v\/|u\/\w\/|embed\/|watch\?v=))([^#\&\?]*)/);
            return (match && match[1].length == 11) ? match[1] : null;
        },
        async toggleWatched() {
           @if(Auth::check())
               try {
                   const response = await fetch('{{ route('movies.watched.toggle', $movie) }}', {
                       method: 'POST',
                       headers: {
                           'X-CSRF-TOKEN': '{{ csrf_token() }}',
                           'Content-Type': 'application/json',
                           'Accept': 'application/json'
                       }                   });
                   const data = await response.json();
                   if (data.watched !== undefined) {
                       this.isWatched = data.watched;
                       this.watchedCount = data.count;
                       // Dispatch event for dashboard cards
                       window.dispatchEvent(new CustomEvent('movie-watched-updated', {
                           detail: { movieId: {{ $movie->id }}, watched: data.watched }
                       }));
                   }
               } catch (e) {
                   console.error('Toggle watched failed', e);
               }           @else
               window.location.href = '{{ route('login') }}';
           @endif
        }     }">
    <!-- Layout Alignment Spacer (Matches Dashboard Tabs) -->
    <div class="h-[46px] mb-8"></div>

    <!-- Title Section (Matches List Header h-10) -->
    <div class="h-10 flex items-center mb-8 px-2">
        <h2 class="text-2xl font-black text-white flex items-center gap-4 tracking-tighter uppercase">
            <div class="h-10 w-2 bg-blue-500 rounded-full shadow-[0_0_15px_rgba(59,130,246,0.5)]"></div>
            {{ $movie->title }}
        </h2>
    </div>

    <!-- Header Area (Backdrop & Poster) -->
    <div class="relative rounded-[2.5rem] overflow-hidden glass-strong mb-10 aspect-[21/9] group shadow-2xl border border-white/5 max-h-[300px] md:max-h-[450px]">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-950 flex items-center justify-center">
             @if($movie->backdrop_url)
                <img src="{{ $movie->backdrop_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover opacity-60">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/40 to-transparent"></div>
            @elseif($movie->cover_url)
                <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-30">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent"></div>
            @else
                <i class="bi bi-film text-6xl text-gray-800"></i>
            @endif
        </div>

        <!-- Content Overlay (Poster & Meta) -->
        <div class="absolute inset-0 z-20 flex flex-col justify-end p-8 md:p-12">
            <div class="flex items-end gap-8 relative z-30">
                <!-- Poster Overlay -->
                <div class="relative w-32 md:w-40 aspect-[2/3] rounded-2xl overflow-hidden glass border-2 border-white/10 shadow-2xl group/poster shrink-0">
                    @if($movie->cover_url)
                        <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover">
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

                <div class="pb-2 flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="px-3 py-1 bg-blue-600/80 backdrop-blur-md rounded-lg text-[10px] font-black tracking-widest uppercase border border-white/20 shadow-lg">
                            {{ $movie->collection_type }}
                        </span>
                        <span class="px-3 py-1 bg-white/10 backdrop-blur-md rounded-lg text-[10px] font-black tracking-widest uppercase border border-white/10 shadow-lg">
                            {{ $movie->year }}
                        </span>
                        @if($movie->rating_age !== null && file_exists(public_path('img/fsk/fsk-' . $movie->rating_age . '.svg')))
                            <img src="{{ asset('img/fsk/fsk-' . $movie->rating_age . '.svg') }}"
                                 alt="FSK {{ $movie->rating_age }}"
                                 class="h-8 w-auto drop-shadow-lg">
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-gray-300 text-sm font-medium">
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
                                <span class="text-white truncate max-w-[200px]">{{ $movie->director }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <i class="bi bi-calendar-plus text-emerald-400"></i>
                            <span class="text-white">{{ $movie->created_at->format('d.m.Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Movies -->
    @if(isset($similarMovies) && $similarMovies->count() > 0)
        <div class="mb-8 pl-2">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="bi bi-collection-play text-blue-400"></i>
                {{ __('Ähnliche Filme') }}
            </h3>
            <div class="flex gap-4 overflow-x-auto pb-4 snap-x snap-mandatory hide-scrollbar">
                @foreach($similarMovies as $similar)
                    <div @click="fetchDetails({{ $similar->id }}, '{{ $similar->backdrop_url }}')" class="w-24 shrink-0 snap-start cursor-pointer group/similar">
                        <div class="w-full aspect-[2/3] bg-gray-900 rounded-xl overflow-hidden glass border border-white/5 group-hover/similar:border-blue-500/30 transition-all shadow-lg mb-2 relative">
                            @if($similar->cover_url)
                                <img src="{{ $similar->cover_url }}" alt="{{ $similar->title }}" class="w-full h-full object-cover group-hover/similar:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="bi bi-film text-gray-700 text-2xl"></i>
                                </div>
                            @endif
                        </div>
                        <div class="text-[10px] font-bold text-gray-400 truncate group-hover/similar:text-blue-400 transition-colors">
                            {{ $similar->title }}
                        </div>
                        <div class="text-[9px] text-gray-600 uppercase">{{ $similar->year }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="space-y-8">
        <!-- Overview -->
        @if($movie->overview)
            <div class="glass p-6 rounded-3xl border-white/5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2 underline decoration-blue-500/50 underline-offset-8">
                    <i class="bi bi-text-left text-blue-400"></i>
                    {{ __('Beschreibung') }}
                </h3>
                <div class="text-gray-300 leading-relaxed text-sm prose-movie">
                    {!! \App\Services\ShortcodeService::parse($movie->overview) !!}
                </div>
            </div>
        @endif

        <!-- Actors -->
        <div class="glass p-6 rounded-3xl border-white/5 w-full max-w-full overflow-hidden" 
             x-data="{ 
                 scrollAmount: 400,
                 scroll(dir) { 
                     this.$refs.castContainer.scrollBy({ left: dir * this.scrollAmount, behavior: 'smooth' }); 
                 } 
             }">
            <div class="flex items-center justify-between mb-4 px-2">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2 underline decoration-blue-500/50 underline-offset-8">
                    <i class="bi bi-people text-blue-400"></i>
                    {{ __('Besetzung') }}
                </h3>
                
                <!-- Navigation Arrows -->
                <div class="flex items-center gap-2">
                    <button @click="scroll(-1)" class="w-8 h-8 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 hover:border-blue-500/50 flex items-center justify-center transition-all active:scale-90 text-gray-500 hover:text-white">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button @click="scroll(1)" class="w-8 h-8 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 hover:border-blue-500/50 flex items-center justify-center transition-all active:scale-90 text-gray-500 hover:text-white">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="flex gap-4 overflow-x-auto pb-4 no-scrollbar scroll-smooth" x-ref="castContainer">
                @forelse($movie->actors as $actor)
                    <div @click="fetchActor({{ $actor->id }})" class="flex items-center gap-3 group/actor cursor-pointer shrink-0 bg-white/5 p-3 rounded-2xl border border-white/10 hover:border-blue-500/30 transition-all w-[220px] min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center flex-shrink-0 group-hover/actor:border-blue-500/50 transition-colors shadow-lg overflow-hidden">
                            @if($actor->profile_url)
                                <img src="{{ $actor->profile_url }}" alt="{{ $actor->full_name }}" class="w-full h-full object-cover group-hover/actor:scale-110 transition-transform">
                            @else
                                <i class="bi bi-person text-lg text-gray-500 group-hover/actor:text-blue-400 transition-colors"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-white truncate group-hover/actor:text-blue-400 transition-colors">
                                {{ $actor->full_name }}
                            </div>
                            <div class="text-[10px] text-gray-500 truncate italic font-medium uppercase tracking-tighter">
                                {{ $actor->pivot->role ?: __('Darsteller') }}
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
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2 underline decoration-blue-500/50 underline-offset-8">
                    <i class="bi bi-collection-play text-blue-400"></i>
                    {{ __('Filme in diesem Set') }} ({{ $movie->boxsetChildren->count() }})                </h3>
                <div class="space-y-3">
                    @foreach($movie->boxsetChildren as $child)
                        <div @click="fetchDetails({{ $child->id }})" class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/5 transition-colors cursor-pointer group/child">
                            <div class="w-8 h-12 bg-gray-800 rounded-lg flex items-center justify-center flex-shrink-0 border border-white/5 group-hover/child:border-blue-500/30 overflow-hidden">
                                @if($child->cover_url)
                                    <img src="{{ $child->cover_url }}" alt="{{ $child->title }}" class="w-full h-full object-cover">
                                @else
                                    <i class="bi bi-film text-gray-700"></i>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-white truncate group-hover/child:text-blue-400 transition-colors">{{ $child->title }}</div>
                                <div class="text-[10px] text-gray-500 uppercase">{{ $child->year }} • {{ $child->collection_type }}</div>
                            </div>
                            <i class="bi bi-chevron-right text-gray-700 text-xs mr-2 group-hover/child:text-blue-400 transition-colors"></i>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Seasons & Episodes (for Series) -->
        @if($movie->collection_type === 'Serie' && $movie->seasons->count() > 0)
            <div class="glass p-6 rounded-3xl border-white/5" x-data="{ activeSeason: null }">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2 underline decoration-blue-500/50 underline-offset-8">
                    <i class="bi bi-layers text-blue-400"></i>
                    {{ __('Staffeln & Episoden') }}
                </h3>
                <div class="space-y-4">
                    @foreach($movie->seasons->sortBy('season_number') as $season)
                        <div class="rounded-2xl border border-white/5 bg-white/5 overflow-hidden transition-all duration-300"
                             :class="activeSeason === {{ $season->id }} ? 'border-blue-500/30 bg-white/10' : 'hover:border-white/10'">
                            <button @click="activeSeason = activeSeason === {{ $season->id }} ? null : {{ $season->id }}"
                                    class="w-full flex items-center justify-between p-4 text-left group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-blue-600/20 rounded-xl flex items-center justify-center text-blue-400 font-black">
                                        {{ $season->season_number }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-white group-hover:text-blue-400 transition-colors">{{ $season->title ?: __('Staffel ' . $season->season_number) }}</div>
                                        <div class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">{{ $season->episodes->count() }} {{ __('Folgen') }}</div>
                                    </div>
                                </div>
                                <i class="bi text-gray-500 transition-transform duration-300"
                                   :class="activeSeason === {{ $season->id }} ? 'bi-chevron-up rotate-180 text-blue-400' : 'bi-chevron-down'"></i>
                            </button>
                            <div x-show="activeSeason === {{ $season->id }}"
                                 x-collapse
                                 class="border-t border-white/5 bg-black/20">
                                @if($season->overview)
                                    <div class="p-4 text-[11px] text-gray-400 border-b border-white/5 italic">
                                        {!! \App\Services\ShortcodeService::parse($season->overview) !!}
                                    </div>
                                @endif
                                <div class="divide-y divide-white/5">
                                    @foreach($season->episodes->sortBy('episode_number') as $episode)
                                        <div class="p-4 hover:bg-white/5 transition-colors">
                                            <div class="flex items-center gap-4 mb-1">
                                                <span class="text-[10px] font-black text-blue-400/50 w-6">E{{ $episode->episode_number }}</span>
                                                <h4 class="text-xs font-bold text-white">{{ $episode->title }}</h4>
                                            </div>
                                            @if($episode->overview)
                                                <p class="text-[10px] text-gray-500 leading-relaxed ml-10 line-clamp-2">
                                                    {!! \App\Services\ShortcodeService::parse($episode->overview) !!}
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Trailer & Actions -->
    <div class="mt-8">
        @if($movie->trailer_url)
            <div class="relative w-full max-w-4xl mx-auto aspect-video rounded-3xl overflow-hidden glass border-white/5 shadow-2xl group/player bg-black mb-4">
                <!-- Thumbnail Layer -->
                <div x-show="!showTrailer"
                     class="absolute inset-0 cursor-pointer"
                     @click="showTrailer = true">
                    @if($movie->backdrop_url)
                        <img src="{{ $movie->backdrop_url }}" alt="Trailer Thumbnail" class="w-full h-full object-cover opacity-80 group-hover/player:opacity-100 transition-opacity duration-500 group-hover/player:scale-105">
                    @elseif($movie->cover_url)
                        <img src="{{ $movie->cover_url }}" alt="Trailer Thumbnail" class="w-full h-full object-cover blur-sm opacity-60 group-hover/player:opacity-80 transition-opacity duration-500 group-hover/player:scale-105">
                    @else
                        <div class="w-full h-full bg-gray-900 flex items-center justify-center">
                            <i class="bi bi-film text-gray-700 text-6xl"></i>
                        </div>
                    @endif
                    <!-- Play Button Overlay -->
                    <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover/player:bg-transparent transition-colors duration-500">
                        <div class="w-16 h-16 bg-rose-600/90 backdrop-blur-md rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(225,29,72,0.4)] transform group-hover/player:scale-110 transition-transform duration-300">
                            <i class="bi bi-play-fill text-3xl text-white ml-1"></i>
                        </div>
                    </div>
                    <!-- Title Overlay -->
                    <div class="absolute bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-black/80 via-black/40 to-transparent">
                        <div class="text-white font-bold text-sm tracking-wider uppercase flex items-center gap-2">
                            <i class="bi bi-youtube text-rose-500 text-lg"></i>
                            {{ __('Offizieller Trailer') }}
                        </div>
                    </div>
                </div>
                <!-- Video Player Layer -->
                <template x-if="showTrailer">
                    <iframe
                        :src="'https://www.youtube-nocookie.com/embed/' + youtubeId + '?autoplay=1&mute=0&rel=0'"
                        :title="'Trailer for ' + '{{ $movie->title }}'"
                        class="w-full h-full absolute inset-0 z-10"
                        style="border: 0;"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </template>
            </div>
        @else
            <div class="w-full bg-white/5 text-gray-400 p-6 rounded-3xl font-bold text-sm border border-white/10 flex items-center justify-center gap-3 mb-4">
                <i class="bi bi-slash-circle text-xl text-gray-500"></i>
                {{ __('Kein Trailer verfügbar') }}
            </div>
        @endif

        <!-- Other Actions (Watched Toggle) -->
        <div class="flex justify-end">
            <button @click="toggleWatched()"
                    class="h-14 px-6 bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl flex items-center gap-3 transition-all group shadow-lg"
                    :class="isWatched ? 'border-blue-500/50 bg-blue-500/10' : ''">
                <span class="text-xs font-bold uppercase tracking-widest transition-colors"
                      :class="isWatched ? 'text-blue-400' : 'text-gray-400 group-hover:text-white'">
                    <span x-show="isWatched">{{ __('Gesehen') }}</span>
                    <span x-show="!isWatched">{{ __('Als gesehen markieren') }}</span>
                </span>
                <i class="bi text-xl transition-colors"
                   :class="isWatched ? 'bi-eye-fill text-blue-400' : 'bi-eye text-gray-400 group-hover:text-blue-400'"></i>
            </button>
        </div>
    </div>
</div>