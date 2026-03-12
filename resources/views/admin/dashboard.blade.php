<x-admin-layout>
    @section('header_title', 'Dashboard')

    <div class="space-y-10">
        <!-- Main Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-blue-500/10 to-transparent flex items-center gap-5 group hover:border-blue-500/30 transition-all duration-500">
                <div class="w-14 h-14 bg-blue-500/20 rounded-2xl flex items-center justify-center text-blue-400 text-2xl group-hover:scale-110 transition-transform shadow-lg shadow-blue-500/10">
                    <i class="bi bi-film"></i>
                </div>
                <div>
                    <div class="text-3xl font-black text-white">{{ number_format($stats['totalMovies']) }}</div>
                    <div class="text-[10px] font-black text-white/30 uppercase tracking-widest mt-1">Filme Gesamt</div>
                </div>
            </div>

            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-purple-500/10 to-transparent flex items-center gap-5 group hover:border-purple-500/30 transition-all duration-500">
                <div class="w-14 h-14 bg-purple-500/20 rounded-2xl flex items-center justify-center text-purple-400 text-2xl group-hover:scale-110 transition-transform shadow-lg shadow-purple-500/10">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="text-3xl font-black text-white">{{ number_format($stats['totalActors']) }}</div>
                    <div class="text-[10px] font-black text-white/30 uppercase tracking-widest mt-1">Schauspieler</div>
                </div>
            </div>

            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-emerald-500/10 to-transparent flex items-center gap-5 group hover:border-emerald-500/30 transition-all duration-500">
                <div class="w-14 h-14 bg-emerald-500/20 rounded-2xl flex items-center justify-center text-emerald-400 text-2xl group-hover:scale-110 transition-transform shadow-lg shadow-emerald-500/10">
                    <i class="bi bi-clock"></i>
                </div>
                <div>
                    <div class="text-3xl font-black text-white">{{ round($stats['totalRuntime'] / 60) }}</div>
                    <div class="text-[10px] font-black text-white/30 uppercase tracking-widest mt-1">Stunden Content</div>
                </div>
            </div>

            <div class="glass p-6 rounded-[2rem] border-white/5 bg-gradient-to-br from-amber-500/10 to-transparent flex items-center gap-5 group hover:border-amber-500/30 transition-all duration-500">
                <div class="w-14 h-14 bg-amber-500/20 rounded-2xl flex items-center justify-center text-amber-400 text-2xl group-hover:scale-110 transition-transform shadow-lg shadow-amber-500/10">
                    <i class="bi bi-collection"></i>
                </div>
                <div>
                    <div class="text-3xl font-black text-white">{{ count($stats['collectionTypes']) }}</div>
                    <div class="text-[10px] font-black text-white/30 uppercase tracking-widest mt-1">Medien-Typen</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Genres & Decades -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Genre Distribution -->
                <div class="glass p-8 rounded-[2.5rem] border-white/5 overflow-hidden relative">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-black text-white flex items-center gap-3">
                                <i class="bi bi-tags text-blue-400"></i>
                                Beliebte Genres
                            </h3>
                            <p class="text-[10px] text-white/30 uppercase tracking-widest font-bold mt-1">Verteilung in der Sammlung</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($stats['genres'] as $genre)
                            @php
                                $percentage = ($genre->count / $stats['totalMovies']) * 100;
                            @endphp
                            <div class="space-y-2">
                                <div class="flex justify-between text-xs font-bold text-white/70">
                                    <span>{{ $genre->genre }}</span>
                                    <span>{{ $genre->count }}</span>
                                </div>
                                <div class="h-2 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.5)] transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Decade Distribution -->
                <div class="glass p-8 rounded-[2.5rem] border-white/5">
                    <h3 class="text-lg font-black text-white mb-6 flex items-center gap-3">
                        <i class="bi bi-calendar-range text-emerald-400"></i>
                        Jahrzehnte
                    </h3>
                    <div class="flex items-end gap-2 h-48 px-4">
                        @php
                            $maxDecadeCount = $stats['decades']->max('count') ?: 1;
                        @endphp
                        @foreach($stats['decades'] as $decade)
                            @php
                                $height = ($decade->count / $maxDecadeCount) * 100;
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-2 group">
                                <div class="text-[10px] text-white/30 font-black opacity-0 group-hover:opacity-100 transition-opacity">{{ $decade->count }}</div>
                                <div class="w-full bg-emerald-500/20 rounded-t-lg group-hover:bg-emerald-500/40 transition-all cursor-help" style="height: {{ $height }}%" title="{{ $decade->count }} Filme"></div>
                                <div class="text-[10px] font-black text-white/40">{{ $decade->decade }}s</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Top Actors & Latest -->
            <div class="space-y-8">
                <!-- Latest Movies -->
                <div class="glass p-8 rounded-[2.5rem] border-white/5">
                    <h3 class="text-lg font-black text-white mb-6 flex items-center gap-3">
                        <i class="bi bi-clock-history text-amber-400"></i>
                        Zuletzt Hinzugefügt
                    </h3>
                    <div class="space-y-4">
                        @foreach($stats['latestMovies'] as $movie)
                            <div class="flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-all group">
                                <div class="w-12 h-16 bg-white/5 rounded-lg overflow-hidden border border-white/10 relative">
                                    @if($movie->cover_id)
                                        <img src="{{ Storage::url($movie->cover_id) }}" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all">
                                    @else
                                        <div class="flex items-center justify-center h-full text-white/10">
                                            <i class="bi bi-film"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-bold text-white truncate">{{ $movie->title }}</div>
                                    <div class="text-[10px] text-white/30 uppercase tracking-widest font-black">{{ $movie->year }} • {{ $movie->genre }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.movies.index') }}" class="mt-6 w-full py-4 text-center border border-white/5 rounded-2xl text-[10px] font-black text-white/30 uppercase tracking-widest hover:bg-white/5 hover:text-white transition-all block">
                        Alle Filme ansehen
                    </a>
                </div>

                <!-- Top Actors -->
                <div class="glass p-8 rounded-[2.5rem] border-white/5">
                    <h3 class="text-lg font-black text-white mb-6 flex items-center gap-3">
                        <i class="bi bi-star text-purple-400"></i>
                        Top Schauspieler
                    </h3>
                    <div class="space-y-4">
                        @foreach($stats['topActors'] as $actor)
                            <div class="flex items-center justify-between p-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-400 font-black text-xs">
                                        {{ substr($actor->last_name, 0, 1) }}
                                    </div>
                                    <span class="text-sm font-bold text-white/80">{{ $actor->full_name }}</span>
                                </div>
                                <span class="text-xs font-black text-white/30">{{ $actor->movies_count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
