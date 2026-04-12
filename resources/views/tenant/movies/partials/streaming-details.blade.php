<style>
    .prose-movie {
        white-space: pre-wrap !important;
        word-wrap: break-word;
        display: block !important;
    }
    .prose-movie p, .prose-movie div { 
        margin-bottom: 1.5rem !important; 
        display: block !important; 
        line-height: 1.8;
    }
    .prose-movie p:last-child, .prose-movie div:last-child { margin-bottom: 0 !important; }
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
    .prose-movie .ql-align-center { text-align: center; }
    .prose-movie .ql-align-right { text-align: right; }
    .prose-movie .ql-align-justify { text-align: justify; }
    .prose-movie .ql-size-small { font-size: 0.75em; }
    .prose-movie .ql-size-large { font-size: 1.5em; }
    .prose-movie .ql-size-huge { font-size: 2.5em; }
    .prose-movie blockquote {
        border-left: 4px solid #ef4444;
        padding-left: 1.5rem;
        margin-left: 0;
        font-style: italic;
        color: rgba(255, 255, 255, 0.6);
    }
</style>

<div class="relative min-h-screen text-white overflow-x-hidden"
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
                       headers: {
                           'X-CSRF-TOKEN': '{{ csrf_token() }}',
                           'Content-Type': 'application/json',
                           'Accept': 'application/json'
                       }
                   });
                   const data = await response.json();
                   if (data.watched !== undefined) {
                       this.isWatched = data.watched;
                       this.watchedCount = data.count;
                       window.dispatchEvent(new CustomEvent('movie-watched-updated', {
                           detail: { movieId: {{ $movie->id }}, watched: data.watched }
                       }));
                   }
               } catch (e) {
                   console.error('Toggle watched failed', e);
               }
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
     }"
     x-init="
        window.dispatchEvent(new CustomEvent('set-active-movie', { 
            detail: { 
                title: '{{ addslashes($movie->title) }}',
                cover: '{{ $movie->cover_url }}'
            } 
        }));
        window.addEventListener('beforeunload', () => {
            window.dispatchEvent(new CustomEvent('toggle-movie-title', { detail: { show: false } }));
        });
     ">
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
            <a href="{{ route('dashboard') }}" 
               class="group inline-flex items-center gap-4 text-white/60 hover:text-white transition-all">
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

                <h1 class="text-6xl md:text-8xl font-black text-white tracking-tighter mb-8 drop-shadow-2xl"
                    x-intersect:leave="window.dispatchEvent(new CustomEvent('toggle-movie-title', { detail: { show: true } }))"
                    x-intersect:enter="window.dispatchEvent(new CustomEvent('toggle-movie-title', { detail: { show: false } }))">
                    {{ $movie->title }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 mb-12">
                    {{-- Play Trailer Button --}}
                    <button @click="showTrailer = true" 
                            @if(!$movie->trailer_url) disabled @endif
                            class="h-12 px-8 bg-white text-black rounded-full font-bold flex items-center gap-2 hover:bg-white/90 hover:scale-105 active:scale-95 transition-all shadow-lg {{ !$movie->trailer_url ? 'opacity-40 cursor-not-allowed' : '' }}">
                        <i class="bi bi-play-fill text-2xl"></i>
                        <span class="text-sm uppercase tracking-wider">{{ __('Trailer') }}</span>
                    </button>

                    {{-- Watched Toggle Button --}}
                    <button @click="toggleWatched()"
                            class="h-12 px-8 rounded-full font-bold flex items-center gap-3 transition-all hover:scale-105 active:scale-95 shadow-lg border-2"
                            :class="isWatched
                                ? 'bg-blue-600 border-blue-600 text-white shadow-blue-600/20'
                                : 'bg-white/5 border-white/20 text-white hover:bg-white/10 hover:border-white/40'">

                        <div class="relative w-5 h-5 flex items-center justify-center">
                            <i class="bi bi-eye-fill absolute transition-all duration-300"
                               :class="isWatched ? 'scale-100 opacity-100' : 'scale-0 opacity-0'"></i>
                            <i class="bi bi-eye absolute transition-all duration-300"
                               :class="!isWatched ? 'scale-100 opacity-100' : 'scale-0 opacity-0'"></i>
                        </div>

                        <span class="text-sm uppercase tracking-wider" x-text="isWatched ? '{{ __('Gesehen') }}' : '{{ __('Nicht gesehen') }}'"></span>
                    </button>

                    {{-- Wishlist Toggle Button --}}
                    <button @click="toggleWishlist()"
                            class="h-12 px-8 rounded-full font-bold flex items-center gap-3 transition-all hover:scale-105 active:scale-95 shadow-lg border-2"
                            :class="isWishlisted
                                ? 'bg-rose-600 border-rose-600 text-white shadow-rose-600/20'
                                : 'bg-white/5 border-white/20 text-white hover:bg-white/10 hover:border-white/40'">
                        <i class="bi text-xl transition-colors"
                           :class="isWishlisted ? 'bi-bookmark-heart-fill' : 'bi-bookmark-heart'"></i>
                        <span class="text-sm uppercase tracking-wider" x-text="isWishlisted ? '{{ __('Auf Merkliste') }}' : '{{ __('Merken') }}'"></span>
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
                    <div class="text-lg md:text-xl text-white/80 leading-relaxed font-medium prose-movie" style="white-space: pre-wrap !important;">
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

                {{-- Seasons & Episodes (for Series) --}}
                @if($movie->collection_type === 'Serie' && $movie->seasons->count() > 0)
                    <div class="mb-16 animate-in slide-in-from-bottom duration-1000 delay-200" x-data="{ activeSeason: null }">
                        <h3 class="text-xl font-black text-white uppercase tracking-tight flex items-center gap-6 mb-10 text-emerald-400">
                            {{ __('Staffeln & Episoden') }}
                            <div class="h-1 w-12 bg-emerald-600 rounded-full shadow-[0_0_15px_rgba(16,185,129,0.5)]"></div>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            @foreach($movie->seasons->sortBy('season_number') as $season)
                                <div class="rounded-3xl border border-white/5 bg-white/5 overflow-hidden transition-colors duration-300 group/season"
                                     :class="activeSeason === {{ $season->id }} ? 'border-emerald-500/30 bg-white/10 shadow-2xl' : 'hover:border-white/10'">
                                    <button @click="activeSeason = activeSeason === {{ $season->id }} ? null : {{ $season->id }}"
                                            class="w-full flex items-center justify-between p-6 text-left">
                                        <div class="flex items-center gap-6">
                                            <div class="w-14 h-14 bg-emerald-600/20 rounded-2xl flex items-center justify-center text-emerald-400 font-black text-xl italic group-hover/season:scale-110 transition-transform">
                                                {{ $season->season_number }}
                                            </div>
                                            <div>
                                                <div class="text-lg font-black text-white group-hover/season:text-emerald-400 transition-colors uppercase italic">{{ $season->title ?: __('Staffel ' . $season->season_number) }}</div>
                                                <div class="text-[10px] text-white/30 uppercase font-black tracking-[0.2em] italic">{{ $season->episodes->count() }} {{ __('Folgen') }}</div>
                                            </div>
                                        </div>
                                        <i class="bi text-white/20 transition-transform duration-500 text-xl"
                                           :class="activeSeason === {{ $season->id }} ? 'bi-dash-lg rotate-180 text-emerald-400' : 'bi-plus-lg group-hover/season:text-emerald-400'"></i>
                                    </button>
                                    <div x-show="activeSeason === {{ $season->id }}"
                                         x-collapse
                                         class="border-t border-white/5 bg-black/40">
                                        <div class="divide-y divide-white/5">
                                            @foreach($season->episodes->sortBy('episode_number') as $episode)
                                                <div class="p-6 hover:bg-white/5 transition-all group/episode">
                                                    <div class="flex items-center gap-6 mb-3">
                                                        <span class="text-[10px] font-black text-emerald-400/50 w-10 uppercase italic">E{{ $episode->episode_number }}</span>
                                                        <h4 class="text-sm font-black text-white uppercase group-hover/episode:text-emerald-400 transition-colors italic tracking-tight">{{ $episode->title }}</h4>
                                                    </div>
                                                    @if($episode->overview)
                                                        <p class="text-[11px] text-white/40 leading-relaxed ml-16 line-clamp-3 font-medium italic prose-movie">
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

                {{-- Boxset Section --}}
                @if($movie->boxsetChildren->isNotEmpty())
                    <div class="mb-16 animate-in slide-in-from-bottom duration-1000 delay-300">
                        <h3 class="text-xl font-black text-white uppercase tracking-tight flex items-center gap-6 mb-10 text-amber-500">
                            {{ __('Filme in diesem Set') }}
                            <div class="h-1 w-12 bg-amber-600 rounded-full shadow-[0_0_15px_rgba(245,158,11,0.5)]"></div>
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                            @foreach($movie->boxsetChildren as $child)
                                <a href="{{ route('movies.show', $child) }}" 
                                   class="group/child">
                                    <div class="relative aspect-[2/3] rounded-[2rem] overflow-hidden glass border border-white/5 transition-all duration-700 group-hover/child:scale-105 group-hover/child:border-amber-500/50 shadow-2xl mb-4">
                                        <img src="{{ $child->cover_url }}" alt="{{ $child->title }}" class="w-full h-full object-cover duration-[1.5s] group-hover/child:scale-110">
                                        <div class="absolute inset-0 bg-gradient-to-t from-[#0c0c0e] via-transparent to-transparent opacity-60"></div>
                                    </div>
                                    <div class="px-2">
                                        <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest mb-1 italic">{{ $child->year }}</p>
                                        <h4 class="text-white font-black text-xs leading-none uppercase tracking-tighter truncate italic group-hover/child:text-amber-400 transition-colors">{{ $child->title }}</h4>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Cast Carousel --}}
                @if($movie->actors->isNotEmpty())
                <div class="mb-16 animate-in slide-in-from-bottom duration-1000 delay-400" 
                     x-data="{ 
                         scrollAmount: 400,
                         scroll(dir) { 
                             this.$refs.castContainer.scrollBy({ left: dir * this.scrollAmount, behavior: 'smooth' }); 
                         } 
                     }">
                    <div class="flex items-center justify-between mb-8 pl-4 pr-4">
                        <h3 class="text-xl font-black text-white uppercase tracking-tight flex items-center gap-4 text-blue-500">
                            {{ __('Besetzung') }}
                            <div class="h-1 w-8 bg-blue-600 rounded-full shadow-[0_0_15px_rgba(59,130,246,0.5)]"></div>
                        </h3>
                        
                        <!-- Navigation Arrows -->
                        <div class="flex items-center gap-3">
                            <button @click="scroll(-1)" class="w-10 h-10 rounded-full border border-white/10 bg-white/5 hover:bg-white/10 hover:border-blue-600/50 flex items-center justify-center transition-all active:scale-90 text-white/40 hover:text-white">
                                <i class="bi bi-chevron-left text-lg"></i>
                            </button>
                            <button @click="scroll(1)" class="w-10 h-10 rounded-full border border-white/10 bg-white/5 hover:bg-white/10 hover:border-blue-600/50 flex items-center justify-center transition-all active:scale-90 text-white/40 hover:text-white">
                                <i class="bi bi-chevron-right text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex gap-6 overflow-x-auto no-scrollbar pb-8 px-4 scroll-smooth" x-ref="castContainer">
                        @foreach($movie->actors as $actor)
                        <a href="{{ route('actors.show', $actor) }}" class="group min-w-[140px] md:min-w-[160px] text-center">
                            <div class="w-full aspect-square rounded-full overflow-hidden border-2 border-white/10 group-hover:border-blue-600 group-hover:shadow-[0_0_30px_rgba(59,130,246,0.3)] transition-all mb-4 relative shadow-2xl">
                                <img src="{{ $actor->profile_url ?: asset('img/default-actor.png') }}" alt="{{ $actor->full_name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                <div class="absolute inset-0 bg-gradient-to-t from-blue-600/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </div>
                            <p class="text-sm font-black text-white truncate px-2 group-hover:text-blue-400 transition-colors uppercase tracking-tight italic">{{ $actor->full_name }}</p>
                            <p class="text-[10px] font-black text-white/30 truncate uppercase italic tracking-widest mt-1">{{ $actor->pivot->role ?? '' }}</p>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Similar Movies Ribbon --}}
                 @if(isset($similarMovies) && $similarMovies->isNotEmpty())
                <div class="animate-in slide-in-from-bottom duration-1000 delay-500" 
                     x-data="{ 
                         scrollAmount: 400,
                         scroll(dir) { 
                             this.$refs.similarContainer.scrollBy({ left: dir * this.scrollAmount, behavior: 'smooth' }); 
                         } 
                     }">
                    <div class="flex items-center justify-between mb-8 pl-4 pr-4">
                        <h3 class="text-xl font-black text-white uppercase tracking-tight flex items-center gap-4">
                            {{ __('Das könnte dir auch gefallen') }}
                            <div class="h-1 w-8 bg-emerald-600 rounded-full"></div>
                        </h3>
                        
                        <!-- Navigation Arrows -->
                        <div class="flex items-center gap-3">
                            <button @click="scroll(-1)" class="w-12 h-12 rounded-full border border-white/10 bg-white/5 hover:bg-white/10 hover:border-emerald-600/50 flex items-center justify-center transition-all active:scale-90 text-white/40 hover:text-white">
                                <i class="bi bi-chevron-left text-xl"></i>
                            </button>
                            <button @click="scroll(1)" class="w-12 h-12 rounded-full border border-white/10 bg-white/5 hover:bg-white/10 hover:border-emerald-600/50 flex items-center justify-center transition-all active:scale-90 text-white/40 hover:text-white">
                                <i class="bi bi-chevron-right text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex gap-8 overflow-x-auto no-scrollbar pb-12 px-4 scroll-smooth" x-ref="similarContainer">
                        @foreach($similarMovies as $similar)
                        <a href="{{ route('movies.show', $similar) }}" 
                             class="group min-w-[180px] md:min-w-[220px]">
                            <div class="w-full aspect-[2/3] rounded-[2rem] overflow-hidden glass border border-white/10 group-hover:border-emerald-500 group-hover:shadow-[0_0_40px_rgba(16,185,129,0.2)] transition-all duration-700 mb-4 relative shadow-2xl hover:scale-105">
                                <img src="{{ $similar->cover_url }}" alt="{{ $similar->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-[1.5s]">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#0c0c0e] via-transparent to-transparent opacity-60"></div>
                            </div>
                            <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1 italic">{{ $similar->year }}</p>
                            <p class="text-sm font-black text-white truncate px-2 group-hover:text-emerald-400 transition-colors uppercase tracking-tight italic">{{ $similar->title }}</p>
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

                     {{-- Star Rating under Cover --}}
                     <div class="mt-6 flex flex-col items-center gap-2">
                         <p class="text-[10px] font-black text-white/30 uppercase tracking-widest">{{ __('Deine Bewertung') }}</p>
                         <div class="flex items-center gap-2">
                             @for($s = 1; $s <= 5; $s++)
                             <button @click="setRating({{ $s }})"
                                     @mouseenter="hoverRating = {{ $s }}"
                                     @mouseleave="hoverRating = 0"
                                     class="text-3xl transition-all active:scale-90 hover:scale-110"
                                     :class="(hoverRating || userRating) >= {{ $s }} ? 'text-amber-400' : 'text-white/10'">
                                 <i class="bi bi-star-fill"></i>
                             </button>
                             @endfor
                         </div>
                         <p class="text-sm text-amber-400 font-black" x-show="userRating > 0" x-text="userRating + '/5'"></p>
                         <p class="text-[10px] text-white/20 font-bold" x-show="ratingCount > 0" x-text="'Ø ' + avgRating + ' (' + ratingCount + ' {{ __('Bewertungen') }})'"></p>
                     </div>

                     {{-- Bonus Details Card --}}
                     <div class="mt-8 glass p-6 rounded-3xl border border-white/10 flex items-center justify-between">
                         <div class="text-center flex-1 border-r border-white/5">
                             <p class="text-[10px] font-black text-white/40 uppercase mb-1">{{ __('Runtime') }}</p>
                             <p class="text-sm font-black text-white">{{ $movie->runtime ?: 'N/A' }}m</p>
                         </div>
                         <div class="text-center flex-1 border-r border-white/5">
                             <p class="text-[10px] font-black text-white/40 uppercase mb-1">{{ __('TMDB') }}</p>
                             <p class="text-sm font-black text-white">{{ $movie->rating ? number_format($movie->rating, 1) . '/10' : 'N/A' }}</p>
                         </div>
                         <div class="text-center flex-1">
                             <p class="text-[10px] font-black text-white/40 uppercase mb-1">{{ __('Jahr') }}</p>
                             <p class="text-sm font-black text-white">{{ $movie->year ?: 'N/A' }}</p>
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
