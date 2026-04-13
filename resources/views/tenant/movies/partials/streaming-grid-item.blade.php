<div @click="window.location.href = '{{ route('movies.show', $movie) }}'" 
     class="aspect-[2/3] relative rounded-[2rem] overflow-hidden glass-streaming border border-white/10 group cursor-pointer hover:border-blue-500/50 hover:scale-105 transition-all duration-500 shadow-2xl">
    <img src="{{ $movie->cover_url }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
    {{-- Permanent subtle bottom shadow for legibility on light covers --}}
    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 transition-opacity duration-500 group-hover:opacity-0"></div>
    {{-- Hover gradient --}}
    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
    <!-- Media Tag Banderole (Top Right Corner) -->
    @if($movie->tag)
    @php
        $tagMap = [
            'DVD'       => ['label' => 'DVD',     'bg' => 'bg-orange-500'],
            'BluRay'    => ['label' => 'Blu-ray', 'bg' => 'bg-blue-500'],
            '4K'        => ['label' => '4K UHD',  'bg' => 'bg-cyan-500'],
            'Streaming' => ['label' => 'Stream',  'bg' => 'bg-green-500'],
            'Digital'   => ['label' => 'Digital', 'bg' => 'bg-purple-500'],
            'VHS'       => ['label' => 'VHS',     'bg' => 'bg-gray-500'],
            'Leihe'     => ['label' => 'Leihe',   'bg' => 'bg-yellow-500'],
        ];
        $tag = $tagMap[$movie->tag] ?? ['label' => $movie->tag, 'bg' => 'bg-white/50'];
    @endphp
    <div class="absolute top-[18px] -right-[30px] z-20 w-[115px] py-[5px] {{ $tag['bg'] }} rotate-45 text-center shadow-lg pointer-events-none">
        <span class="text-[9px] font-black text-white uppercase tracking-widest drop-shadow-sm">{{ $tag['label'] }}</span>
    </div>
    @endif
    <div class="absolute bottom-0 left-0 right-0 p-5 translate-y-4 group-hover:translate-y-0 transition-transform duration-500 text-center">
         <h4 class="text-[10px] font-black text-white uppercase tracking-wider mb-2 drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)] truncate">{{ $movie->title }}</h4>
         <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">
             <span class="text-[8px] font-black text-blue-400 uppercase tracking-widest">{{ $movie->year }}</span>
             <div class="h-1 w-1 bg-white/20 rounded-full"></div>
             <span class="text-[8px] font-black text-white/40 uppercase tracking-widest">{{ $movie->collection_type }}</span>
         </div>
    </div>
</div>
