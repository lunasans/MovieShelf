<x-app-layout>
    <div class="max-w-6xl mx-auto px-6 py-12">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-black text-white tracking-tight">{{ $list->name }}</h1>
                <p class="text-white/40 text-xs font-black uppercase tracking-[0.3em] mt-1">
                    {{ $list->movies->count() }} {{ $list->movies->count() === 1 ? 'Film' : 'Filme' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Rename -->
                <div x-data="{ editing: false }">
                    <button @click="editing = !editing" class="px-4 py-2.5 glass border border-white/10 rounded-2xl text-xs font-black text-white/50 hover:text-white transition-all uppercase tracking-widest">
                        <i class="bi bi-pencil mr-1"></i> Umbenennen
                    </button>
                    <div x-show="editing" x-cloak class="absolute z-50 mt-2 bg-gray-900 border border-white/10 rounded-2xl p-4 shadow-2xl" style="min-width: 280px">
                        <form action="{{ route('lists.update', $list) }}" method="POST" class="flex gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="name" value="{{ $list->name }}" required
                                class="flex-1 bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-rose-500/50">
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-black text-xs rounded-xl transition-all uppercase tracking-widest">OK</button>
                        </form>
                    </div>
                </div>
                <a href="{{ route('lists.index') }}" class="px-4 py-2.5 glass border border-white/10 rounded-2xl text-xs font-black text-white/50 hover:text-white transition-all uppercase tracking-widest">
                    <i class="bi bi-arrow-left mr-1"></i> Zurück
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-2xl text-green-400 text-sm font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if($list->movies->isEmpty())
            <div class="glass p-12 rounded-3xl border border-white/10 text-center">
                <i class="bi bi-collection text-4xl text-white/10 block mb-4"></i>
                <p class="text-white/40 text-sm font-bold">Diese Liste ist noch leer.</p>
                <p class="text-white/20 text-xs mt-1">Füge Filme über die TMDb-Suche im Admin-Bereich hinzu.</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach($list->movies as $movie)
                    <div class="group relative">
                        <div class="relative aspect-[2/3] rounded-2xl overflow-hidden glass border border-white/10 hover:border-rose-500/40 transition-all duration-300">
                            @if($movie->cover_url)
                                <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-3 text-center">
                                    <i class="bi bi-camera-video text-white/10 text-2xl mb-2"></i>
                                    <span class="text-[10px] text-white/30 font-bold leading-tight">{{ $movie->title }}</span>
                                </div>
                            @endif

                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
                                <form action="{{ route('lists.remove-movie', [$list, $movie]) }}" method="POST" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Film von Liste entfernen?')"
                                        class="w-full py-2 bg-red-600/80 hover:bg-red-500 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all">
                                        <i class="bi bi-trash mr-1"></i> Entfernen
                                    </button>
                                </form>
                            </div>

                            @if($movie->in_collection)
                                <div class="absolute top-2 right-2">
                                    <span class="px-2 py-0.5 bg-green-500/80 backdrop-blur-sm rounded-full text-[9px] font-black text-white uppercase tracking-widest">Sammlung</span>
                                </div>
                            @endif
                        </div>
                        <p class="mt-1.5 text-xs font-bold text-white/70 truncate">{{ $movie->title }}</p>
                        <p class="text-[10px] text-white/30">{{ $movie->year }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
