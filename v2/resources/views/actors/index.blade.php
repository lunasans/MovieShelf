<x-app-layout>
    <div class="px-8 py-10">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h1 class="text-4xl font-black text-white tracking-tighter mb-2">
                        Unsere <span class="text-blue-500">Stars</span>
                    </h1>
                    <p class="text-gray-400 font-medium">Entdecke die Darsteller deiner Sammlung</p>
                </div>

                <!-- Search Form -->
                <form action="{{ route('actors.index') }}" method="GET" class="relative w-full max-w-md">
                    <input type="text" name="q" value="{{ request('q') }}" 
                        placeholder="Schauspieler suchen..." 
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 pl-14 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-white transition-all outline-none placeholder:text-gray-500 glass">
                    <i class="bi bi-search absolute left-6 top-1/2 -translate-y-1/2 text-gray-500 text-xl"></i>
                </form>
            </div>

            <!-- Actors Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @forelse($actors as $actor)
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
                        <div class="absolute bottom-0 left-0 right-0 p-6 transform translate-y-2 group-hover:translate-y-0 transition-all duration-500">
                            <h3 class="text-white font-black text-[13px] leading-tight tracking-tight mb-2 uppercase group-hover:text-blue-400 transition-colors">{{ $actor->full_name }}</h3>
                            <div class="flex items-center gap-2 opacity-60 group-hover:opacity-100">
                                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-blue-400 italic">
                                    {{ $actor->movies_count }} {{ Str::plural('Film', $actor->movies_count) }}
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-20 text-center glass rounded-3xl border border-dashed border-white/10">
                        <i class="bi bi-people text-6xl text-gray-700 mb-4 block"></i>
                        <h3 class="text-xl font-bold text-gray-500">Keine Schauspieler gefunden</h3>
                        <p class="text-gray-600">Probiere es mit einem anderen Suchbegriff.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-12">
                {{ $actors->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
