<template>
  <div class="p-8 max-w-xl">
    <h1 class="text-2xl font-black text-white uppercase tracking-tight mb-1">Synchronisation</h1>
    <p class="text-sm text-white/40 mb-8">Lokale Sammlung mit deiner MovieShelf abgleichen</p>

    <div v-if="!settings.isOnline" class="bg-yellow-500/10 border border-yellow-500/20 rounded-2xl p-6 text-center">
      <p class="text-yellow-400 text-sm font-bold mb-2">Nicht verbunden</p>
      <p class="text-white/40 text-xs">Bitte zuerst in den Einstellungen eine MovieShelf-Verbindung einrichten.</p>
      <router-link to="/settings" class="inline-block mt-4 text-blue-400 text-sm font-bold hover:underline">
        Zu den Einstellungen →
      </router-link>
    </div>

    <template v-else>
      <div class="bg-[#12121a] rounded-2xl border border-white/5 p-6 mb-4 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-bold text-white">Shelf → Desktop</p>
            <p class="text-xs text-white/40">Alle Filme aus deiner Shelf herunterladen</p>
          </div>
          <button
            @click="syncFromShelf"
            :disabled="syncing"
            class="bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white text-sm font-bold px-4 py-2 rounded-xl transition-colors"
          >
            {{ syncing ? 'Läuft...' : 'Sync starten' }}
          </button>
        </div>
      </div>

      <div v-if="log.length > 0" class="bg-[#12121a] rounded-2xl border border-white/5 p-4">
        <p class="text-xs font-black uppercase tracking-widest text-white/30 mb-3">Protokoll</p>
        <div class="space-y-1 max-h-64 overflow-y-auto">
          <p v-for="(line, i) in log" :key="i" class="text-xs font-mono text-white/60">{{ line }}</p>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { useApi } from '@/composables/useApi'

const settings = useSettingsStore()
const { apiGet } = useApi()

const syncing = ref(false)
const log = ref<string[]>([])

async function syncFromShelf() {
  syncing.value = true
  log.value = []
  try {
    log.value.push('Verbinde mit MovieShelf...')
    const data = await apiGet('/admin/export')
    log.value.push(`${data.count} Filme gefunden.`)

    for (const movie of data.movies) {
      await window.electron.db.movies.create({
        title:           movie.title,
        year:            movie.year,
        genre:           movie.genre,
        director:        movie.director,
        runtime:         movie.runtime,
        rating:          movie.rating,
        overview:        movie.overview,
        collection_type: movie.collection_type ?? 'Film',
        tag:             movie.tag,
        tmdb_id:         movie.tmdb_id,
        remote_id:       movie.id,
        cover_path:      movie.cover_url,
      })
    }

    log.value.push(`✓ Sync abgeschlossen. ${data.count} Filme importiert.`)
  } catch (e) {
    log.value.push('Fehler: ' + (e as Error).message)
  } finally {
    syncing.value = false
  }
}
</script>
