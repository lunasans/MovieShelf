<x-app-layout>
    <div class="max-w-6xl mx-auto px-6 py-12">
        <div class="mb-8">
            <p class="text-white/40 text-xs font-black uppercase tracking-[0.3em] mb-1">
                <i class="bi bi-share mr-1"></i> Geteilte Liste
            </p>
            <h1 class="text-3xl font-black text-white tracking-tight">{{ $list->name }}</h1>
            <p class="text-white/40 text-xs font-black uppercase tracking-[0.3em] mt-1">
                {{ $items->count() }} {{ $items->count() === 1 ? 'Film' : 'Filme' }}
                @if($list->user)
                    &middot; von {{ $list->user->name }}
                @endif
            </p>
        </div>

        @if($items->isEmpty())
            <div class="glass p-12 rounded-3xl border border-white/10 text-center">
                <i class="bi bi-collection text-4xl text-white/10 block mb-4"></i>
                <p class="text-white/40 text-sm font-bold">Diese Liste ist noch leer.</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach($items as $movie)
                    <div class="group relative">
                        <div class="relative aspect-[2/3] rounded-2xl overflow-hidden glass border border-white/10 hover:border-rose-500/40 transition-all duration-300">
                            @if($movie->cover_url)
                                <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-3 text-center">
                                    <i class="bi bi-camera-video text-white/10 text-2xl mb-2"></i>
                                    <span class="text-[10px] text-white/30 font-bold leading-tight">{{ $movie->title }}</span>
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
