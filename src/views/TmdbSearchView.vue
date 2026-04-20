<template>
  <div class="p-8">
    <h1 class="text-2xl font-black text-[var(--text-main)] uppercase tracking-tight mb-1">TMDb Suche</h1>
    <p class="text-sm text-[var(--text-muted)] opacity-60 mb-6">Film in TMDb suchen und zur Sammlung hinzufügen</p>

    <!-- Toast -->
    <Transition name="toast">
      <div v-if="toast" class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-[var(--bg-elevated)] border border-[var(--status-green)]/30 rounded-2xl px-5 py-3.5 shadow-xl">
        <i class="bi bi-check-circle-fill text-[var(--status-green)] text-lg"></i>
        <span class="text-sm font-bold text-[var(--text-main)]">{{ toast }}</span>
      </div>
    </Transition>

    <!-- No API key and offline -->
    <div v-if="!canSearch" class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-ui)] p-8 text-center">
      <i class="bi bi-key text-4xl text-[var(--text-muted)] opacity-30 block mb-4"></i>
      <p class="text-sm font-bold text-[var(--text-main)] mb-1">Kein TMDb API Key hinterlegt</p>
      <p class="text-xs text-[var(--text-muted)] opacity-60 max-w-xs mx-auto mb-4">
        Bitte hinterlege einen TMDb API Key in den Einstellungen oder verbinde dich mit deiner MovieShelf.
      </p>
      <router-link to="/settings"
        class="inline-block px-5 py-2.5 bg-[var(--bg-elevated)] border border-[var(--border-ui)] rounded-xl text-xs font-black uppercase tracking-widest text-[var(--text-main)] hover:border-red-500/40 transition-all">
        Zu den Einstellungen →
      </router-link>
    </div>

    <template v-else>
      <!-- Search bar -->
      <div class="flex gap-3 mb-6">
        <input
          v-model="query"
          @keyup.enter="search"
          type="text"
          placeholder="Filmtitel eingeben..."
          class="flex-1 bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-xl px-4 py-3 text-sm text-[var(--text-main)] placeholder-[var(--text-muted)] focus:outline-none focus:border-red-500/50 transition-colors"
        />
        <button
          @click="search"
          :disabled="loading"
          class="bg-[var(--status-red)] hover:opacity-90 disabled:opacity-50 text-white font-bold px-5 py-3 rounded-xl transition-all text-sm"
        >
          Suchen
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-16">
        <div class="w-8 h-8 border-2 border-[var(--status-red)] border-t-transparent rounded-full animate-spin"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="bg-[var(--status-red-bg)] border border-[var(--status-red)]/20 rounded-2xl p-4 mb-6">
        <p class="text-[var(--status-red)] text-sm font-bold">{{ error }}</p>
      </div>

      <!-- Results -->
      <div v-else class="grid grid-cols-4 xl:grid-cols-6 gap-4">
        <div
          v-for="result in results"
          :key="result.id"
          class="group relative"
        >
          <div
            class="relative aspect-[2/3] rounded-2xl overflow-hidden bg-[var(--bg-card)] border transition-all duration-300"
            :class="importedIds.has(result.id)
              ? 'border-[var(--status-green)]/50'
              : 'border-[var(--border-ui)] hover:border-[var(--status-red)]/50 group-hover:scale-105'"
          >
            <img
              v-if="result.poster_path"
              :src="`https://image.tmdb.org/t/p/w300${result.poster_path}`"
              :alt="result.title"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <i class="bi bi-camera-video text-[var(--text-muted)] opacity-20 text-3xl"></i>
            </div>

            <!-- Imported overlay -->
            <div v-if="importedIds.has(result.id)" class="absolute inset-0 bg-black/50 flex items-center justify-center">
              <i class="bi bi-check-circle-fill text-[var(--status-green)] text-4xl"></i>
            </div>

            <!-- Hover overlay -->
            <div v-else class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-stretch justify-end p-2 gap-1.5">
              <span v-if="importing === result.id" class="text-[10px] font-black text-white uppercase tracking-widest flex items-center justify-center gap-1 py-1">
                <span class="w-3 h-3 border border-white border-t-transparent rounded-full animate-spin"></span>
                Lädt...
              </span>
              <template v-else>
                <button
                  @click.stop="importMovie(result)"
                  class="w-full bg-red-600 hover:bg-red-500 text-white text-[10px] font-black uppercase tracking-widest py-1.5 rounded-lg transition-colors"
                >
                  <i class="bi bi-plus-lg mr-1"></i>Sammlung
                </button>
                <button
                  @click.stop="listPickerFor = listPickerFor === result.id ? null : result.id"
                  class="w-full bg-white/20 hover:bg-white/30 text-white text-[10px] font-black uppercase tracking-widest py-1.5 rounded-lg transition-colors"
                >
                  <i class="bi bi-collection-fill mr-1"></i>Liste
                </button>
              </template>
            </div>
          </div>

          <!-- List picker dropdown -->
          <div
            v-if="listPickerFor === result.id"
            class="absolute z-50 top-full mt-1 left-0 right-0 bg-[var(--bg-elevated)] border border-[var(--border-ui)] rounded-xl shadow-xl overflow-hidden"
          >
            <div v-if="listStore.lists.length === 0" class="px-3 py-2 text-xs text-[var(--text-muted)] opacity-50">Keine Listen vorhanden</div>
            <button
              v-for="list in listStore.lists"
              :key="list.id"
              @click.stop="addToList(result, list.id)"
              class="w-full text-left px-3 py-2 text-xs font-bold text-[var(--text-main)] hover:bg-[var(--bg-card)] transition-colors truncate"
            >
              <i class="bi bi-collection-fill text-red-500 mr-2"></i>{{ list.name }}
            </button>
          </div>

          <p class="mt-2 text-xs font-bold truncate"
            :class="importedIds.has(result.id) ? 'text-[var(--status-green)]' : 'text-[var(--text-main)] opacity-70'">
            {{ result.title }}
          </p>
          <p class="text-[10px] text-[var(--text-muted)] opacity-50">{{ result.release_date?.slice(0, 4) }}</p>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useApi } from '@/composables/useApi'
