@php
    $layoutMode = auth()->check() 
        ? auth()->user()->layout 
        : \App\Models\Setting::get('default_guest_layout', 'classic');
@endphp

<div class="{{ $layoutMode === 'streaming' ? 'streaming-actor-details animate-in fade-in slide-in-from-bottom-8 duration-700 pt-32' : 'animate-in fade-in slide-in-from-right-4 duration-500' }}">
    @if($layoutMode === 'streaming')
        {{-- High-End Immersive Profile --}}
        <div class="relative mb-16 rounded-[3rem] overflow-hidden group shadow-2xl border border-white/10 min-h-[500px]">
             <!-- Large Portrait Backdrop -->
             <div class="absolute inset-0 bg-[#0c0c0e]">
                @if($actor->profile_url)
                    <img src="{{ $actor->profile_url }}" alt="{{ $actor->full_name }}" 
                         class="w-full h-full object-cover opacity-40 blur-[80px] scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0c0c0e] via-[#0c0c0e]/40 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-[#0c0c0e] via-transparent to-[#0c0c0e]/40"></div>
                @else
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-purple-600/10 opacity-30"></div>
                @endif
             </div>

             <!-- Content Overlay -->
             <div class="relative z-10 p-12 lg:p-20 flex flex-col lg:flex-row gap-16 items-center lg:items-start text-center lg:text-left">
                <!-- Large Portrait -->
                <div class="relative w-full max-w-[320px] aspect-[2/3] rounded-[2.5rem] overflow-hidden shadow-[0_0_80px_rgba(0,0,0,0.8)] border border-white/20 transform skew-y-1 hover:skew-y-0 transition-transform duration-700 group-hover:scale-[1.02]">
                    @if($actor->profile_url)
                        <img src="{{ $actor->profile_url }}" alt="{{ $actor->full_name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-[#1a1a1f] flex items-center justify-center">
                            <i class="bi bi-person text-white/5 text-8xl"></i>
                        </div>
                    @endif
                    
                    {{-- Interactive Badge --}}
                    <div class="absolute top-6 right-6">
                         <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-xl border border-white/20 flex items-center justify-center text-white shadow-2xl">
                             <i class="bi bi-star-fill text-yellow-500"></i>
                         </div>
                    </div>
                </div>

                <!-- Info Block -->
                <div class="flex-1 pt-4">
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 mb-8">
                        <span class="px-6 py-2 bg-blue-600 rounded-2xl text-[10px] font-black tracking-[0.3em] uppercase shadow-xl shadow-blue-500/20">
                            {{ __('Schauspieler') }}
                        </span>
                        @if($actor->birthday)
                            @php
                                $birth = \Carbon\Carbon::parse($actor->birthday);
                                $death = $actor->deathday ? \Carbon\Carbon::parse($actor->deathday) : null;
                                $age = $death ? $birth->diffInYears($death) : $birth->age;
                            @endphp
                            <span class="px-6 py-2 bg-white/5 backdrop-blur-xl rounded-2xl text-[10px] font-black tracking-[0.3em] uppercase border border-white/10 italic">
                                {{ $death ? '✝ ' : '' }}{{ $age }} {{ __('Jahre') }}
                            </span>
                        @endif
                    </div>

                    <h2 class="text-6xl lg:text-8xl font-black text-white leading-[0.9] tracking-tighter mb-8 uppercase italic drop-shadow-2xl">
                        {{ $actor->first_name }}<br>
                        <span class="text-blue-500 selection:bg-white selection:text-blue-600">{{ $actor->last_name }}</span>
                    </h2>

                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-12 text-white/40 text-xs font-bold uppercase tracking-[0.2em] mb-12 italic">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-film text-blue-500 text-lg"></i>
                            <span>{{ $stats['total_movies'] }} {{ __('Filme') }}</span>
                        </div>
                        @if ($actor->place_of_birth)
                            <div class="flex items-center gap-3">
                                <i class="bi bi-geo-alt text-rose-500 text-lg"></i>
                                <span>{{ $actor->place_of_birth }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-3">
                            <i class="bi bi-eye text-emerald-500 text-lg"></i>
                            <span>{{ number_format($actor->view_count) }} {{ __('Aufrufe') }}</span>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-6">
                        @if($actor->imdb_id)
                            <a href="https://www.imdb.com/name/{{ $actor->imdb_id }}" target="_blank" 
                               class="px-8 py-4 bg-white/10 backdrop-blur-xl border border-white/10 rounded-[1.5rem] text-[10px] font-black uppercase tracking-[0.3em] text-white hover:bg-yellow-500/20 hover:border-yellow-500/50 transition-all flex items-center gap-4 italic group">
                                <i class="bi bi-film text-yellow-500 text-lg group-hover:scale-125 transition-transform"></i> IMDb
                            </a>
                        @endif
                        <a href="https://www.themoviedb.org/person/{{ $actor->tmdb_id }}" target="_blank" 
                           class="px-8 py-4 bg-white/10 backdrop-blur-xl border border-white/10 rounded-[1.5rem] text-[10px] font-black uppercase tracking-[0.3em] text-white hover:bg-emerald-500/20 hover:border-emerald-500/50 transition-all flex items-center gap-4 italic group">
                            <i class="bi bi-info-circle text-emerald-500 text-lg group-hover:scale-125 transition-transform"></i> TMDb
                        </a>
                        <a href="{{ route('actors.show', $actor) }}" target="_blank" 
                           class="w-14 h-14 bg-blue-600 rounded-[1.5rem] flex items-center justify-center text-white shadow-xl shadow-blue-500/20 hover:scale-110 active:scale-95 transition-all group">
                            <i class="bi bi-fullscreen text-xl group-hover:rotate-12 transition-transform"></i>
                        </a>
                    </div>
                </div>
             </div>
        </div>

        {{-- Biography Ribbon --}}
        @if($actor->bio)
            <div class="mb-20 animate-in fade-in duration-1000 delay-300">
                <div class="flex items-center gap-8 mb-10">
                    <h3 class="text-xs font-black text-white/20 uppercase tracking-[0.5em] italic shrink-0">{{ __('Biografie') }}</h3>
                    <div class="h-px bg-gradient-to-r from-white/10 via-white/5 to-transparent flex-1"></div>
                </div>
                <div class="relative">
                    <div class="absolute -left-8 top-0 bottom-0 w-1 bg-blue-600 rounded-full shadow-[0_0_20px_rgba(59,130,246,0.5)] opacity-50"></div>
                    <div class="text-white/60 leading-relaxed text-xl lg:text-2xl font-medium italic max-w-4xl tracking-tight prose-movie">
                        {!! $actor->bio !!}
                    </div>
                </div>
            </div>
        @endif

        {{-- Filmography Header --}}
        <div class="mb-10 flex items-center justify-between">
             <h3 class="text-xs font-black text-white/20 uppercase tracking-[0.5em] italic">{{ __('Sammlung') }}</h3>
             <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest italic animate-pulse group-hover:animate-none">
                 {{ count($movies) }} {{ __('Produktionen gefunden') }}
             </span>
        </div>

        {{-- Horizontal Movie Ribbon --}}
        <div class="flex gap-6 overflow-x-auto no-scrollbar pb-10">
            @foreach ($movies as $movie)
                <div @click="window.location.href = '{{ route('movies.show', $movie) }}'" 
                     class="min-w-[180px] md:min-w-[220px] group cursor-pointer animate-in zoom-in duration-700">
                    <div class="relative aspect-[2/3] rounded-[2rem] overflow-hidden glass-streaming border border-white/5 transition-all duration-700 hover:scale-[1.08] hover:border-blue-500/50 shadow-2xl">
                        @if ($movie->cover_url)
                            <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover duration-[1.5s] group-hover:scale-110">
                        @else
                            <div class="w-full h-full bg-[#1a1a1f] flex items-center justify-center">
                                <i class="bi bi-film text-white/5 text-4xl"></i>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0c0c0e] via-transparent to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
                        
                        {{-- Hover Role Overlay --}}
                        <div class="absolute inset-0 flex flex-col justify-end p-6 opacity-0 group-hover:opacity-100 transition-all duration-500 translate-y-4 group-hover:translate-y-0">
                             <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-1 italic">{{ $movie->pivot->role ?? 'Darsteller' }}</span>
                             <h4 class="text-white font-black text-xs leading-none uppercase tracking-tighter truncate">{{ $movie->title }}</h4>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        {{-- Original Classic Layout --}}
        <div class="relative rounded-[2.5rem] overflow-hidden glass-strong mb-10 aspect-[21/9] group shadow-2xl border border-white/5">
            <div class="absolute inset-0 bg-gray-950 flex items-center justify-center">
                @if ($actor->profile_url)
                    <img src="{{ $actor->profile_url }}" alt="{{ $actor->full_name }}" 
                         class="w-full h-full object-cover opacity-30 blur-xl scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/40 to-transparent"></div>
                @else
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-purple-600/10 opacity-30"></div>
                @endif
            </div>
            <div class="absolute left-8 bottom-8 flex items-end gap-8 z-20">
                <div class="relative w-32 md:w-36 aspect-[2/3] rounded-2xl overflow-hidden glass border-2 border-white/10 shadow-2xl">
                    @if ($actor->profile_url)
                        <img src="{{ $actor->profile_url }}" alt="{{ $actor->full_name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-white/5 flex items-center justify-center">
                            <i class="bi bi-person text-white/20 text-3xl"></i>
                        </div>
                    @endif
                </div>
                <div class="pb-2">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="px-3 py-1 bg-blue-600/80 backdrop-blur-md rounded-lg text-[10px] font-black tracking-widest uppercase border border-white/20 shadow-lg">{{ __('Schauspieler') }}</span>
                    </div>
                    <h2 class="text-3xl font-black text-white leading-tight mb-2 drop-shadow-2xl tracking-tighter uppercase">{{ $actor->full_name }}</h2>
                    <div class="flex items-center gap-6 text-gray-300 text-xs font-medium">
                        <div class="flex items-center gap-2 italic">
                            <i class="bi bi-film text-blue-400"></i>
                            <span>{{ $stats['total_movies'] }} {{ __('Filme') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="glass p-4 rounded-2xl flex flex-col items-center justify-center text-center">
                <span class="text-gray-500 text-[9px] font-bold uppercase tracking-widest mb-1">{{ __('Hauptrollen') }}</span>
                <div class="text-xl font-bold text-white">{{ $stats['main_roles'] }}</div>
            </div>
            <div class="glass p-4 rounded-2xl flex flex-col items-center justify-center text-center">
                <span class="text-gray-500 text-[9px] font-bold uppercase tracking-widest mb-1">{{ __('Zeitspanne') }}</span>
                <div class="text-sm font-bold text-blue-400">{{ $stats['year_span'] ?? 'N/A' }}</div>
            </div>
            <div class="glass p-4 rounded-2xl flex flex-col items-center justify-center text-center">
                <span class="text-gray-500 text-[9px] font-bold uppercase tracking-widest mb-1">{{ __('Aufrufe') }}</span>
                <div class="text-xl font-bold text-white">{{ number_format($actor->view_count) }}</div>
            </div>
        </div>

        <div class="space-y-8">
            @if ($actor->bio)
                <div class="glass p-6 rounded-3xl border-white/5">
                    <div class="text-gray-300 leading-relaxed text-xs italic opacity-80 prose-movie">
                        {!! Str::limit($actor->bio, 450) !!}
                    </div>
                </div>
            @endif
            <div class="glass p-6 rounded-3xl border-white/5">
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-4">
                    @foreach ($movies as $movie)
                        <div @click="fetchDetails({{ $movie->id }})" class="group cursor-pointer flex flex-col gap-2">
                            <div class="relative aspect-[2/3] rounded-xl overflow-hidden glass border border-white/5 transition-all duration-500 group-hover:scale-105 shadow-lg">
                                @if ($movie->cover_url)
                                    <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover">
                                @endif
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <i class="bi bi-info-circle text-white text-xl"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
