<div class="animate-in fade-in slide-in-from-right-4 duration-500"> <!-- Header Area -->
    <div
        class="relative rounded-[2.5rem] overflow-hidden glass-strong mb-10 aspect-[21/9] group shadow-2xl border border-white/5">
        <!-- Background/Backdrop -->
        <div class="absolute inset-0 bg-gray-950 flex items-center justify-center">
            @if ($actor->profile_path)
                <img src="{{ asset('storage/' . $actor->profile_path) }}" alt="{{ $actor->full_name }}"
                    class="w-full h-full object-cover opacity-30 blur-xl scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/40 to-transparent"></div>
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-purple-600/10 opacity-30"></div>
                @endif
        </div> <!-- Portrait Overlay -->
        <div class="absolute left-8 bottom-8 flex items-end gap-8 z-20">
            <div
                class="relative w-32 md:w-36 aspect-[2/3] rounded-2xl overflow-hidden glass border-2 border-white/10 shadow-2xl">
                @if ($actor->profile_path)
                    <img src="{{ asset('storage/' . $actor->profile_path) }}" alt="{{ $actor->full_name }}"
                        class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-white/5 flex items-center justify-center"> <i
                            class="bi bi-person text-white/20 text-3xl"></i> </div>
                    @endif
            </div>
            <div class="pb-2">
                <div class="flex items-center gap-3 mb-3"> <span
                        class="px-3 py-1 bg-blue-600/80 backdrop-blur-md rounded-lg text-[10px] font-black tracking-widest uppercase border border-white/20 shadow-lg">
                        {{ __('Schauspieler') }} </span>
                    @if ($actor->birthday)
                        @php
                            $birth = \Carbon\Carbon::parse($actor->birthday);
                            $death = $actor->deathday ? \Carbon\Carbon::parse($actor->deathday) : null;
                            $age = $death ? $birth->diffInYears($death) : $birth->age;
                        @endphp <span
                            class="px-3 py-1 bg-white/10 backdrop-blur-md rounded-lg text-[10px] font-black tracking-widest uppercase border border-white/10 shadow-lg">
                            {{ $death ? '✝ ' : '' }}{{ $age }} {{ __('Jahre') }} </span>
                        @endif
                </div>
                <h2
                    class="text-3xl font-black text-white leading-tight mb-2 drop-shadow-2xl tracking-tighter uppercase">
                    {{ $actor->full_name }} </h2>
                <div class="flex items-center gap-6 text-gray-300 text-xs font-medium">
                    <div class="flex items-center gap-2"> <i class="bi bi-film text-blue-400"></i>
                        <span>{{ $stats['total_movies'] }} {{ __('Filme') }}</span> </div>
                    @if ($actor->place_of_birth)
                        <div class="flex items-center gap-2 truncate max-w-[200px]"> <i
                                class="bi bi-geo-alt text-rose-400"></i> <span
                                class="truncate">{{ $actor->place_of_birth }}</span> </div>
                        @endif
                </div>
            </div>
        </div>
    </div> <!-- Quick Stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="glass p-4 rounded-2xl flex flex-col items-center justify-center text-center"> <span
                class="text-gray-500 text-[9px] font-bold uppercase tracking-widest mb-1">{{ __('Hauptrollen') }}</span>
            <div class="text-xl font-bold text-white">{{ $stats['main_roles'] }}</div>
        </div>
        <div class="glass p-4 rounded-2xl flex flex-col items-center justify-center text-center"> <span
                class="text-gray-500 text-[9px] font-bold uppercase tracking-widest mb-1">{{ __('Zeitspanne') }}</span>
            <div class="text-sm font-bold text-blue-400">{{ $stats['year_span'] ?? 'N/A' }}</div>
        </div>
        <div class="glass p-4 rounded-2xl flex flex-col items-center justify-center text-center"> <span
                class="text-gray-500 text-[9px] font-bold uppercase tracking-widest mb-1">{{ __('Aufrufe') }}</span>
            <div class="text-xl font-bold text-white">{{ number_format($actor->view_count) }}</div>
        </div>
    </div> <!-- Main Content -->
    <div class="space-y-8"> <!-- Bio -->
        @if ($actor->bio)
            <div class="glass p-6 rounded-3xl border-white/5">
                <h3
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2 underline decoration-blue-500/50 underline-offset-8 italic">
                    <i class="bi bi-text-left text-blue-400"></i> {{ __('Biografie') }} </h3>
                <p class="text-gray-300 leading-relaxed text-xs italic opacity-80"> {{ Str::limit($actor->bio, 450) }}
                </p>
            </div>
            @endif <!-- Filmography (Grid Style) -->
            <div class="glass p-6 rounded-3xl border-white/5">
                <h3
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2 underline decoration-blue-500/50 underline-offset-8 italic">
                    <i class="bi bi-grid text-blue-400"></i> {{ __('Sammlung') }} </h3>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-4">
                    @foreach ($movies as $movie)
                        <div @click="fetchDetails({{ $movie->id }})"
                            class="group cursor-pointer flex flex-col gap-2">
                            <div
                                class="relative aspect-[2/3] rounded-xl overflow-hidden glass border border-white/5 transition-all duration-500 group-hover:scale-105 group-hover:border-blue-500/50 shadow-lg">
                                @if ($movie->cover_url)
                                    <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-white/5 flex items-center justify-center"> <i
                                            class="bi bi-film text-white/5"></i> </div>
                                @endif
                            </div>
                            <div class="px-1">
                                <h4
                                    class="text-white font-black text-[9px] leading-tight truncate group-hover:text-blue-400 transition-colors uppercase tracking-widest">
                                    {{ $movie->title }}</h4>
                            </div>
                        </div>
                        @endforeach
                </div>
            </div>
    </div> <!-- External Links -->
    <div class="mt-8 flex gap-3">
        @if ($actor->imdb_id)
            <a href="https://www.imdb.com/name/{{ $actor->imdb_id }}" target="_blank"
                class="flex-1 glass hover:bg-yellow-500/20 text-white px-4 py-3 rounded-2xl font-bold text-[10px] transition-all flex items-center justify-center gap-2 uppercase tracking-widest border border-white/5">
                <i class="bi bi-film text-yellow-500"></i> IMDb </a>
        @endif <a href="https://www.themoviedb.org/person/{{ $actor->tmdb_id }}" target="_blank"
            class="flex-1 glass hover:bg-emerald-500/20 text-white px-4 py-3 rounded-2xl font-bold text-[10px] transition-all flex items-center justify-center gap-2 uppercase tracking-widest border border-white/5">
            <i class="bi bi-info-circle text-emerald-500"></i> TMDb </a> <a href="{{ route('actors.show', $actor) }}"
            target="_blank"
            class="w-12 h-12 bg-blue-600/20 hover:bg-blue-600/40 border border-blue-500/30 rounded-2xl flex items-center justify-center transition-colors group"
            title="Vollbild Profil"> <i
                class="bi bi-fullscreen text-blue-400 group-hover:scale-110 transition-transform"></i> </a>
    </div>
</div>
