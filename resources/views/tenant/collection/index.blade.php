<x-app-layout>
<div class="min-h-screen" x-data="{ isSearchFocused: false, filtersOpen: {{ collect(['genre','year_from','year_to','rating_min','runtime_max'])->filter(fn($k) => request()->filled($k))->count() > 0 ? 'true' : 'false' }} }">

    <!-- Public Collection Header -->
    <div class="border-b border-white/5 bg-black/20 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="bi bi-collection-play-fill text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em]">Öffentliche Sammlung</p>
                    <h1 class="text-sm font-black text-white leading-none">{{ $siteTitle }}</h1>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[10px] text-white/30 font-bold uppercase tracking-widest hidden sm:block">
                    {{ $movies->total() }} {{ $movies->total() === 1 ? 'Film' : 'Filme' }}
                </span>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2 glass border border-white/10 rounded-xl text-xs font-bold text-gray-400 hover:text-white transition-all">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span class="hidden sm:inline">Anmelden</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="max-w-7xl mx-auto px-6 pt-8 pb-4 flex flex-col gap-4">

        <!-- Search + Type Filters -->
        <div class="flex flex-col gap-4">
            <div class="relative group max-w-xl">
                <form action="{{ route('collection.index') }}" method="GET">
                    <input type="text" name="q" value="{{ request('q') }}"
                        @focus="isSearchFocused = true" @blur="isSearchFocused = false"
                        placeholder="In der Sammlung suchen..."
                        class="w-full bg-white/10 border border-white/20 rounded-[2rem] py-2.5 px-6 pl-14 text-sm transition-all placeholder:text-gray-400 backdrop-blur-2xl text-white focus:outline-none focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/10"
                        :class="isSearchFocused ? 'bg-white/20 border-blue-500/50' : ''"
                    >
                    <div class="absolute left-5 top-1/2 -translate-y-1/2">
                        <i class="bi bi-search text-lg text-gray-500 transition-colors" :class="isSearchFocused ? 'text-blue-400' : ''"></i>
                    </div>
                    @foreach(request()->except('q', 'page') as $key => $val)
                        @if($val)<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
                    @endforeach
                </form>
            </div>

            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-2 bg-white/5 border border-white/10 p-1.5 rounded-2xl overflow-x-auto no-scrollbar">
                    <a href="{{ route('collection.index', array_filter(request()->except('type', 'page'))) }}"
                       class="px-4 py-1.5 rounded-xl text-sm font-semibold transition-all {{ !request('type') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        Alle
                    </a>
                    @foreach($collectionTypes as $type)
                        <a href="{{ route('collection.index', array_filter(array_merge(request()->except('type', 'page'), ['type' => $type]))) }}"
                           class="px-4 py-1.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all {{ request('type') === $type ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            {{ $type }}
                        </a>
                    @endforeach
                </div>

                <button @click="filtersOpen = !filtersOpen"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all border shrink-0"
                    :class="filtersOpen ? 'bg-blue-500/10 border-blue-500/30 text-blue-400' : 'bg-white/5 border-white/10 text-gray-400 hover:text-white hover:bg-white/10'"
                >
                    <i class="bi bi-funnel-fill"></i>
                    <span class="uppercase tracking-widest">Filter</span>
                    @php $activeCount = collect(['genre','year_from','year_to','rating_min','runtime_max'])->filter(fn($k) => request()->filled($k))->count(); @endphp
                    @if($activeCount > 0)
                        <span class="bg-blue-500 text-white text-[9px] font-black rounded-full w-4 h-4 flex items-center justify-center">{{ $activeCount }}</span>
                    @endif
                    <i class="bi bi-chevron-down text-[10px] transition-transform duration-300" :class="filtersOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>
        </div>

        <!-- Filter Drawer -->
        <div x-show="filtersOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2">
            <form action="{{ route('collection.index') }}" method="GET" class="glass border border-white/10 rounded-2xl p-5 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="type" value="{{ request('type') }}">
                <div>
                    <label class="block text-[9px] font-black text-white/30 uppercase tracking-widest mb-2">Genre</label>
                    <select name="genre" class="w-full bg-white/5 border border-white/10 rounded-xl py-2 px-3 text-xs text-white focus:outline-none focus:border-blue-500/50 appearance-none cursor-pointer">
                        <option value="">Alle</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre }}" {{ request('genre') === $genre ? 'selected' : '' }} class="bg-zinc-900">{{ $genre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-white/30 uppercase tracking-widest mb-2">Jahr von</label>
                    <input type="number" name="year_from" value="{{ request('year_from') }}" placeholder="1900" min="1900" max="{{ date('Y') }}" class="w-full bg-white/5 border border-white/10 rounded-xl py-2 px-3 text-xs text-white focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-white/30 uppercase tracking-widest mb-2">Jahr bis</label>
                    <input type="number" name="year_to" value="{{ request('year_to') }}" placeholder="{{ date('Y') }}" min="1900" max="{{ date('Y') }}" class="w-full bg-white/5 border border-white/10 rounded-xl py-2 px-3 text-xs text-white focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-white/30 uppercase tracking-widest mb-2">Mind. Bewertung</label>
                    <input type="number" name="rating_min" value="{{ request('rating_min') }}" placeholder="0 – 10" min="0" max="10" step="0.5" class="w-full bg-white/5 border border-white/10 rounded-xl py-2 px-3 text-xs text-white focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-white/30 uppercase tracking-widest mb-2">Max. Laufzeit (Min.)</label>
                    <input type="number" name="runtime_max" value="{{ request('runtime_max') }}" placeholder="z.B. 120" min="1" class="w-full bg-white/5 border border-white/10 rounded-xl py-2 px-3 text-xs text-white focus:outline-none focus:border-blue-500/50 placeholder:text-gray-600">
                </div>
                <div class="col-span-2 md:col-span-3 lg:col-span-5 flex gap-3 pt-1">
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-blue-500/20">
                        <i class="bi bi-funnel mr-1"></i> Anwenden
                    </button>
                    <a href="{{ route('collection.index', array_filter(['q' => request('q'), 'type' => request('type')])) }}" class="px-5 py-2 bg-white/5 hover:bg-white/10 border border-white/10 text-gray-400 hover:text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                        <i class="bi bi-x-lg mr-1"></i> Zurücksetzen
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Movie Grid -->
    <div class="max-w-7xl mx-auto px-6 pb-16">
        @if($movies->isEmpty())
            <div class="py-20 text-center glass rounded-2xl border-dashed border border-white/10">
                <i class="bi bi-search text-6xl text-gray-800 mb-4 block"></i>
                <h3 class="text-xl font-bold text-white">Keine Filme gefunden.</h3>
                <p class="text-gray-500 mt-2">Filter anpassen oder Suche leeren.</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach($movies as $movie)
                    <a href="{{ route('movies.show', $movie) }}" class="group cursor-pointer block">
                        <div class="relative aspect-[2/3] rounded-3xl overflow-hidden glass border border-white/10 shadow-2xl transition-all duration-500 group-hover:scale-[1.05] group-hover:shadow-blue-500/30 group-hover:border-blue-500/50">
                            <div class="absolute inset-0 bg-gray-900 flex items-center justify-center">
                                @if($movie->cover_url)
                                    <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" loading="lazy">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
                                @else
                                    <i class="bi bi-film text-4xl text-white/5"></i>
                                @endif
                            </div>

                            <!-- Collection Type Badge -->
                            <div class="absolute bottom-3 left-3 z-10">
                                <span class="text-[9px] font-black text-white/90 uppercase tracking-widest glass px-2 py-1 rounded-lg border border-white/10 shadow-lg">
                                    {{ $movie->collection_type }}
                                </span>
                            </div>

                            <!-- Media Tag Banderole -->
                            @if($movie->tag)
                            @php
                                $tagMap = [
                                    'DVD'       => ['label' => 'DVD',     'bg' => 'bg-orange-800/80'],
                                    'BluRay'    => ['label' => 'Blu-ray', 'bg' => 'bg-blue-800/80'],
                                    '4K'        => ['label' => '4K UHD',  'bg' => 'bg-cyan-800/80'],
                                    'Streaming' => ['label' => 'Stream',  'bg' => 'bg-emerald-800/80'],
                                    'Digital'   => ['label' => 'Digital', 'bg' => 'bg-violet-800/80'],
                                    'VHS'       => ['label' => 'VHS',     'bg' => 'bg-stone-600/80'],
                                    'Leihe'     => ['label' => 'Leihe',   'bg' => 'bg-amber-800/80'],
                                ];
                                $tag = $tagMap[$movie->tag] ?? ['label' => $movie->tag, 'bg' => 'bg-black/50'];
                            @endphp
                            <div class="absolute top-[22px] -right-[55px] z-20 w-[180px] py-[5px] {{ $tag['bg'] }} rotate-45 text-center shadow-lg pointer-events-none">
                                <span class="text-[9px] font-black text-white uppercase tracking-widest drop-shadow-sm">{{ $tag['label'] }}</span>
                            </div>
                            @endif

                            <!-- Hover Icon -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-500 bg-blue-500/10">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white border border-white/30 transform scale-75 group-hover:scale-100 transition-transform duration-500">
                                    <i class="bi bi-info-circle text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 px-1">
                            <h3 class="text-[12px] font-black text-white leading-tight truncate group-hover:text-blue-400 transition-colors uppercase tracking-tight">{{ $movie->title }}</h3>
                            <div class="flex items-center gap-2 mt-1 opacity-60">
                                <span class="text-[10px] text-gray-400 font-bold italic">{{ $movie->year }}</span>
                                @if($movie->genre)
                                    <span class="w-1 h-1 bg-blue-500 rounded-full"></span>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter truncate">{{ Str::before($movie->genre, ',') }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($movies->hasPages())
                <div class="mt-12 flex justify-center">
                    <div class="flex items-center gap-2">
                        @if($movies->onFirstPage())
                            <span class="px-4 py-2 glass border border-white/5 rounded-xl text-xs font-bold text-gray-600 cursor-not-allowed">
                                <i class="bi bi-chevron-left"></i>
                            </span>
                        @else
                            <a href="{{ $movies->previousPageUrl() }}" class="px-4 py-2 glass border border-white/10 rounded-xl text-xs font-bold text-gray-400 hover:text-white hover:border-blue-500/30 transition-all">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        @endif

                        <span class="px-4 py-2 glass border border-white/10 rounded-xl text-xs font-bold text-white">
                            Seite {{ $movies->currentPage() }} / {{ $movies->lastPage() }}
                        </span>

                        @if($movies->hasMorePages())
                            <a href="{{ $movies->nextPageUrl() }}" class="px-4 py-2 glass border border-white/10 rounded-xl text-xs font-bold text-gray-400 hover:text-white hover:border-blue-500/30 transition-all">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @else
                            <span class="px-4 py-2 glass border border-white/5 rounded-xl text-xs font-bold text-gray-600 cursor-not-allowed">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
</x-app-layout>
