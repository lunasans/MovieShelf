@php
    $layoutMode = auth()->user()->layout ?? 'classic';
@endphp

<x-app-layout>
    <style>
        [x-cloak] { display: none !important; }
        .glass-streaming {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(40px);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                inset 0 1px 1px 0 rgba(255, 255, 255, 0.05);
        }
    </style>

    <div class="{{ $layoutMode === 'streaming' ? 'streaming-actor-profile min-h-screen pt-32 pb-20 px-4 md:px-12 lg:px-24 relative overflow-hidden' : 'relative min-h-screen' }}">
        
        @if($layoutMode === 'streaming')
            {{-- Background Elements --}}
            <div class="fixed inset-0 z-0 pointer-events-none">
                <div class="absolute inset-0 bg-[#0c0c0e]"></div>
                @if($actor->profile_url)
                    <img src="{{ $actor->profile_url }}" class="absolute inset-0 w-full h-full object-cover opacity-20 blur-[100px] scale-110">
                @endif
                <div class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-blue-600/10 rounded-full blur-[120px]"></div>
                <div class="absolute bottom-[-10%] left-[-10%] w-[50%] h-[50%] bg-rose-600/10 rounded-full blur-[120px]"></div>
            </div>

            <div class="relative z-10">
                 <!-- Back Button -->
                 <div class="mb-16 animate-in slide-in-from-left duration-700">
                    <a href="{{ route('actors.index') }}" class="group inline-flex items-center gap-4 text-white/60 hover:text-white transition-all mb-10">
                        <div class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center bg-white/5 backdrop-blur-xl group-hover:border-white/30 group-hover:scale-110 transition-all">
                            <i class="bi bi-arrow-left text-xl"></i>
                        </div>
                        <span class="font-black uppercase tracking-widest text-sm italic">{{ __('Back to List') }}</span>
                    </a>
                 </div>

                 <div class="flex flex-col lg:flex-row gap-20 items-center lg:items-start">
                     <!-- Left Portrait Column -->
                     <div class="w-full lg:w-1/3 max-w-[450px]">
                         <div class="relative group">
                            <div class="absolute -inset-6 bg-blue-500/20 rounded-[3rem] blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
                            <div class="relative aspect-[2/3] rounded-[3rem] overflow-hidden glass-streaming border border-white/20 shadow-[0_0_100px_rgba(0,0,0,0.8)] transform rotate-[-2deg] hover:rotate-0 transition-all duration-700">
                                @if($actor->profile_url)
                                    <img src="{{ $actor->profile_url }}" alt="{{ $actor->full_name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-[#1a1a1f] flex items-center justify-center">
                                        <i class="bi bi-person-fill text-9xl text-white/5"></i>
                                    </div>
                                @endif

                                <!-- Hover Social Links -->
                                <div class="absolute inset-x-0 bottom-0 p-8 flex justify-center gap-6 opacity-0 group-hover:opacity-100 transition-all duration-500 translate-y-8 group-hover:translate-y-0 bg-gradient-to-t from-black/80 to-transparent">
                                    @if($actor->imdb_id)
                                        <a href="https://www.imdb.com/name/{{ $actor->imdb_id }}" target="_blank" class="w-14 h-14 glass hover:bg-yellow-500/80 rounded-2xl flex items-center justify-center text-white transition-all shadow-2xl border border-white/20 hover:scale-110 active:scale-90">
                                            <i class="bi bi-film"></i>
                                        </a>
                                    @endif
                                    <a href="https://www.themoviedb.org/person/{{ $actor->tmdb_id }}" target="_blank" class="w-14 h-14 glass hover:bg-emerald-500/80 rounded-2xl flex items-center justify-center text-white transition-all shadow-2xl border border-white/20 hover:scale-110 active:scale-90">
                                        <i class="bi bi-info-circle"></i>
                                    </a>
                                </div>
                            </div>
                         </div>
                         
                         <!-- Stats Grid -->
                         <div class="grid grid-cols-2 gap-6 mt-12 animate-in slide-in-from-bottom duration-700 delay-300">
                             <div class="glass-streaming p-6 rounded-[2rem] border border-white/10 flex flex-col items-center justify-center text-center">
                                <span class="text-white text-3xl font-black italic tracking-tighter">{{ $stats['total_movies'] }}</span>
                                <span class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em] mt-2 italic">{{ __('Filme') }}</span>
                             </div>
                             <div class="glass-streaming p-6 rounded-[2rem] border border-white/10 flex flex-col items-center justify-center text-center group hover:bg-blue-500/10 transition-colors">
                                <span class="text-blue-500 text-3xl font-black italic tracking-tighter group-hover:scale-110 transition-transform">{{ $actor->view_count }}</span>
                                <span class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em] mt-2 italic">{{ __('Aufrufe') }}</span>
                             </div>
                         </div>
                     </div>

                     <!-- Right Bio/Filmography Column -->
                     <div class="flex-1 w-full">
                         <div class="mb-16 animate-in slide-in-from-right duration-1000">
                            <h1 class="text-7xl md:text-9xl font-black text-white tracking-tighter leading-[0.8] mb-8 uppercase drop-shadow-2xl italic">
                                {{ $actor->first_name }}<br>
                                <span class="text-blue-500 drop-shadow-[0_0_50px_rgba(59,130,246,0.3)]">{{ $actor->last_name }}</span>
                            </h1>
                            <div class="h-2 w-32 bg-blue-600 rounded-full shadow-[0_0_30px_rgba(59,130,246,0.7)] mb-10"></div>
                            
                            <div class="flex items-center gap-6 text-white/40 text-xs font-black uppercase tracking-[0.4em] italic flex-wrap">
                                 @if($actor->birthday)
                                    <div class="flex items-center gap-3">
                                        <i class="bi bi-calendar-check text-blue-500"></i>
                                        <span>{{ \Carbon\Carbon::parse($actor->birthday)->format('d. F Y') }}</span>
                                    </div>
                                 @endif
                                 @if($actor->place_of_birth)
                                    <div class="flex items-center gap-3">
                                        <i class="bi bi-geo-alt text-rose-500"></i>
                                        <span>{{ $actor->place_of_birth }}</span>
                                    </div>
                                 @endif
                            </div>
                         </div>

                         @if($actor->bio)
                            <div class="mb-20 animate-in fade-in duration-1000 delay-500">
                                <h2 class="text-xs font-black uppercase tracking-[0.5em] text-white/20 mb-8 flex items-center gap-6 italic">
                                    {{ __('Biografie') }}
                                    <div class="h-px bg-white/5 flex-1"></div>
                                </h2>
                                <p class="text-white/70 leading-relaxed text-xl lg:text-2xl font-medium italic tracking-tight relative pl-10">
                                    <span class="absolute left-0 top-0 text-7xl text-blue-500/20 leading-none">"</span>
                                    {{ $actor->bio }}
                                </p>
                            </div>
                         @endif

                         <div>
                            <h2 class="text-xs font-black uppercase tracking-[0.5em] text-white/20 mb-10 flex items-center gap-6 italic">
                                {{ __('Sammlung') }}
                                <div class="h-px bg-white/5 flex-1"></div>
                            </h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-8">
                                @foreach($movies as $movie)
                                    <a href="{{ route('dashboard', ['movie' => $movie->id]) }}" class="group flex flex-col gap-5 animate-in zoom-in duration-700">
                                        <div class="relative aspect-[2/3] rounded-[2.5rem] overflow-hidden glass-streaming border border-white/5 transition-all duration-700 hover:scale-[1.08] hover:border-blue-500/50 shadow-2xl">
                                            @if($movie->cover_url)
                                                <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover transition-transform duration-[1.5s] group-hover:scale-110">
                                            @else
                                                <div class="w-full h-full bg-[#1a1a1f] flex items-center justify-center">
                                                    <i class="bi bi-film text-4xl text-white/5"></i>
                                                </div>
                                            @endif
                                            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black via-black/40 to-transparent z-10 opacity-60 group-hover:opacity-100 transition-opacity"></div>
                                            
                                            {{-- collection Badge --}}
                                            <div class="absolute bottom-5 left-5 z-20">
                                                <span class="text-[8px] font-black text-white uppercase tracking-widest bg-white/10 backdrop-blur-xl px-3 py-1.5 rounded-xl border border-white/20 shadow-xl">
                                                    {{ $movie->collection_type }}
                                                </span>
                                            </div>

                                            {{-- Role Overlay --}}
                                            <div class="absolute inset-0 flex flex-col justify-center items-center p-6 opacity-0 group-hover:opacity-100 transition-all duration-500 scale-90 group-hover:scale-100 bg-black/60 backdrop-blur-sm z-30">
                                                 <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white shadow-xl mb-4">
                                                     <i class="bi bi-info-lg text-xl"></i>
                                                 </div>
                                                 <span class="text-[10px] font-black text-white uppercase tracking-widest text-center">{{ $movie->pivot->role ?? 'Darsteller' }}</span>
                                            </div>
                                        </div>
                                        <div class="px-2 text-center lg:text-left">
                                            <h4 class="text-white font-black text-sm leading-none truncate group-hover:text-blue-400 transition-colors uppercase tracking-tight italic">{{ $movie->title }}</h4>
                                            <p class="text-[9px] font-black text-white/20 uppercase tracking-widest mt-2 group-hover:text-white/40 transition-all">{{ $movie->year }} • {{ $movie->runtime }}m</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                         </div>
                     </div>
                 </div>
            </div>
        @else
            <!-- Legacy Classic Mode -->
            <div class="absolute inset-0 h-[60vh] bg-gradient-to-b from-blue-600/10 to-transparent opacity-30 pointer-events-none"></div>
            <div class="max-w-7xl mx-auto px-8 py-20 relative z-10">
                {{-- Original show layout content --}}
                <div class="flex flex-col lg:flex-row gap-16">
                     <div class="w-full lg:w-1/3 flex flex-col items-center lg:items-start text-center lg:text-left">
                        <div class="relative group mb-10 w-full max-w-[350px]">
                            <div class="relative aspect-[2/3] rounded-[2rem] overflow-hidden glass border border-white/20 shadow-2xl transition-transform duration-700 italic">
                                @if($actor->profile_url)
                                    <img src="{{ $actor->profile_url }}" alt="{{ $actor->full_name }}" class="w-full h-full object-cover">
                                @endif
                                <div class="absolute bottom-6 left-0 right-0 flex justify-center gap-4 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                                    <a href="https://www.imdb.com/name/{{ $actor->imdb_id }}" target="_blank" class="w-10 h-10 glass rounded-xl flex items-center justify-center text-white border border-white/20">
                                        <i class="bi bi-film"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="w-full glass p-8 rounded-3xl border border-white/10">
                             <h2 class="text-xs font-black uppercase tracking-[0.3em] text-blue-400 mb-4">{{ __('Details') }}</h2>
                             <span class="text-white font-bold">{{ number_format($actor->view_count) }} {{ __('Aufrufe') }}</span>
                        </div>
                     </div>
                     <div class="w-full lg:w-2/3">
                        <h1 class="text-6xl md:text-8xl font-black text-white tracking-tighter mb-4 uppercase">{{ $actor->full_name }}</h1>
                        <p class="text-gray-300 italic">{{ $actor->bio }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6 mt-12">
                             @foreach($movies as $movie)
                                <a href="{{ route('dashboard', ['movie' => $movie->id]) }}" class="group">
                                    <div class="aspect-[2/3] rounded-3xl overflow-hidden glass border border-white/10 group-hover:scale-105 transition-all">
                                        @if($movie->cover_url)
                                            <img src="{{ $movie->cover_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                </a>
                             @endforeach
                        </div>
                     </div>
                </div>
            </div>
        @endif
    </div>
    @push('scripts')
    <script type="application/ld+json">
    {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush
</x-app-layout>