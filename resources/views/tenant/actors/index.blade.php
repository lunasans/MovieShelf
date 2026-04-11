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
        .alphabet-nav::-webkit-scrollbar { display: none; }
        .alphabet-nav { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <script>
        function actorGallery() {
            return {
                nextPageUrl: '{{ $actors->nextPageUrl() }}',
                isLoading: false,
                async loadMore() {
                    if (this.isLoading || !this.nextPageUrl) return;
                    this.isLoading = true;
                    try {
                        const response = await fetch(this.nextPageUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const html = await response.text();
                        if (html.trim() === '') {
                            this.nextPageUrl = null;
                            return;
                        }
                        const temp = document.createElement('div');
                        temp.innerHTML = html;
                        const grid = this.$refs.actorGrid;
                        while (temp.firstChild) {
                            grid.appendChild(temp.firstChild);
                        }
                        try {
                            const url = new URL(this.nextPageUrl);
                            const page = parseInt(url.searchParams.get('page')) + 1;
                            url.searchParams.set('page', page);
                            this.nextPageUrl = url.toString();
                        } catch (urlErr) {
                            this.nextPageUrl = null;
                        }
                    } catch (e) {
                        console.error('Failed to load more actors', e);
                    } finally {
                        this.isLoading = false;
                    }
                }
            }
        }
    </script>

    <div class="{{ $layoutMode === 'streaming' ? 'streaming-actors-view min-h-screen pt-32 pb-20 px-4 md:px-12 lg:px-24 relative' : 'px-8 py-10 min-h-screen' }}"
         x-data="actorGallery()">
        
        @if($layoutMode === 'streaming')
            {{-- Background Elements --}}
            <div class="fixed inset-0 z-0 pointer-events-none">
                <div class="absolute inset-0 bg-[#0c0c0e]"></div>
                <div class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-blue-600/10 rounded-full blur-[120px]"></div>
                <div class="absolute bottom-[-10%] left-[-10%] w-[50%] h-[50%] bg-purple-600/10 rounded-full blur-[120px]"></div>
            </div>

            <div class="relative z-10">
                <!-- Header Section -->
                <div class="mb-16 animate-in slide-in-from-left duration-700">
                    <a href="{{ route('dashboard') }}" class="group inline-flex items-center gap-4 text-white/60 hover:text-white transition-all mb-10">
                        <div class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center bg-white/5 backdrop-blur-xl group-hover:border-white/30 group-hover:scale-110 transition-all">
                            <i class="bi bi-arrow-left text-xl"></i>
                        </div>
                        <span class="font-black uppercase tracking-widest text-sm italic">{{ __('Back to Library') }}</span>
                    </a>

                    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-12">
                        <div class="flex-1">
                            <h1 class="text-6xl md:text-8xl font-black text-white tracking-tighter mb-4 drop-shadow-2xl uppercase">
                                {!! __('Our <span class="text-blue-500">Stars</span>') !!}
                            </h1>
                            <div class="flex items-center gap-4 mt-8 flex-wrap">
                                <span class="px-5 py-2 bg-blue-500/10 border border-blue-500/20 rounded-full text-[10px] font-black text-blue-400 uppercase tracking-widest shadow-xl">
                                    <i class="bi bi-people-fill mr-2"></i> {{ number_format($filteredActorsCount) }} / {{ number_format($totalActors) }} {{ __('Actors') }}
                                </span>
                                @if($letter)
                                    <a href="{{ route('actors.index', ['q' => request('q')]) }}" class="px-5 py-2 bg-rose-500/10 border border-rose-500/20 rounded-full text-[10px] font-black text-rose-400 uppercase tracking-widest hover:bg-rose-500/20 transition-all flex items-center gap-3 group shadow-xl">
                                        <i class="bi bi-funnel-fill"></i> {{ __('Letter: :letter', ['letter' => $letter]) }} <i class="bi bi-x-lg group-hover:rotate-90 transition-transform"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Search Form -->
                        <form action="{{ route('actors.index') }}" method="GET" class="relative w-full max-w-md group">
                            @if($letter) <input type="hidden" name="letter" value="{{ $letter }}"> @endif
                            <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl blur opacity-20 group-focus-within:opacity-50 transition duration-500"></div>
                            <input type="text" name="q" value="{{ request('q') }}"
                                placeholder="{{ __('Search actors...') }}"
                                class="relative w-full bg-[#0c0c0e]/80 border border-white/10 rounded-2xl px-6 py-5 pl-14 focus:ring-0 focus:border-white/30 text-white transition-all outline-none placeholder:text-white/20 backdrop-blur-xl">
                            <i class="bi bi-search absolute left-6 top-1/2 -translate-y-1/2 text-white/30 text-xl group-focus-within:text-blue-500 transition-colors"></i>
                        </form>
                    </div>
                </div>

                <!-- Alphabet Navigation -->
                <div class="relative z-20 mb-20 animate-in fade-in duration-1000 delay-300">
                    <div class="glass-streaming p-3 rounded-[2rem] border border-white/10 flex items-center gap-2 overflow-x-auto alphabet-nav px-4">
                        <a href="{{ route('actors.index', ['q' => request('q')]) }}"
                           class="px-8 py-3 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest transition-all {{ !$letter ? 'bg-blue-600 text-white shadow-xl shadow-blue-500/30 grow-0 shrink-0' : 'text-white/40 hover:text-white hover:bg-white/5 grow-0 shrink-0' }}">
                            {{ __('All') }}
                        </a>
                        <div class="h-6 w-[1px] bg-white/10 mx-2 shrink-0"></div>
                        @foreach(range('A', 'Z') as $char)
                            @php $hasActors = in_array($char, $availableLetters); @endphp
                            <a href="{{ $hasActors ? route('actors.index', ['letter' => $char, 'q' => request('q')]) : '#' }}"
                               class="w-11 h-11 shrink-0 flex items-center justify-center rounded-2xl text-[10px] font-black uppercase transition-all
                               {{ $letter === $char ? 'bg-blue-600 text-white shadow-xl shadow-blue-500/30' : ($hasActors ? 'text-white/40 hover:bg-white/10 hover:text-white' : 'text-white/10 cursor-not-allowed') }}">
                                {{ $char }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Grouped Actor Grid -->
                <div class="space-y-24" x-ref="actorGrid">
                    @include('tenant.actors.partials.actor-list', ['groupedActors' => $groupedActors])
                </div>

                <!-- Manual Load More -->
                <div x-show="nextPageUrl" class="mt-24 flex flex-col items-center justify-center gap-6">
                    <div x-show="isLoading" class="flex flex-col items-center gap-6 animate-in fade-in duration-500">
                        <div class="w-16 h-16 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin shadow-[0_0_30px_rgba(59,130,246,0.3)]"></div>
                        <span class="text-[12px] font-black text-white/30 uppercase tracking-[0.4em] italic animate-pulse">{{ __('Loading more actors...') }}</span>
                    </div>
                    <button x-show="!isLoading" @click="loadMore()" class="glass-streaming px-12 py-5 border border-white/10 rounded-3xl text-xs font-black text-white/40 hover:text-white hover:border-blue-500 transition-all uppercase tracking-[0.3em] italic group shadow-2xl">
                        <span>{{ __('Load more actors') }}</span>
                        <i class="bi bi-chevron-down ml-3 group-hover:translate-y-1 transition-transform inline-block"></i>
                    </button>
                </div>
            </div>
        @else
            <!-- Original Classic layout logic remains unchanged -->
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 uppercase">
                    <div>
                        <h1 class="text-4xl font-black text-white tracking-tighter mb-2 italic">
                            {!! __('Our <span class="text-blue-500">Stars</span>') !!}
                        </h1>
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 bg-blue-500/10 border border-blue-500/20 rounded-full text-[10px] font-black text-blue-400 uppercase tracking-widest italic">
                                <i class="bi bi-people-fill mr-1"></i> {{ number_format($filteredActorsCount) }} / {{ number_format($totalActors) }} {{ __('Actors') }}
                            </span>
                        </div>
                    </div>
                    <form action="{{ route('actors.index') }}" method="GET" class="relative w-full max-w-md">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search actors...') }}" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 pl-14 text-white glass outline-none">
                        <i class="bi bi-search absolute left-6 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    </form>
                </div>

                <div class="glass p-4 rounded-2xl border border-white/10 mb-12 overflow-x-auto no-scrollbar">
                    <div class="flex items-center justify-between gap-2 min-w-max px-2">
                        <a href="{{ route('actors.index', ['q' => request('q')]) }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ !$letter ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-white' }}">{{ __('All') }}</a>
                        @foreach(range('A', 'Z') as $char)
                            @php $hasActors = in_array($char, $availableLetters); @endphp
                            <a href="{{ $hasActors ? route('actors.index', ['letter' => $char, 'q' => request('q')]) : '#' }}" class="w-10 h-10 flex items-center justify-center rounded-xl text-xs font-black transition-all {{ $letter === $char ? 'bg-blue-600 text-white' : ($hasActors ? 'text-gray-300 hover:bg-white/10' : 'text-gray-700 cursor-not-allowed') }}">{{ $char }}</a>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-16">
                    @include('tenant.actors.partials.actor-list', ['groupedActors' => $groupedActors])
                </div>
                
                <div class="mt-20">
                    {{ $actors->appends(request()->except('page'))->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>