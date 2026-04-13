<template>
  <div class="p-8">
    <h1 class="text-2xl font-black text-white uppercase tracking-tight mb-1">TMDb Suche</h1>
    <p class="text-sm text-white/40 mb-6">Film in TMDb suchen und zur Sammlung hinzufügen</p>

    <div class="flex gap-3 mb-6">
      <input
        v-model="query"
        @keyup.enter="search"
        type="text"
        placeholder="Filmtitel eingeben..."
        class="flex-1 bg-[#12121a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-blue-500/50"
      />
      <button
        @click="search"
        :disabled="loading"
        class="bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-bold px-5 py-3 rounded-xl transition-colors text-sm"
      >
        Suchen
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <div class="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div v-else class="grid grid-cols-4 xl:grid-cols-6 gap-4">
      <div
        v-for="result in results"
        :key="result.id"
        class="group cursor-pointer"
        @click="importMovie(result)"
      >
        <div class="relative aspect-[2/3] rounded-2xl overflow-hidden bg-[#12121a] border border-white/10 hover:border-blue-500/50 transition-all duration-300 group-hover:scale-105">
          <img
            v-if="result.poster_path"
            :src="`https://image.tmdb.org/t/p/w300${result.poster_path}`"
            :alt="result.title"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <span class="text-white/20 text-3xl">🎬</span>
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
            <span class="text-[10px] font-black text-white uppercase tracking-widest">Hinzufügen</span>
          </div>
        </div>
        <p class="mt-2 text-xs font-bold text-white/70 truncate">{{ result.title }}</p>
        <p class="text-[10px] text-white/30">{{ result.release_date?.slice(0, 4) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useApi } from '@/composables/useApi'
import { useSettingsStore } from '@/stores/settings'
import axios from 'axios'

const settings = useSettingsStore()
const { apiGet, apiPost, isOnline } = useApi()

const query   = ref('')
const results = ref<Array<{ id: number; title: string; poster_path: string | null; release_date: string }>>([])
const loading = ref(false)

async function search() {
  if (!query.value.trim()) return
  loading.value = true
  try {
    if (isOnline.value) {
      const data = await apiGet('/tmdb/search', { query: query.value })
      results.value = data.results ?? []
    } else {
      // No TMDb key in standalone mode — show hint
      results.value = []
      alert('TMDb-Suche erfordert eine Verbindung zur MovieShelf (Online-Modus).')
    }
  } finally {
    loading.value = false
  }
}

async function importMovie(result: { id: number; title: string }) {
  if (!isOnline.value) {
    alert('Import erfordert eine Verbindung zur MovieShelf.')
    return
  }
  await apiPost('/tmdb/import', { tmdb_id: result.id, type: 'movie' })
  alert(`"${result.title}" wurde importiert.`)
}
</script>
