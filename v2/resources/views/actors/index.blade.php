<x-app-layout>
    <div class="px-8 py-10">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-4xl font-black text-white tracking-tighter mb-2 uppercase">
                        Unsere <span class="text-blue-500">Stars</span>
                    </h1>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-blue-500/10 border border-blue-500/20 rounded-full text-[10px] font-black text-blue-400 uppercase tracking-widest">
                            <i class="bi bi-people-fill mr-1"></i> {{ number_format($filteredActorsCount) }} / {{ number_format($totalActors) }} Darsteller
                        </span>
                        @if($letter)
                            <a href="{{ route('actors.index', ['q' => request('q')]) }}" class="px-3 py-1 bg-rose-500/10 border border-rose-500/20 rounded-full text-[10px] font-black text-rose-400 uppercase tracking-widest hover:bg-rose-500/20 transition-all flex items-center gap-2">
                                <i class="bi bi-funnel-fill"></i> Buchstabe: {{ $letter }} <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Search Form -->
                <form action="{{ route('actors.index') }}" method="GET" class="relative w-full max-w-md">
                    @if($letter) <input type="hidden" name="letter" value="{{ $letter }}"> @endif
                    <input type="text" name="q" value="{{ request('q') }}" 
                        placeholder="Schauspieler suchen..." 
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 pl-14 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-white transition-all outline-none placeholder:text-gray-500 glass">
                    <i class="bi bi-search absolute left-6 top-1/2 -translate-y-1/2 text-gray-500 text-xl"></i>
                </form>
            </div>

            <!-- Alphabet Navigation -->
            <div class="glass p-4 rounded-2xl border border-white/10 mb-12 overflow-x-auto no-scrollbar">
                <div class="flex items-center justify-between gap-2 min-w-max px-2">
                    <a href="{{ route('actors.index', ['q' => request('q')]) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ !$letter ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-gray-500 hover:text-white hover:bg-white/5' }}">
                        Alle
                    </a>
                    
                    <div class="h-4 w-[1px] bg-white/10 mx-2"></div>
                    
                    @foreach(range('A', 'Z') as $char)
                        @php $hasActors = in_array($char, $availableLetters); @endphp
                        <a href="{{ $hasActors ? route('actors.index', ['letter' => $char, 'q' => request('q')]) : '#' }}" 
                           class="w-10 h-10 flex items-center justify-center rounded-xl text-xs font-black uppercase transition-all 
                           {{ $letter === $char ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : ($hasActors ? 'text-gray-300 hover:bg-white/10 hover:text-white' : 'text-gray-700 cursor-not-allowed') }}">
                            {{ $char }}
                        </a>
                    @endforeach

                    @php $hasSpecial = in_array('#', $availableLetters); @endphp
                    <a href="{{ $hasSpecial ? route('actors.index', ['letter' => '#', 'q' => request('q')]) : '#' }}" 
                       class="w-10 h-10 flex items-center justify-center rounded-xl text-xs font-black transition-all
                       {{ $letter === '#' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : ($hasSpecial ? 'text-gray-300 hover:bg-white/10 hover:text-white' : 'text-gray-700 cursor-not-allowed') }}">
                        #
                    </a>
                </div>
            </div>

            <!-- Grouped Actor Grid -->
            <div class="space-y-16">
                @forelse($groupedActors as $groupLetter => $groupActors)
                    <div class="animate-in fade-in slide-in-from-bottom-8 duration-700">
                        <div class="flex items-center gap-6 mb-8">
                            <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center text-white text-2xl font-black shadow-xl shadow-blue-500/20">
                                {{ $groupLetter }}
                            </div>
                            <div class="flex-1 h-[1px] bg-gradient-to-r from-white/20 to-transparent"></div>
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-[0.3em] italic">
                                {{ $groupActors->count() }} Darsteller
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
                            @foreach($groupActors as $actor)
                                <a href="{{ route('dashboard', ['actor' => $actor->id]) }}" class="group relative aspect-[2/3] rounded-3xl overflow-hidden glass border border-white/10 transition-all duration-500 hover:scale-[1.05] hover:border-blue-500/50 shadow-2xl">
                                    @if($actor->profile_path)
                                        <img src="{{ asset('storage/' . $actor->profile_path) }}" alt="{{ $actor->full_name }}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                                            <i class="bi bi-person-fill text-6xl text-white/5"></i>
                                        </div>
                                    @endif
                                    
                                    <!-- Premium Overlay -->
                                    <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black via-black/40 to-transparent opacity-90 group-hover:opacity-100 transition-opacity"></div>
                                    
                                    <!-- Info Section -->
                                    <div class="absolute bottom-0 left-0 right-0 p-5 transform translate-y-2 group-hover:translate-y-0 transition-all duration-500">
                                        <h3 class="text-white font-black text-[12px] leading-tight tracking-tight mb-2 uppercase group-hover:text-blue-400 transition-colors truncate">
                                            {{ $actor->full_name }}
                                        </h3>
                                        <div class="flex items-center gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <span class="text-[8px] font-black uppercase tracking-[0.2em] text-blue-400 italic">
                                                {{ $actor->movies_count }} {{ Str::plural('Film', $actor->movies_count) }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="py-20 text-center glass rounded-3xl border border-dashed border-white/10 animate-in fade-in duration-1000">
                        <i class="bi bi-people text-6xl text-gray-700 mb-4 block"></i>
                        <h3 class="text-xl font-black text-white uppercase tracking-tighter">Keine Schauspieler gefunden</h3>
                        <p class="text-gray-500 mt-2 font-medium italic">Probiere es mit einem anderen Buchstaben oder Suchbegriff.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-20">
                {{ $actors->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>

    <!-- Custom CSS for no-scrollbar -->
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</x-app-layout>