import { useSettingsStore } from '@/stores/settings'
import { useListStore } from '@/stores/lists'

const TMDB_BASE = 'https://api.themoviedb.org/3'

interface TmdbResult {
  id: number
  title: string
  poster_path: string | null
  release_date: string
}

const settings   = useSettingsStore()
const listStore  = useListStore()
const { apiGet, apiPost, isOnline } = useApi()

const query       = ref('')
const results     = ref<TmdbResult[]>([])
const loading     = ref(false)
const importing   = ref<number | null>(null)
const importedIds = ref(new Set<number>())
const error       = ref('')
const toast       = ref('')
const listPickerFor = ref<number | null>(null)
let toastTimer: ReturnType<typeof setTimeout> | null = null

onMounted(() => listStore.fetchLists())

function showToast(message: string) {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = message
  toastTimer = setTimeout(() => { toast.value = '' }, 3000)
}

const canSearch = computed(() => isOnline.value || settings.hasTmdb)

async function search() {
  if (!query.value.trim()) return
  loading.value = true
  error.value = ''
  results.value = []
  importedIds.value = new Set()
  try {
    if (isOnline.value) {
      const data = await apiGet('/tmdb/search', { query: query.value })
      results.value = data.results ?? []
    } else {
      const { data } = await axios.get(`${TMDB_BASE}/search/movie`, {
        params: { api_key: settings.tmdbApiKey, query: query.value, language: 'de-DE' }
      })
      results.value = data.results ?? []
    }
    // Mark already-imported films
    if (results.value.length > 0) {
      const tmdbIds = results.value.map(r => r.id)
      const existing = await window.electron.db.movies.checkTmdbIds(tmdbIds)
      importedIds.value = new Set(existing)
    }
  } catch (e: any) {
    error.value = e?.response?.data?.status_message ?? e.message ?? 'Suche fehlgeschlagen.'
  } finally {
    loading.value = false
  }
}

async function importMovie(result: TmdbResult) {
  if (importing.value || importedIds.value.has(result.id)) return
  importing.value = result.id
  error.value = ''
  try {
    if (isOnline.value) {
      await apiPost('/tmdb/import', { tmdb_id: result.id, type: 'movie' })
    } else {
      await importLocally(result.id)
    }
    importedIds.value = new Set(importedIds.value).add(result.id)
    showToast(`„${result.title}" wurde zur Sammlung hinzugefügt.`)
  } catch (e: any) {
    error.value = `Import fehlgeschlagen: ${e?.response?.data?.status_message ?? e.message}`
  } finally {
    importing.value = null
  }
}

async function importLocally(tmdbId: number, inCollection = 1) {
  const [detailRes, videoRes] = await Promise.all([
    axios.get(`${TMDB_BASE}/movie/${tmdbId}`, {
      params: { api_key: settings.tmdbApiKey, language: 'de-DE', append_to_response: 'credits' }
    }),
    axios.get(`${TMDB_BASE}/movie/${tmdbId}/videos`, {
      params: { api_key: settings.tmdbApiKey, language: 'de-DE' }
    }).catch(() => ({ data: { results: [] } }))
  ])

  const m = detailRes.data
  const director = (m.credits?.crew ?? []).find((c: any) => c.job === 'Director')?.name ?? ''
  const actorsNames = (m.credits?.cast ?? []).slice(0, 10).map((c: any) => c.name).join(', ')
  const trailer = (videoRes.data.results ?? []).find(
    (v: any) => v.site === 'YouTube' && v.type === 'Trailer'
  )
  const trailerUrl = trailer ? `https://www.youtube.com/watch?v=${trailer.key}` : ''

  const movieData = {
    title:           m.title,
    year:            m.release_date ? parseInt(m.release_date.slice(0, 4)) : null,
    genre:           (m.genres ?? []).map((g: any) => g.name).join(', '),
    director,
    runtime:         m.runtime ?? null,
    rating:          m.vote_average ?? null,
    rating_age:      null,
    overview:        m.overview ?? '',
    cover_path:      m.poster_path ? `https://image.tmdb.org/t/p/w500${m.poster_path}` : null,
    backdrop_path:   m.backdrop_path ? `https://image.tmdb.org/t/p/w1280${m.backdrop_path}` : null,
    actors_names:    actorsNames,
    trailer_url:     trailerUrl,
    collection_type: 'Film',
    tag:             null,
    tmdb_id:         m.id,
    remote_id:       null,
    in_collection:   inCollection,
  }

  return await window.electron.db.movies.create(movieData)
}

async function addToList(result: TmdbResult, listId: number) {
  listPickerFor.value = null
  try {
    const existing = await window.electron.db.movies.getByRemoteId(result.id) as any
    let localId: number

    if (existing?.id) {
      localId = existing.id
    } else {
      // Vollständige TMDb-Infos holen, aber in_collection=0
      const created = await importLocally(result.id, 0) as any
      localId = created?.id
    }

    if (localId) {
      await listStore.addMovie(listId, localId)
      const listName = listStore.lists.find(l => l.id === listId)?.name ?? 'Liste'
      showToast(`„${result.title}" zu „${listName}" hinzugefügt.`)
    }
  } catch (e: any) {
    error.value = `Fehler: ${e.message}`
  }
}
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.25s ease; }
.toast-enter-from, .toast-leave-to       { opacity: 0; transform: translateY(12px); }
</style>
