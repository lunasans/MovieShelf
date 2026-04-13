<template>
  <div
    class="flex items-center justify-between h-10 px-4 bg-[#0d0d14] border-b border-white/5 select-none"
    style="-webkit-app-region: drag"
  >
    <!-- Logo -->
    <div class="flex items-center gap-2" style="-webkit-app-region: no-drag">
      <span class="text-blue-500 text-lg">🎬</span>
      <span class="text-sm font-bold text-white/80 tracking-widest uppercase">MovieShelf</span>
    </div>

    <!-- Mode indicator -->
    <div class="flex items-center gap-2">
      <span
        :class="isOnline ? 'bg-green-500/20 text-green-400 border-green-500/30' : 'bg-gray-500/20 text-gray-400 border-gray-500/30'"
        class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full border"
      >
        {{ isOnline ? 'Online · ' + shelfUrl : 'Offline' }}
      </span>
    </div>

    <!-- Window controls -->
    <div class="flex items-center gap-1" style="-webkit-app-region: no-drag">
      <button @click="minimize" class="w-7 h-7 rounded-lg hover:bg-white/10 flex items-center justify-center text-white/50 hover:text-white transition-colors">
        <svg width="10" height="2" viewBox="0 0 10 2" fill="currentColor"><rect width="10" height="2"/></svg>
      </button>
      <button @click="maximize" class="w-7 h-7 rounded-lg hover:bg-white/10 flex items-center justify-center text-white/50 hover:text-white transition-colors">
        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="0.75" y="0.75" width="8.5" height="8.5"/></svg>
      </button>
      <button @click="close" class="w-7 h-7 rounded-lg hover:bg-red-500/80 flex items-center justify-center text-white/50 hover:text-white transition-colors">
        <svg width="10" height="10" viewBox="0 0 10 10" stroke="currentColor" stroke-width="1.5"><line x1="1" y1="1" x2="9" y2="9"/><line x1="9" y1="1" x2="1" y2="9"/></svg>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useSettingsStore } from '@/stores/settings'

const settings = useSettingsStore()

const isOnline = computed(() => settings.mode === 'online')
const shelfUrl = computed(() => settings.shelfUrl)

const minimize = () => window.electron.window.minimize()
const maximize = () => window.electron.window.maximize()
const close    = () => window.electron.window.close()
</script>
