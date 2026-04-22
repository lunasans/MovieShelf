

<div class="{{ $layoutMode === 'streaming' ? 'streaming-movie-details animate-in fade-in slide-in-from-bottom-8 duration-700' : 'animate-in fade-in slide-in-from-right-4 duration-500' }}"
     x-data="{
        showTrailer: false,
        isWatched: {{ Auth::check() && Auth::user()->watchedMovies()->where('movie_id', $movie->id)->exists() ? 'true' : 'false' }},
        watchedCount: {{ $movie->watchedByUsers()->count() }},
        isWishlisted: {{ Auth::check() && \App\Models\UserWishlist::where('user_id', Auth::id())->where('movie_id', $movie->id)->exists() ? 'true' : 'false' }},
        userRating: {{ Auth::check() ? (\App\Models\UserRating::where('user_id', Auth::id())->where('movie_id', $movie->id)->value('rating') ?? 0) : 0 }},
        hoverRating: 0,
        avgRating: {{ round(\App\Models\UserRating::where('movie_id', $movie->id)->avg('rating') ?? 0, 1) }},
        ratingCount: {{ \App\Models\UserRating::where('movie_id', $movie->id)->count() }},
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
                       headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' }
                   });
                   const data = await response.json();
                   if (data.watched !== undefined) {
                       this.isWatched = data.watched;
                       this.watchedCount = data.count;
                       window.dispatchEvent(new CustomEvent('movie-watched-updated', { detail: { movieId: {{ $movie->id }}, watched: data.watched } }));
                   }
               } catch (e) { console.error('Toggle watched failed', e); }
           @else
               window.location.href = '{{ route('login') }}';
           @endif
        },
        async toggleWishlist() {
           @if(Auth::check())
               try {
                   const response = await fetch('{{ route('movies.wishlist.toggle', $movie) }}', {
                       method: 'POST',
                       headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' }
                   });
                   const data = await response.json();
                   if (data.wishlisted !== undefined) this.isWishlisted = data.wishlisted;
               } catch (e) { console.error('Toggle wishlist failed', e); }
           @else
               window.location.href = '{{ route('login') }}';
           @endif
        },
        async setRating(n) {
           @if(Auth::check())
               try {
                   const response = await fetch('{{ route('movies.rate', $movie) }}', {
                       method: 'POST',
                       headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                       body: JSON.stringify({ rating: n })
                   });
                   const data = await response.json();
                   this.userRating = data.rating;
                   this.avgRating = data.avg;
                   this.ratingCount = data.count;
               } catch (e) { console.error('Rating failed', e); }
           @else
               window.location.href = '{{ route('login') }}';
           @endif
        }
     }">
    @if($layoutMode !== 'streaming')
        <!-- Layout Alignment Spacer (Matches Dashboard Tabs) -->
        <div class="h-[46px] mb-8"></div>
    
        <!-- Title Section (Matches List Header h-10) -->
        <div class="h-10 flex items-center mb-8 px-2">
            <h2 class="text-2xl font-black text-white flex items-center gap-4 tracking-tighter uppercase">
                <div class="h-10 w-2 bg-blue-500 rounded-full shadow-[0_0_15px_rgba(59,130,246,0.5)]"></div>
                {{ $movie->title }}
            </h2>
        </div>
    @endif

    <!-- Header Area (Backdrop & Poster) -->
    <div class="relative {{ $layoutMode === 'streaming' ? 'rounded-[3rem] min-h-[60vh] flex items-center' : 'rounded-[2.5rem] aspect-[21/9] flex flex-col justify-end' }} overflow-hidden glass-strong mb-10 group shadow-2xl border border-white/5 max-h-[1200px]">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-[#0c0c0e] flex items-center justify-center">
             @if($movie->backdrop_url)
                <img src="{{ $movie->backdrop_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover {{ $layoutMode === 'streaming' ? 'opacity-40' : 'opacity-60' }}">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0c0c0e] via-[#0c0c0e]/40 to-transparent"></div>
                @if($layoutMode === 'streaming')
                    <div class="absolute inset-x-0 bottom-0 h-64 bg-gradient-to-t from-[#0c0c0e] to-transparent"></div>
                @endif
            @elseif($movie->cover_url)
                <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-30">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0c0c0e] via-[#0c0c0e]/20 to-transparent"></div>
            @endif
        </div>

        <!-- Content Overlay -->
        <div class="relative z-30 w-full px-12 md:px-20 py-20">
            <div class="flex flex-col md:flex-row items-center md:items-end gap-12">
                <!-- Poster Overlay -->
                <div class="relative {{ $layoutMode === 'streaming' ? 'w-48 md:w-64' : 'w-32 md:w-40' }} aspect-[2/3] rounded-[2rem] overflow-hidden shadow-[0_0_60px_rgba(0,0,0,0.8)] border border-white/10 group/poster shrink-0 transform hover:scale-[1.02] hover:rotate-1 transition-all duration-700">
                    @if($movie->cover_url)
                        <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover">
                        @if($movie->trailer_url)
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover/poster:opacity-100 transition-opacity cursor-pointer" @click="showTrailer = true">
                                <img src="{{ asset('img/play.png') }}" alt="Play"
                                     class="w-20 h-20 scale-75 group-hover/poster:scale-100 transition-all duration-500 drop-shadow-[0_0_20px_rgba(220,38,38,0.6)]">
                            </div>
                        @endif
                    @endif
                </div>

                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mb-6">
                        <span class="px-6 py-2 bg-blue-600 rounded-2xl text-[10px] font-black tracking-[0.3em] uppercase shadow-xl shadow-blue-500/20">
                            {{ $movie->collection_type }}
                        </span>
                        <span class="px-6 py-2 bg-white/5 backdrop-blur-xl rounded-2xl text-[10px] font-black tracking-[0.3em] uppercase border border-white/10 italic">
                            {{ $movie->year }}
                        </span>
                        @if($movie->rating_age !== null && file_exists(public_path('img/fsk/fsk-' . $movie->rating_age . '.svg')))
                            <img src="{{ asset('img/fsk/fsk-' . $movie->rating_age . '.svg') }}" alt="FSK {{ $movie->rating_age }}" class="h-10 w-auto drop-shadow-2xl">
                        @endif
                    </div>

                    <h1 class="{{ $layoutMode === 'streaming' ? 'text-5xl md:text-8xl font-black' : 'text-3xl md:text-5xl font-bold' }} text-white leading-[0.9] tracking-tighter mb-8 uppercase italic drop-shadow-2xl">
                        {{ $movie->title }}
                    </h1>

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-12 text-white/40 text-xs font-bold uppercase tracking-[0.2em] italic">
                        @if($movie->runtime)
                            <div class="flex items-center gap-3">
                                <i class="bi bi-clock text-blue-500 text-lg"></i>
                                <span>{{ $movie->runtime }} Min.</span>
                            </div>
                        @endif
                        @if($movie->rating)
                            <div class="flex items-center gap-3">
                                <i class="bi bi-star-fill text-yellow-500 text-lg"></i>
                                <span class="text-white">{{ number_format($movie->rating, 1) }} / 10</span>
                            </div>
                        @endif
                        @if($movie->director)
                            <div class="flex items-center gap-3">
                                <i class="bi bi-megaphone text-purple-500 text-lg"></i>
                                <span class="text-white">{{ $movie->director }}</span>
                            </div>
                        @endif
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
            <div class="glass p-6 md:p-12 rounded-[3rem] border-white/5 mb-12">
                <h3 class="text-xs font-black text-white/20 uppercase tracking-[0.5em] italic mb-8 flex items-center gap-6">
                    {{ __('Biografie') }}
                    <div class="h-px bg-gradient-to-r from-white/10 via-white/5 to-transparent flex-1"></div>
                </h3>
                <div class="relative">
                    <div class="absolute -left-8 top-0 bottom-0 w-1 bg-blue-600 rounded-full shadow-[0_0_20px_rgba(59,130,246,0.5)] opacity-50"></div>
                    <div class="text-white/80 leading-relaxed text-xl lg:text-2xl font-medium prose-movie max-w-5xl tracking-tight" style="white-space: pre-wrap !important;">
                        {!! \App\Services\ShortcodeService::parse($movie->overview) !!}
                    </div>
                </div>
            </div>
        @endif

        <!-- Actors -->
        <div class="mb-12" 
             x-data="{ 
                 scrollAmount: 600,
                 scroll(dir) { 
                     this.$refs.castContainer.scrollBy({ left: dir * this.scrollAmount, behavior: 'smooth' }); 
                 } 
             }">
            <div class="flex items-center justify-between mb-8 px-2">
                <h3 class="text-xs font-black text-white/20 uppercase tracking-[0.5em] italic">
                    {{ __('Besetzung') }}
                </h3>
                
                <!-- Navigation Arrows -->
                @if($movie->actors->count() > 4)
                    <div class="flex items-center gap-2">
                        <button @click="scroll(-1)" class="w-12 h-12 rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-blue-500/50 flex items-center justify-center transition-all active:scale-90 text-gray-500 hover:text-white">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button @click="scroll(1)" class="w-12 h-12 rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-blue-500/50 flex items-center justify-center transition-all active:scale-90 text-gray-500 hover:text-white">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                @endif
            </div>

            <div class="flex gap-6 overflow-x-auto pb-8 no-scrollbar scroll-smooth" x-ref="castContainer">
                @forelse($movie->actors as $actor)
                    <div @click="fetchActor({{ $actor->id }})" 
                         class="group/actor cursor-pointer shrink-0 w-[240px] animate-in zoom-in duration-700">
                        <div class="relative aspect-square rounded-[2rem] overflow-hidden glass-streaming border border-white/5 transition-all duration-700 hover:scale-[1.08] hover:border-blue-500/50 shadow-2xl mb-4">
                            @if($actor->profile_url)
                                <img src="{{ $actor->profile_url }}" alt="{{ $actor->full_name }}" class="w-full h-full object-cover duration-1000 group-hover/actor:scale-110">
                            @else
                                <div class="w-full h-full bg-[#1a1a1f] flex items-center justify-center">
                                    <i class="bi bi-person text-white/5 text-6xl"></i>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0c0c0e] via-transparent to-transparent opacity-60"></div>
                        </div>
                        <div class="px-2">
                             <div class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-1 italic">{{ $actor->pivot->role ?: __('Darsteller') }}</div>
                             <h4 class="text-white font-black text-sm leading-none uppercase tracking-tighter truncate">{{ $actor->full_name }}</h4>
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
                        <div class="rounded-2xl border border-white/5 bg-white/5 overflow-hidden transition-colors duration-300"
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
                                    <div class="p-4 text-[11px] text-gray-400 border-b border-white/5 italic prose-movie">
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
                                                <p class="text-[10px] text-gray-500 leading-relaxed ml-10 line-clamp-2 prose-movie">
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
                        <img src="{{ asset('img/play.png') }}" alt="Play"
                             class="w-24 h-24 transform group-hover/player:scale-110 transition-transform duration-300 drop-shadow-[0_0_30px_rgba(220,38,38,0.5)]">
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

        <!-- Star Rating Row -->
        <div class="flex items-center justify-between mt-6 px-1">
            <div class="flex items-center gap-3">
                <span class="text-[10px] font-black text-white/30 uppercase tracking-widest">{{ __('Deine Bewertung') }}</span>
                <div class="flex items-center gap-1">
                    @for($s = 1; $s <= 5; $s++)
                    <button @click="setRating({{ $s }})"
                            @mouseenter="hoverRating = {{ $s }}"
                            @mouseleave="hoverRating = 0"
                            class="text-2xl transition-all active:scale-90"
                            :class="(hoverRating || userRating) >= {{ $s }} ? 'text-amber-400' : 'text-white/10'">
                        <i class="bi bi-star-fill"></i>
                    </button>
                    @endfor
                </div>
                <span class="text-xs text-white/20 font-medium" x-show="userRating > 0" x-text="userRating + '/5'"></span>
            </div>
            <div class="flex items-center gap-2 text-[10px] text-white/20 font-bold" x-show="ratingCount > 0">
                <i class="bi bi-people text-sm"></i>
                <span x-text="ratingCount + ' ' + '{{ __('Bewertungen') }}' + ' · Ø ' + avgRating"></span>
            </div>
        </div>

        <!-- Other Actions (Watched + Wishlist) -->
        <div class="flex items-center justify-end gap-3 mt-4">
            <!-- Watchlist -->
            <button @click="toggleWishlist()"
                    class="h-14 px-6 bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl flex items-center gap-3 transition-all group shadow-lg"
                    :class="isWishlisted ? 'border-rose-500/50 bg-rose-500/10' : ''">
                <span class="text-xs font-bold uppercase tracking-widest transition-colors"
                      :class="isWishlisted ? 'text-rose-400' : 'text-gray-400 group-hover:text-white'">
                    <span x-show="isWishlisted">{{ __('Auf Merkliste') }}</span>
                    <span x-show="!isWishlisted">{{ __('Merken') }}</span>
                </span>
                <i class="bi text-xl transition-colors"
                   :class="isWishlisted ? 'bi-bookmark-heart-fill text-rose-400' : 'bi-bookmark-heart text-gray-400 group-hover:text-rose-400'"></i>
            </button>

            <!-- Watched -->
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