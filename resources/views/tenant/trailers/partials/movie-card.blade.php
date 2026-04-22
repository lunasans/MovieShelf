@php
    $layoutMode = auth()->user()->layout ?? 'classic';
@endphp

<div class="group relative flex flex-col gap-4 animate-in fade-in zoom-in duration-500">
    <!-- Preview Card -->
    <div @click="openTrailer('{{ addslashes($movie->title) }}', '{{ $movie->trailer_url }}')" 
         class="relative aspect-video rounded-[2rem] overflow-hidden {{ $layoutMode === 'streaming' ? 'glass-streaming border-white/10 shadow-2xl skew-x-[-1deg] group-hover:skew-x-0' : 'glass border-white/5' }} transition-all duration-700 hover:scale-[1.05] hover:border-blue-500/50 shadow-2xl group/card cursor-pointer">
        
        <!-- Backdrop Image -->
        @if($movie->backdrop_url)
            <img src="{{ $movie->backdrop_url }}" alt="{{ $movie->title }}" 
                 class="w-full h-full object-cover {{ $layoutMode === 'streaming' ? 'opacity-70' : 'opacity-60' }} group-hover/card:scale-110 group-hover/card:opacity-90 transition-all duration-1000">
        @elseif($movie->cover_url)
            <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" 
                 class="w-full h-full object-cover blur-sm opacity-40 group-hover/card:scale-110 group-hover/card:opacity-60 transition-all duration-1000">
        @else
            <div class="w-full h-full bg-[#1a1a1f] flex items-center justify-center">
                <i class="bi bi-film text-4xl text-white/5"></i>
            </div>
        @endif

        <!-- Play Overlay -->
        <div class="absolute inset-0 flex items-center justify-center opacity-100 transition-all duration-500 bg-black/60 backdrop-blur-[4px]">
            <img src="{{ asset('img/play.png') }}" alt="Play"
                 class="w-24 h-24 transition-all duration-500 hover:scale-110 active:scale-90 drop-shadow-[0_0_30px_rgba(220,38,38,0.6)]">
        </div>

        <!-- Info Badge -->
        <div class="absolute bottom-6 left-6 flex items-center gap-2">
            @if($movie->rating)
             <div class="px-3 py-1.5 bg-yellow-500/90 backdrop-blur-md rounded-xl text-[10px] font-black text-black border border-white/10 shadow-xl scale-90 group-hover/card:scale-100 transition-transform duration-500">
                <i class="bi bi-star-fill mr-1"></i> {{ number_format($movie->rating, 1) }}
             </div>
            @endif
             <div class="px-4 py-1.5 {{ $layoutMode === 'streaming' ? 'bg-white/10 border-white/20' : 'glass border-white/10' }} backdrop-blur-xl rounded-xl text-[10px] font-black text-white italic uppercase tracking-[0.2em] border shadow-xl scale-90 group-hover/card:scale-100 transition-transform duration-500">
                {{ $movie->year }}
             </div>
        </div>

        {{-- Type Badge --}}
        <div class="absolute top-6 left-6 opacity-0 group-hover/card:opacity-100 transition-opacity duration-700">
            <span class="px-3 py-1 bg-rose-600 text-white text-[9px] font-black uppercase tracking-widest rounded-lg shadow-lg">
                {{ $movie->collection_type }}
            </span>
        </div>
    </div>

    <!-- Info Section -->
    <div class="px-4">
        <h3 class="text-white font-black text-xl leading-tight tracking-tighter mb-2 truncate group-hover:text-blue-400 transition-colors uppercase italic">
            {{ $movie->title }}
        </h3>
        <div class="flex items-center justify-between">
            <div @click.stop="window.location.href = '{{ route('dashboard', ['movie' => $movie->id]) }}'" 
                 class="flex items-center gap-3 text-[10px] font-black text-white/40 uppercase tracking-[0.2em] cursor-pointer hover:text-blue-400 transition-all italic w-fit group/btn">
                <span class="border-b border-white/10 group-hover/btn:border-blue-500/50 pb-0.5">{{ __('Details ansehen') }}</span>
                <i class="bi bi-arrow-right-short text-lg group-hover/btn:translate-x-1 transition-transform"></i>
            </div>
            
            <span class="text-[10px] font-black text-white/10 uppercase tracking-widest">{{ $movie->runtime }}m</span>
        </div>
    </div>
</div>