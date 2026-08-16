<x-app-layout>
    <div class="max-w-6xl mx-auto px-6 py-12">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-black text-white tracking-tight">{{ $list->name }}</h1>
                <p class="text-white/40 text-xs font-black uppercase tracking-[0.3em] mt-1">
                    {{ $items->count() }} {{ $items->count() === 1 ? __('Movie') : __('Movies') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Share -->
                <div x-data="{ open: false, copied: false }" class="relative">
                    <button @click="open = !open" class="px-4 py-2.5 glass border rounded-2xl text-xs font-black transition-all uppercase tracking-widest {{ $list->isShared() ? 'border-emerald-500/40 text-emerald-400' : 'border-white/10 text-white/50 hover:text-white' }}">
                        <i class="bi bi-share mr-1"></i> {{ $list->isShared() ? __('Shared') : __('Share') }}
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-50 mt-2 bg-gray-900 border border-white/10 rounded-2xl p-4 shadow-2xl" style="min-width: 320px">
                        @if($list->isShared())
                            <p class="text-white/50 text-xs font-bold mb-2">{{ __('Anyone with this link can view the list:') }}</p>
                            <div class="flex gap-2 mb-3">
                                <input type="text" readonly value="{{ route('lists.shared', $list->share_token) }}" x-ref="shareUrl"
                                    class="flex-1 bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-white/70 text-xs focus:outline-none">
                                <button type="button"
                                    @click="navigator.clipboard.writeText($refs.shareUrl.value); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="px-3 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-black rounded-xl transition-all">
                                    <span x-show="!copied"><i class="bi bi-clipboard"></i></span>
                                    <span x-show="copied" x-cloak><i class="bi bi-check-lg text-emerald-400"></i></span>
                                </button>
                            </div>
                            <form action="{{ route('lists.share.toggle', $list) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2 bg-red-600/20 hover:bg-red-600/40 border border-red-500/30 text-red-400 text-xs font-black rounded-xl transition-all uppercase tracking-widest">
                                    {{ __('Stop sharing') }}
                                </button>
                            </form>
                        @else
                            <p class="text-white/50 text-xs font-bold mb-3">{{ __('Create a public read-only link for this list.') }}</p>
                            <form action="{{ route('lists.share.toggle', $list) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl transition-all uppercase tracking-widest">
                                    <i class="bi bi-share mr-1"></i> {{ __('Create link') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <!-- Rename -->
                <div x-data="{ editing: false }">
                    <button @click="editing = !editing" class="px-4 py-2.5 glass border border-white/10 rounded-2xl text-xs font-black text-white/50 hover:text-white transition-all uppercase tracking-widest">
                        <i class="bi bi-pencil mr-1"></i> {{ __('Rename') }}
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
                    <i class="bi bi-arrow-left mr-1"></i> {{ __('Back') }}
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-2xl text-green-400 text-sm font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if($items->isEmpty())
            <div class="glass p-12 rounded-3xl border border-white/10 text-center">
                <i class="bi bi-collection text-4xl text-white/10 block mb-4"></i>
                <p class="text-white/40 text-sm font-bold">{{ __('This list is still empty.') }}</p>
                <p class="text-white/20 text-xs mt-1">{{ __('Add movies via the TMDb search in the admin area.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach($items as $movie)
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
                                <form action="{{ route('lists.remove-item', $list) }}" method="POST" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="item_type" value="{{ $movie->item_type }}">
                                    <input type="hidden" name="item_id" value="{{ $movie->id }}">
                                    <button type="submit"
                                        onclick="return confirm('{{ __('Remove movie from list?') }}')"
                                        class="w-full py-2 bg-red-600/80 hover:bg-red-500 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all">
                                        <i class="bi bi-trash mr-1"></i> {{ __('Remove') }}
                                    </button>
                                </form>
                            </div>

                            <div class="absolute top-2 right-2">
                                @if($movie->item_type === 'movie')
                                    <span class="px-2 py-0.5 bg-green-500/80 backdrop-blur-sm rounded-full text-[9px] font-black text-white uppercase tracking-widest">{{ __('Collection') }}</span>
                                @else
                                    <span class="px-2 py-0.5 bg-sky-500/80 backdrop-blur-sm rounded-full text-[9px] font-black text-white uppercase tracking-widest">{{ __('External') }}</span>
                                @endif
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs font-bold text-white/70 truncate">{{ $movie->title }}</p>
                        <p class="text-[10px] text-white/30">{{ $movie->year }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
