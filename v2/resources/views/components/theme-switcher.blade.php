<div x-data="{ 
    open: false, 
    currentTheme: '{{ session('theme', 'default') }}',
    themes: [
        { id: 'default', name: 'Standard', color: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' },
        { id: 'dark', name: 'Dark', color: 'linear-gradient(135deg, #bb86fc 0%, #3700b3 100%)' },
        { id: 'blue', name: 'Blue', color: 'linear-gradient(135deg, #00d4ff 0%, #0080ff 100%)' },
        { id: 'green', name: 'Green', color: 'linear-gradient(135deg, #00ff41 0%, #00aa2b 100%)' },
        { id: 'red', name: 'Red', color: 'linear-gradient(135deg, #ff4757 0%, #c0392b 100%)' },
        { id: 'purple', name: 'Purple', color: 'linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%)' }
    ],
    seasonal: [
        { id: 'christmas', name: '🎄 Weihnachten', color: 'linear-gradient(135deg, #c41e3a 0%, #165b33 100%)' },
        { id: 'halloween', name: '🎃 Halloween', color: 'linear-gradient(135deg, #ff6600 0%, #8b00ff 100%)' },
        { id: 'summer', name: '☀️ Sommer', color: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' }
    ],
    async setTheme(themeId) {
        this.currentTheme = themeId;
        document.documentElement.setAttribute('data-theme', themeId);
        
        try {
            const response = await fetch('{{ route('theme.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ theme: themeId })
            });
            
            if (response.ok) {
                this.open = false;
            }
        } catch (error) {
            console.error('Theme switch failed:', error);
        }
    }
}" class="fixed bottom-8 right-8 z-[100]">
    
    <!-- Pulse Effect Button -->
    <button @click="open = !open" 
            class="w-14 h-14 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-2xl shadow-blue-500/40 hover:scale-110 active:scale-95 transition-all group relative">
        <div class="absolute inset-0 rounded-full bg-blue-600 animate-ping opacity-20"></div>
        <i class="bi bi-palette-fill text-xl relative z-10 transition-transform group-hover:rotate-12"></i>
    </button>

    <!-- Theme Picker Panel -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-10 scale-95"
         @click.away="open = false"
         class="absolute bottom-20 right-0 w-72 glass-strong p-6 rounded-3xl shadow-2xl border border-white/10">
        
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xs font-black text-white uppercase tracking-widest flex items-center gap-2">
                <i class="bi bi-stars text-blue-400"></i>
                Farbschema
            </h3>
            <button @click="open = false" class="text-gray-500 hover:text-white transition-colors">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <!-- Theme Grid -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <template x-for="theme in themes" :key="theme.id">
                <button @click="setTheme(theme.id)" 
                        :class="currentTheme === theme.id ? 'border-blue-500/50 bg-white/5' : 'border-white/5 bg-white/[0.02]'"
                        class="p-3 rounded-2xl border flex flex-col items-center gap-2 hover:bg-white/[0.05] transition-all group">
                    <div :style="'background: ' + theme.color" class="w-10 h-10 rounded-lg shadow-inner group-hover:scale-110 transition-transform"></div>
                    <span class="text-[10px] font-bold text-gray-400 group-hover:text-white transition-colors" x-text="theme.name"></span>
                </button>
            </template>
        </div>

        <!-- Seasonal Header -->
        <div class="flex items-center gap-2 mb-4">
            <div class="h-px flex-1 bg-white/5"></div>
            <span class="text-[8px] font-black text-gray-600 uppercase tracking-widest">Saisonal</span>
            <div class="h-px flex-1 bg-white/5"></div>
        </div>

        <!-- Seasonal Row -->
        <div class="grid grid-cols-3 gap-2">
            <template x-for="theme in seasonal" :key="theme.id">
                <button @click="setTheme(theme.id)"
                        :class="currentTheme === theme.id ? 'border-blue-500/50 bg-white/5' : 'border-white/5 bg-white/[0.02]'"
                        class="p-2 rounded-xl border flex flex-col items-center gap-1.5 hover:bg-white/[0.05] transition-all group text-center">
                    <div :style="'background: ' + theme.color" class="w-6 h-6 rounded flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform"></div>
                    <span class="text-[7px] font-bold text-gray-500 group-hover:text-white transition-colors uppercase leading-tight" x-text="theme.name"></span>
                </button>
            </template>
        </div>

        <div class="mt-6 pt-4 border-t border-white/5 text-center">
            <p class="text-[8px] text-gray-600 font-bold uppercase tracking-widest">Einstellungen werden gespeichert</p>
        </div>
    </div>
</div>
