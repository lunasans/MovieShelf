<x-app-layout>
    <div class="relative min-h-screen">
        <!-- Backdrop (Subtle) -->
        <div class="absolute inset-0 h-[60vh] bg-gradient-to-b from-blue-600/10 to-transparent opacity-30 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-8 py-20 relative z-10">
            <div class="flex flex-col lg:flex-row gap-16">
                
                <!-- Left Column: Portrait & Info -->
                <div class="w-full lg:w-1/3 flex flex-col items-center lg:items-start text-center lg:text-left">
                    <div class="relative group mb-10 w-full max-w-[350px]">
                        <div class="absolute -inset-4 bg-blue-500/20 rounded-[2.5rem] blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                        <div class="relative aspect-[2/3] rounded-[2rem] overflow-hidden glass border border-white/20 shadow-2xl skew-y-1 transform transition-transform duration-700 hover:skew-y-0">
                            @if($actor->profile_path)
                                <img src="{{ asset('storage/' . $actor->profile_path) }}" alt="{{ $actor->full_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                                    <i class="bi bi-person-fill text-9xl text-white/5"></i>
                                </div>
                            @endif

                            <!-- Social Links Overlay -->
                            <div class="absolute bottom-6 left-0 right-0 flex justify-center gap-4 opacity-0 group-hover:opacity-100 transition-opacity translate-y-4 group-hover:translate-y-0 duration-500">
                                @if($actor->imdb_id)
                                    <a href="https://www.imdb.com/name/{{ $actor->imdb_id }}" target="_blank" class="w-12 h-12 glass hover:bg-yellow-500/80 rounded-xl flex items-center justify-center text-white transition-all shadow-xl border border-white/20">
                                        <i class="bi bi-film"></i>
                                    </a>
                                @endif
                                @if($actor->homepage)
                                    <a href="{{ $actor->homepage }}" target="_blank" class="w-12 h-12 glass hover:bg-blue-500/80 rounded-xl flex items-center justify-center text-white transition-all shadow-xl border border-white/20">
                                        <i class="bi bi-globe"></i>
                                    </a>
                                @endif
                                <a href="https://www.themoviedb.org/person/{{ $actor->tmdb_id }}" target="_blank" class="w-12 h-12 glass hover:bg-emerald-500/80 rounded-xl flex items-center justify-center text-white transition-all shadow-xl border border-white/20">
                                    <i class="bi bi-info-circle"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Details -->
                    <div class="w-full space-y-6 glass p-8 rounded-3xl border border-white/10 mb-8">
                        <h2 class="text-xs font-black uppercase tracking-[0.3em] text-blue-400 mb-4 italic">Persönliche Details</h2>
                        
                        @if($actor->birthday)
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Geburtstag</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-white font-bold">{{ \Carbon\Carbon::parse($actor->birthday)->format('d. F Y') }}</span>
                                    @php
                                        $birth = \Carbon\Carbon::parse($actor->birthday);
                                        $death = $actor->deathday ? \Carbon\Carbon::parse($actor->deathday) : null;
                                        $age = $death ? $birth->diffInYears($death) : $birth->age;
                                    @endphp
                                    <span class="px-2 py-0.5 bg-white/5 rounded text-[10px] font-black text-blue-400">
                                        {{ $death ? '✝ ' : '' }}{{ $age }} Jahre
                                    </span>
                                </div>
                            </div>
                        @endif

                        @if($actor->place_of_birth)
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Geburtsort</span>
                                <span class="text-white font-bold italic opacity-80">{{ $actor->place_of_birth }}</span>
                            </div>
                        @endif

                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Aufrufe</span>
                            <span class="text-white font-bold">{{ number_format($actor->view_count) }}</span>
                        </div>
                    </div>

                    <!-- Stats Highlights -->
                    <div class="w-full grid grid-cols-2 gap-4">
                        <div class="glass p-5 rounded-2xl border border-white/10 flex flex-col items-center justify-center text-center">
                            <span class="text-white text-2xl font-black">{{ $stats['total_movies'] }}</span>
                            <span class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mt-1">Filme</span>
                        </div>
                        <div class="glass p-5 rounded-2xl border border-white/10 flex flex-col items-center justify-center text-center">
                            <span class="text-blue-500 text-2xl font-black">{{ $stats['main_roles'] }}</span>
                            <span class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mt-1">Top</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Bio & Movies -->
                <div class="w-full lg:w-2/3">
                    <div class="mb-12">
                        <h1 class="text-6xl md:text-8xl font-black text-white tracking-tighter mb-4 leading-none uppercase">
                            {{ $actor->first_name }}<br>
                            <span class="text-blue-500 drop-shadow-[0_0_30px_rgba(37,99,235,0.3)]">{{ $actor->last_name }}</span>
                        </h1>
                        <div class="h-1.5 w-24 bg-blue-600 rounded-full shadow-[0_0_20px_rgba(37,99,235,0.5)]"></div>
                    </div>

                    <!-- Biography -->
                    @if($actor->bio)
                        <div class="mb-16">
                            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-gray-500 mb-6 italic flex items-center gap-4">
                                Biografie
                                <div class="h-[1px] bg-white/10 flex-1"></div>
                            </h2>
                            <p class="text-gray-300 leading-relaxed text-lg font-medium opacity-80 italic">
                                {{ $actor->bio }}
                            </p>
                        </div>
                    @endif

                    <!-- Filmography -->
                    <div>
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-gray-500 italic flex items-center gap-4 flex-1">
                                Sammlung
                                <div class="h-[1px] bg-white/10 flex-1"></div>
                            </h2>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                            @foreach($movies as $movie)
                                <a href="{{ route('dashboard', ['movie' => $movie->id]) }}" class="group flex flex-col gap-4">
                                    <div class="relative aspect-[2/3] rounded-3xl overflow-hidden glass border border-white/10 transition-all duration-500 hover:scale-[1.05] hover:shadow-blue-500/30 hover:border-blue-500/50 shadow-2xl">
                                        @if($movie->cover_id)
                                            <img src="{{ asset('storage/' . $movie->cover_id) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                                            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/80 to-transparent z-10 opacity-60 group-hover:opacity-40 transition-opacity"></div>
                                        @else
                                            <div class="w-full h-full bg-gray-900 flex items-center justify-center">
                                                <i class="bi bi-film text-4xl text-white/5"></i>
                                            </div>
                                        @endif

                                        <!-- collection Badge -->
                                        <div class="absolute bottom-3 left-3 z-20">
                                            <span class="text-[8px] font-black text-white/90 uppercase tracking-widest glass px-2 py-1 rounded-lg border border-white/10 shadow-lg">
                                                {{ $movie->collection_type }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="px-1 text-center sm:text-left">
                                        <h4 class="text-white font-black text-[13px] leading-tight truncate group-hover:text-blue-400 transition-colors uppercase tracking-tight">{{ $movie->title }}</h4>
                                        <p class="text-[10px] font-bold text-gray-400 group-hover:text-blue-200 transition-all mt-1 italic truncate opacity-60 group-hover:opacity-100">
                                            {{ $movie->pivot->role ?? 'Darsteller' }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script type="application/ld+json">
    {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush
</x-app-layout>
