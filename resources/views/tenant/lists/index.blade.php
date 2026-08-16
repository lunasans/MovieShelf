<x-app-layout>
    <div class="max-w-4xl mx-auto px-6 py-12">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-black text-white tracking-tight">{{ __('My lists') }}</h1>
                <p class="text-white/40 text-xs font-black uppercase tracking-[0.3em] mt-1">{{ __('Save movies without adding them to your collection') }}</p>
            </div>
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 glass border border-white/10 rounded-2xl text-xs font-black text-white/50 hover:text-white transition-all uppercase tracking-widest">
                <i class="bi bi-arrow-left mr-1"></i> {{ __('Back') }}
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-2xl text-green-400 text-sm font-bold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Create list form -->
        <div class="glass p-6 rounded-3xl border border-white/10 mb-8" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-3 text-white/60 hover:text-white transition-colors text-sm font-black uppercase tracking-widest">
                <i class="bi bi-plus-circle text-rose-500 text-lg"></i>
                {{ __('Create new list') }}
            </button>
            <form x-show="open" x-cloak @submit.prevent action="{{ route('lists.store') }}" method="POST" class="mt-4 flex gap-3">
                @csrf
                <input type="text" name="name" placeholder="{{ __('List name...') }}" required
                    class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/20 focus:outline-none focus:border-rose-500/50 text-sm">
                <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-black text-xs uppercase tracking-widest rounded-xl transition-all">
                    {{ __('Create') }}
                </button>
            </form>
        </div>

        @if($lists->isEmpty())
            <div class="glass p-12 rounded-3xl border border-white/10 text-center">
                <i class="bi bi-collection text-4xl text-white/10 block mb-4"></i>
                <p class="text-white/40 text-sm font-bold">{{ __('No lists yet.') }}</p>
                <p class="text-white/20 text-xs mt-1">{{ __('Create a list to save movies for later.') }}</p>
            </div>
        @else
            <div class="grid gap-4">
                @foreach($lists as $list)
                    <div class="glass border border-white/10 rounded-2xl p-5 flex items-center justify-between hover:border-rose-500/30 transition-all group">
                        <a href="{{ route('lists.show', $list) }}" class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="w-10 h-10 bg-rose-600/20 border border-rose-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-collection-fill text-rose-400 text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-black text-white group-hover:text-rose-400 transition-colors truncate">
                                    {{ $list->name }}
                                    @if($list->isShared())
                                        <span class="ml-2 px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 rounded-full text-[9px] font-black text-emerald-400 uppercase tracking-widest align-middle"><i class="bi bi-share mr-0.5"></i> {{ __('Shared') }}</span>
                                    @endif
                                </p>
                                @php($listCount = $list->movies_count + $list->external_movies_count)
                                <p class="text-white/30 text-xs mt-0.5">{{ $listCount }} {{ $listCount === 1 ? __('Movie') : __('Movies') }}</p>
                            </div>
                        </a>
                        <form action="{{ route('lists.destroy', $list) }}" method="POST" onsubmit="return confirm('{{ __('Really delete the list :name?', ['name' => addslashes($list->name)]) }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ml-4 p-2 text-white/20 hover:text-red-400 transition-colors rounded-xl hover:bg-red-500/10">
                                <i class="bi bi-trash text-sm"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
