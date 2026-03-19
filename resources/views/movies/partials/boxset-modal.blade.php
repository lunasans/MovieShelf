<div x-show="boxsetOpen" 
     x-cloak
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/80 backdrop-blur-xl" @click="boxsetOpen = false"></div>

    <!-- Modal Content -->
    <div class="relative w-full max-w-4xl max-h-[90vh] glass-dark border border-white/10 rounded-[2.5rem] shadow-2xl flex flex-col overflow-hidden"
         x-show="boxsetOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4">
        
        <!-- Header -->
        <div class="px-8 py-6 border-b border-white/10 flex items-center justify-between shrink-0 bg-white/5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <i class="bi bi-collection-play text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-white uppercase tracking-tight leading-none" x-text="boxsetMovie?.title"></h2>
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-[0.2em] mt-2 italic">{{ __('Boxset Contents') }}</p>
                </div>
            </div>
            <button @click="boxsetOpen = false" class="w-10 h-10 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center text-white transition-all active:scale-95">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-grow overflow-y-auto p-8 no-scrollbar">
            <!-- Loading State (Skeletons) -->
            <div x-show="boxsetLoading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                @for($i = 0; $i < 10; $i++)
                <div class="animate-pulse">
                    <div class="aspect-[2/3] rounded-xl bg-white/5 border border-white/5"></div>
                    <div class="mt-2 h-2.5 w-20 bg-white/5 rounded mx-auto"></div>
                </div>
                @endfor
            </div>

            <!-- Children Grid -->
            <div x-show="!boxsetLoading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                <template x-for="child in boxsetChildren" :key="child.id">
                    <div class="group cursor-pointer" @click="selectBoxsetChild(child)">
                        <div class="aspect-[2/3] rounded-2xl overflow-hidden border border-white/10 shadow-lg transition-all duration-300 group-hover:scale-105 group-hover:border-indigo-500/50 group-hover:shadow-indigo-500/20 relative">
                            <img :src="child.cover_url" :alt="child.title" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-indigo-600/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <i class="bi bi-plus-lg text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h4 class="text-[11px] font-bold text-white leading-tight truncate group-hover:text-indigo-400 transition-colors uppercase tracking-tight" x-text="child.title"></h4>
                            <p class="text-[9px] text-gray-500 font-bold italic mt-0.5" x-text="child.year"></p>
                        </div>
                    </div>
                </template>
            </div>
            
            <!-- Empty State -->
            <div x-show="!boxsetLoading && boxsetChildren.length === 0" class="py-20 text-center opacity-40">
                <i class="bi bi-collection text-6xl block mb-4"></i>
                <p class="text-sm font-bold text-white uppercase tracking-widest">{{ __('No movies found in this boxset.') }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-8 py-4 bg-black/40 border-t border-white/5 flex justify-end shrink-0">
            <button @click="boxsetOpen = false" class="px-6 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-[10px] font-black text-white uppercase tracking-widest transition-all active:scale-95">
                {{ __('Close') }}
            </button>
        </div>
    </div>
</div>
