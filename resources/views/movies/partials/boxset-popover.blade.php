<div x-show="boxsetOpen" 
     x-cloak
     class="fixed bottom-0 left-0 right-0 z-[100] will-change-transform"
     x-transition:enter="transition cubic-bezier(0.32, 0.72, 0, 1) duration-500"
     x-transition:enter-start="opacity-0 translate-y-full"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition cubic-bezier(0.32, 0.72, 0, 1) duration-400"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-full">
    
    <!-- Backdrop (Subtle) -->
    <div class="absolute inset-0 bg-black/20 backdrop-blur-sm -z-10" @click="boxsetOpen = false"></div>

    <div class="glass-dark border-t border-white/10 rounded-t-[2.5rem] shadow-[0_-20px_50px_-12px_rgba(0,0,0,0.5)] overflow-hidden flex flex-col backdrop-blur-3xl max-w-7xl mx-auto">
        <!-- Pull Indicator -->
        <div class="w-12 h-1.5 bg-white/10 rounded-full mx-auto mt-4 mb-2"></div>
        <!-- Header -->
        <div class="px-10 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <i class="bi bi-collection-play text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-white uppercase tracking-wider" x-text="boxsetMovie?.title"></h2>
                    <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-widest mt-0.5" x-text="`${boxsetChildren.length} {{ __('Movies') }}`"></p>
                </div>
            </div>
            <button @click="boxsetOpen = false" class="w-10 h-10 rounded-full bg-white/5 hover:bg-white/10 text-white transition-all flex items-center justify-center">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="px-10 pb-10 pt-2 overflow-x-auto no-scrollbar">
            <!-- Loading State (Skeletons) -->
            <div x-show="boxsetLoading" class="flex gap-4 min-w-min">
                @for($i = 0; $i < 5; $i++)
                <div class="shrink-0 w-32 animate-pulse">
                    <div class="aspect-[2/3] rounded-xl bg-white/5 border border-white/5"></div>
                    <div class="mt-2 h-2.5 w-20 bg-white/5 rounded mx-auto"></div>
                    <div class="mt-1.5 h-2 w-12 bg-white/5 rounded mx-auto"></div>
                </div>
                @endfor
            </div>

            <!-- Children Horizontal Scroll -->
            <div x-show="!boxsetLoading" class="flex gap-4 min-w-min"
                 x-transition:enter="transition ease-out delay-150 duration-500"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0">
                <template x-for="child in boxsetChildren" :key="child.id">
                    <div class="group cursor-pointer shrink-0 w-32" @click="selectBoxsetChild(child)">
                        <div class="aspect-[2/3] rounded-xl overflow-hidden border border-white/10 shadow-lg transition-all duration-300 group-hover:scale-105 group-hover:border-indigo-500/50 relative">
                            <img :src="child.cover_url" :alt="child.title" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-indigo-600/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <i class="bi bi-plus-lg text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-2 text-center">
                            <h4 class="text-[10px] font-bold text-white leading-tight truncate px-1 group-hover:text-indigo-400 transition-colors uppercase" x-text="child.title"></h4>
                            <p class="text-[8px] text-gray-500 font-bold italic" x-text="child.year"></p>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="!boxsetLoading && boxsetChildren.length === 0" class="py-10 text-center opacity-30">
                <p class="text-xs font-bold text-white uppercase tracking-widest">{{ __('No movies found.') }}</p>
            </div>
        </div>
    </div>
</div>
