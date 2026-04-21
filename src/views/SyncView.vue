<template>
  <div class="p-8 max-w-3xl mx-auto">
    <h1 class="text-2xl font-black text-[var(--text-main)] uppercase tracking-tight mb-1">Synchronisation</h1>
    <p class="text-sm text-[var(--text-muted)] opacity-60 mb-8">Lokale Sammlung mit deiner MovieShelf abgleichen</p>

    <!-- Not connected -->
    <div v-if="!settings.isOnline" class="bg-[var(--status-yellow-bg)] border border-[var(--status-yellow)]/20 rounded-2xl p-8 text-center">
      <i class="bi bi-cloud-slash text-3xl text-[var(--status-yellow)] block mb-3"></i>
      <p class="text-[var(--status-yellow)] text-base font-black uppercase tracking-tight mb-2">Nicht verbunden</p>
      <p class="text-[var(--text-muted)] opacity-70 text-sm max-w-xs mx-auto mb-6">Bitte zuerst in den Einstellungen eine MovieShelf-Verbindung einrichten.</p>
      <router-link to="/settings" class="inline-block px-6 py-3 bg-[var(--bg-elevated)] border border-[var(--border-ui)] rounded-xl text-[var(--status-red)] text-sm font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all">
        Zu den Einstellungen →
      </router-link>
    </div>

    <template v-else>

      <!-- Status row -->
      <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-4">
          <p class="text-xs text-[var(--text-muted)] opacity-50 font-bold uppercase tracking-widest mb-1">Lokal</p>
          <p class="text-2xl font-black text-[var(--text-main)]">{{ localCount }}</p>
          <p class="text-xs text-[var(--text-muted)] opacity-50 mt-0.5">Filme</p>
        </div>
        <div class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-4">
          <p class="text-xs text-[var(--text-muted)] opacity-50 font-bold uppercase tracking-widest mb-1">Ausstehend</p>
          <p class="text-2xl font-black" :class="dirtyCount > 0 ? 'text-[var(--status-yellow)]' : 'text-[var(--text-main)]'">{{ dirtyCount }}</p>
          <p class="text-xs text-[var(--text-muted)] opacity-50 mt-0.5">Änderungen</p>
        </div>
        <div class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-4">
          <p class="text-xs text-[var(--text-muted)] opacity-50 font-bold uppercase tracking-widest mb-1">Letzter Sync</p>
          <p class="text-sm font-black text-[var(--text-main)] leading-tight mt-1">{{ lastSyncLabel }}</p>
        </div>
      </div>

      <!-- Active sync progress -->
      <div v-if="phase !== 'idle'" class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-[var(--status-red)]/10 flex items-center justify-center">
              <i class="bi bi-arrow-repeat text-[var(--status-red)] animate-spin"></i>
            </div>
            <div>
              <p class="text-sm font-black text-[var(--text-main)]">{{ phaseLabel }}</p>
              <p class="text-xs text-[var(--text-muted)] opacity-50">{{ phaseDetail }}</p>
            </div>
          </div>
          <span class="text-xs font-black text-[var(--text-muted)] opacity-40">{{ progressPct }}%</span>
        </div>
        <div class="w-full bg-[var(--bg-app)] rounded-full h-1.5 overflow-hidden">
          <div class="h-1.5 bg-[var(--status-red)] rounded-full transition-all duration-300" :style="{ width: progressPct + '%' }"></div>
        </div>
      </div>

      <!-- Pull preview -->
      <div v-if="preview && phase === 'idle'" class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl mb-6 overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--border-ui)]">
          <div class="flex items-center gap-3">
            <i class="bi bi-eye text-[var(--status-red)]"></i>
            <p class="text-sm font-black text-[var(--text-main)]">Vorschau: Änderungen vom Shelf</p>
          </div>
          <div class="flex items-center gap-3">
            <span v-if="preview.new > 0"    class="text-xs font-bold text-[var(--status-green)] bg-[var(--status-green)]/10 px-2 py-0.5 rounded-lg">+{{ preview.new }} neu</span>
            <span v-if="preview.updated > 0" class="text-xs font-bold text-blue-400 bg-blue-400/10 px-2 py-0.5 rounded-lg">~ {{ preview.updated }} geändert</span>
            <span v-if="preview.deleted > 0" class="text-xs font-bold text-[var(--status-red)] bg-[var(--status-red)]/10 px-2 py-0.5 rounded-lg">– {{ preview.deleted }} gelöscht</span>
            <span v-if="preview.items.length === 0" class="text-xs text-[var(--text-muted)] opacity-50">Keine Änderungen</span>
          </div>
        </div>

        <!-- No changes -->
        <div v-if="preview.items.length === 0" class="px-5 py-8 text-center">
          <i class="bi bi-check-circle text-2xl text-[var(--status-green)] block mb-2"></i>
          <p class="text-sm text-[var(--text-muted)] opacity-60">Lokal ist bereits auf dem aktuellen Stand.</p>
        </div>

        <!-- Change list -->
        <div v-else class="max-h-72 overflow-y-auto">
          <div v-for="item in preview.items" :key="item.remoteId"
            class="flex items-start gap-3 px-5 py-3 border-b border-[var(--border-ui)] last:border-0 hover:bg-[var(--bg-elevated)] transition-colors">

            <!-- Action badge -->
            <span class="flex-shrink-0 mt-0.5 text-xs font-black uppercase tracking-wider w-16 text-center py-0.5 rounded-md"
              :class="{
                'bg-[var(--status-green)]/10 text-[var(--status-green)]': item.action === 'new',
                'bg-blue-400/10 text-blue-400': item.action === 'updated',
                'bg-[var(--status-red)]/10 text-[var(--status-red)]': item.action === 'deleted',
              }">
              {{ item.action === 'new' ? 'Neu' : item.action === 'updated' ? 'Update' : 'Gelöscht' }}
            </span>

            <!-- Info -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-[var(--text-main)] truncate">{{ item.title }}</p>
              <p v-if="item.action === 'updated' && item.changes.length > 0"
                class="text-xs text-[var(--text-muted)] opacity-60 mt-0.5">
                {{ item.changes.join(' · ') }}
              </p>
              <p v-else class="text-xs text-[var(--text-muted)] opacity-40 mt-0.5">{{ item.year ?? '–' }}</p>
            </div>
          </div>

          <!-- Overflow hint -->
          <div v-if="preview.overflow > 0" class="px-5 py-3 text-center text-xs text-[var(--text-muted)] opacity-40">
            … und {{ preview.overflow }} weitere
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 px-5 py-4 border-t border-[var(--border-ui)] bg-[var(--bg-app)]">
          <button @click="applyPull"
            :disabled="preview.items.length === 0"
            class="flex-1 bg-[var(--status-red)] hover:opacity-90 disabled:opacity-40 text-white text-sm font-black py-3 rounded-xl transition-all flex items-center justify-center gap-2">
            <i class="bi bi-cloud-download"></i>
            {{ preview.items.length === 0 ? 'Nichts zu tun' : 'Änderungen übernehmen' }}
          </button>
          <button @click="preview = null" class="px-5 py-3 text-sm font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors">
            Abbrechen
          </button>
        </div>
      </div>

      <!-- Result summary -->
      <div v-if="result && phase === 'idle' && !preview" class="rounded-2xl p-5 mb-6 border"
        :class="result.errors > 0 ? 'bg-[var(--status-yellow-bg)] border-[var(--status-yellow)]/20' : 'bg-[var(--bg-card)] border-[var(--status-green)]/30'">
        <div class="flex items-center gap-3 mb-3">
          <i class="bi text-lg" :class="result.errors > 0 ? 'bi-exclamation-triangle text-[var(--status-yellow)]' : 'bi-check-circle text-[var(--status-green)]'"></i>
          <p class="font-black text-sm text-[var(--text-main)]">
            {{ result.errors > 0 ? 'Sync abgeschlossen mit Fehlern' : 'Sync erfolgreich' }}
          </p>
          <span class="ml-auto text-xs text-[var(--text-muted)] opacity-50">{{ result.duration }}s</span>
        </div>
        <div class="grid grid-cols-5 gap-3">
          <div class="text-center">
            <p class="text-xl font-black text-[var(--text-main)]">{{ result.pulled }}</p>
            <p class="text-xs text-[var(--text-muted)] opacity-50">Gezogen</p>
          </div>
          <div class="text-center">
            <p class="text-xl font-black text-[var(--text-main)]">{{ result.pushed }}</p>
            <p class="text-xs text-[var(--text-muted)] opacity-50">Hochgeladen</p>
          </div>
          <div class="text-center">
            <p class="text-xl font-black text-[var(--text-main)]">{{ result.deleted }}</p>
            <p class="text-xs text-[var(--text-muted)] opacity-50">Gelöscht</p>
          </div>
          <div class="text-center">
            <p class="text-xl font-black text-[var(--text-main)]">{{ result.media }}</p>
            <p class="text-xs text-[var(--text-muted)] opacity-50">Bilder</p>
          </div>
          <div class="text-center">
            <p class="text-xl font-black" :class="result.errors > 0 ? 'text-[var(--status-yellow)]' : 'text-[var(--text-muted)]'">{{ result.errors }}</p>
            <p class="text-xs text-[var(--text-muted)] opacity-50">Fehler</p>
          </div>
        </div>
      </div>

      <!-- Action buttons -->
      <div class="grid grid-cols-2 gap-4 mb-4">
        <!-- Pull button: shows preview first -->
        <button @click="loadPreview" :disabled="phase !== 'idle' || previewLoading"
          class="bg-[var(--bg-card)] hover:bg-[var(--bg-elevated)] border border-[var(--border-ui)] disabled:opacity-40 text-[var(--text-main)] text-sm font-bold px-4 py-4 rounded-2xl transition-all flex flex-col items-center gap-2">
          <i class="bi text-xl text-[var(--status-red)]" :class="previewLoading ? 'bi-hourglass-split animate-spin' : 'bi-cloud-download'"></i>
          <span>Shelf → Desktop</span>
          <span class="text-xs text-[var(--text-muted)] opacity-50">{{ previewLoading ? 'Lade Vorschau…' : 'Vorschau & importieren' }}</span>
        </button>

        <!-- Push button -->
        <button @click="runPush" :disabled="phase !== 'idle'"
          class="bg-[var(--bg-card)] hover:bg-[var(--bg-elevated)] border border-[var(--border-ui)] disabled:opacity-40 text-[var(--text-main)] text-sm font-bold px-4 py-4 rounded-2xl transition-all flex flex-col items-center gap-2"
          :class="dirtyCount > 0 ? 'border-[var(--status-yellow)]/30' : ''">
          <i class="bi bi-cloud-upload text-xl text-[var(--status-red)]"></i>
          <span>Desktop → Shelf</span>
          <span class="text-xs text-[var(--text-muted)] opacity-50">{{ dirtyCount > 0 ? `${dirtyCount} Änderungen` : 'Keine Änderungen' }}</span>
        </button>
      </div>

      <button @click="runFullSync" :disabled="phase !== 'idle'"
        class="w-full bg-[var(--status-red)] hover:opacity-90 disabled:opacity-50 text-white font-black py-4 rounded-2xl transition-all text-sm flex items-center justify-center gap-3 shadow-lg shadow-red-600/10">
        <i class="bi bi-arrow-repeat text-lg" :class="{ 'animate-spin': phase !== 'idle' }"></i>
        {{ phase !== 'idle' ? phaseLabel : 'Vollständig synchronisieren' }}
      </button>

      <!-- Error log (collapsible) -->
      <div v-if="errors.length > 0" class="mt-4">
        <button @click="showErrors = !showErrors" class="text-xs text-[var(--status-yellow)] font-bold flex items-center gap-1">
          <i :class="showErrors ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
          {{ errors.length }} Fehler anzeigen
        </button>
        <div v-if="showErrors" class="mt-2 bg-[var(--bg-card)] border border-[var(--status-yellow)]/20 rounded-xl p-4 space-y-1 max-h-40 overflow-y-auto">
          <p v-for="(e, i) in errors" :key="i" class="text-xs font-mono text-[var(--status-yellow)] opacity-80">{{ e }}</p>
        </div>
      </div>

    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { useApi } from '@/composables/useApi'

const settings = useSettingsStore()
const { apiGet, apiPost, apiPut, apiDelete, resolveMediaUrl } = useApi()

// ── State ────────────────────────────────────────────────────────────────────

type Phase = 'idle' | 'connecting' | 'metadata' | 'media' | 'push' | 'lists'

type PreviewItem = {
  remoteId: number
  title: string
  year: number | null
  action: 'new' | 'updated' | 'deleted'
  changes: string[]
}

type PreviewData = {
  items: PreviewItem[]
  new: number
  updated: number
  deleted: number
  overflow: number
  rawMovies: any[]
}

const phase         = ref<Phase>('idle')
const phaseLabel    = ref('')
const phaseDetail   = ref('')
const progressPct   = ref(0)

const localCount    = ref(0)
const dirtyCount    = ref(0)
const lastSyncLabel = ref('–')

const showErrors    = ref(false)
const errors        = ref<string[]>([])
const previewLoading = ref(false)
const preview       = ref<PreviewData | null>(null)

const result = ref<{ pulled: number; deleted: number; pushed: number; media: number; errors: number; duration: string } | null>(null)

const PREVIEW_LIMIT = 100

// ── Field labels für Diff ─────────────────────────────────────────────────────

const FIELD_LABELS: Record<string, string> = {
  title: 'Titel', year: 'Jahr', genre: 'Genre', director: 'Regisseur',
  runtime: 'Laufzeit', rating: 'Bewertung', rating_age: 'FSK',
  overview: 'Beschreibung', collection_type: 'Typ', tag: 'Format',
  trailer_url: 'Trailer',
}

// ── Init ─────────────────────────────────────────────────────────────────────

onMounted(loadStats)

async function loadStats() {
  const all = await window.electron.db.movies.list({ page: 1, perPage: 1 }) as any
  localCount.value = all.total

  const dirty = await window.electron.db.movies.sync.dirty() as any[]
  dirtyCount.value = dirty.length

  const ts = await window.electron.settings.get('last_sync_at') as string | null
  if (ts) {
    const d = new Date(ts)
    lastSyncLabel.value = d.toLocaleDateString('de-AT', { day: '2-digit', month: '2-digit', year: '2-digit' })
      + ' ' + d.toLocaleTimeString('de-AT', { hour: '2-digit', minute: '2-digit' })
  }
}

// ── Preview ───────────────────────────────────────────────────────────────────

async function loadPreview() {
  previewLoading.value = true
  preview.value = null
  result.value = null

  try {
    const since = await window.electron.settings.get('last_sync_at') as string | null
    const data = await apiGet('/admin/export', since ? { since } : {})
    const movies = data.movies as any[]

    const items: PreviewItem[] = []
    let newCount = 0, updatedCount = 0, deletedCount = 0

    for (const movie of movies) {
      if (movie.is_deleted) {
        deletedCount++
        if (items.length < PREVIEW_LIMIT) {
          items.push({ remoteId: movie.id, title: movie.title, year: movie.year, action: 'deleted', changes: [] })
        }
        continue
      }

      const local = await window.electron.db.movies.getByRemoteId(movie.id)

      if (!local) {
        newCount++
        if (items.length < PREVIEW_LIMIT) {
          items.push({ remoteId: movie.id, title: movie.title, year: movie.year, action: 'new', changes: [] })
        }
      } else {
        // Felder vergleichen
        const changed: string[] = []
        for (const [field, label] of Object.entries(FIELD_LABELS)) {
          const remoteVal = movie[field] ?? null
          const localVal  = local[field]  ?? null
          if (String(remoteVal) !== String(localVal)) changed.push(label)
        }
        if (changed.length > 0) {
          updatedCount++
          if (items.length < PREVIEW_LIMIT) {
            items.push({ remoteId: movie.id, title: movie.title, year: movie.year, action: 'updated', changes: changed })
          }
        }
      }
    }

    const total = newCount + updatedCount + deletedCount
    preview.value = {
      items,
      new: newCount,
      updated: updatedCount,
      deleted: deletedCount,
      overflow: Math.max(0, total - items.length),
      rawMovies: movies,
    }
  } catch (e: any) {
    errors.value = [e.message]
  } finally {
    previewLoading.value = false
  }
}

async function applyPull() {
  if (!preview.value) return
  preview.value = null
  await runPull()
}

// ── Sync helpers ─────────────────────────────────────────────────────────────

function setPhase(p: Phase, label: string, detail = '', pct = 0) {
  phase.value       = p
  phaseLabel.value  = label
  phaseDetail.value = detail
  progressPct.value = pct
}

// ── Pull ─────────────────────────────────────────────────────────────────────

async function pull(full = false): Promise<{ pulled: number; deleted: number; media: number; pullErrors: number }> {
  setPhase('connecting', 'Verbinde mit Shelf…', '', 0)

  const since = full ? null : await window.electron.settings.get('last_sync_at') as string | null
  const data = await apiGet('/admin/export', since ? { since } : {})
  const movies = data.movies as any[]
  let pulled = 0, deleted = 0, pullErrors = 0

  // Metadata
  setPhase('metadata', data.is_delta ? 'Delta laden' : 'Metadaten laden', '', 0)
  for (let i = 0; i < movies.length; i++) {
    const movie = movies[i]
    phaseDetail.value = movie.title
    progressPct.value = Math.round((i / movies.length) * 50)

    try {
      if (movie.is_deleted) {
        const r = await window.electron.db.movies.deleteByRemoteId(movie.id)
        if (r.success) deleted++
        continue
      }

      const local = await window.electron.db.movies.create({
        title:           movie.title,
        year:            movie.year,
        genre:           movie.genre,
        director:        movie.director,
        runtime:         movie.runtime,
        rating:          movie.rating,
        rating_age:      movie.rating_age,
        overview:        movie.overview,
        collection_type: movie.collection_type ?? 'Film',
        tag:             movie.tag,
        tmdb_id:         movie.tmdb_id,
        remote_id:        movie.id,
        cover_path:       movie.cover_url,
        backdrop_path:    movie.backdrop_url,
        actors_names:     movie.actors_names,
        trailer_url:      movie.trailer_url,
        created_at:       movie.created_at,
        updated_at:       movie.updated_at,
        is_boxset:        movie.is_boxset ? 1 : 0,
        boxset_parent_id: movie.boxset_parent_id ?? null,
        view_count:       movie.view_count ?? 0,
        is_watched:       movie.is_watched ? 1 : 0,
        in_collection:    movie.in_collection != null ? (movie.in_collection ? 1 : 0) : 1,
      }) as { id: number } | null

      if (local) {
        await window.electron.db.movies.sync.markSynced({
          id: local.id, remote_id: movie.id, synced_at: new Date().toISOString()
        })
        if (movie.actors && Array.isArray(movie.actors)) {
          for (const a of movie.actors) {
            const aid = await window.electron.db.movies.actors.upsert({
              remote_id: a.id, name: a.name, bio: a.bio, birthday: a.birthday,
              place_of_birth: a.place_of_birth, tmdb_id: a.tmdb_id, image_path: a.image_url
            })
            await window.electron.db.movies.actors.link({
              film_id: local.id, actor_id: aid, role: a.role, is_main_role: a.is_main_role
            })
          }
        }
        pulled++
      }
    } catch (e: any) {
      errors.value.push(`Pull ${movie.title}: ${e.message}`)
      pullErrors++
    }
  }

  // Media
  let media = 0
  setPhase('media', 'Bilder herunterladen', '', 50)
  const processedActors = new Set<number>()
  let mediaTotal = 0
  for (const movie of movies) {
    if (movie.cover_url)   mediaTotal++
    if (movie.backdrop_url) mediaTotal++
    if (movie.actors) for (const a of movie.actors) if (!processedActors.has(a.id) && a.image_url) { mediaTotal++; processedActors.add(a.id) }
  }
  processedActors.clear()

  let mediaDone = 0
  for (const movie of movies) {
    phaseDetail.value = movie.title
    const coverUrl = resolveMediaUrl(movie.cover_url)
    if (coverUrl && !await window.electron.db.movies.exists(movie.id, 'cover')) {
      const r = await window.electron.db.movies.download(coverUrl, movie.id, 'cover')
      if (r.success) media++
    }
    mediaDone++; progressPct.value = 50 + Math.round((mediaDone / Math.max(mediaTotal, 1)) * 50)

    const backdropUrl = resolveMediaUrl(movie.backdrop_url)
    if (backdropUrl && !await window.electron.db.movies.exists(movie.id, 'backdrop')) {
      const r = await window.electron.db.movies.download(backdropUrl, movie.id, 'backdrop')
      if (r.success) media++
    }
    mediaDone++; progressPct.value = 50 + Math.round((mediaDone / Math.max(mediaTotal, 1)) * 50)

    if (movie.actors && Array.isArray(movie.actors)) {
      for (const a of movie.actors) {
        if (!processedActors.has(a.id) && a.image_url) {
          if (!await window.electron.db.movies.exists(a.id, 'actor')) {
            const url = resolveMediaUrl(a.image_url)
            if (url) { const r = await window.electron.db.movies.download(url, a.id, 'actor'); if (r.success) media++ }
          }
          mediaDone++; progressPct.value = 50 + Math.round((mediaDone / Math.max(mediaTotal, 1)) * 50)
          processedActors.add(a.id)
        }
      }
    }
  }

  progressPct.value = 100
  return { pulled, deleted, media, pullErrors }
}

// ── Push ─────────────────────────────────────────────────────────────────────

async function push(): Promise<{ pushed: number; pushErrors: number }> {
  setPhase('push', 'Änderungen hochladen', '', 0)

  const dirty = await window.electron.db.movies.sync.dirty() as any[]
  let pushed = 0, pushErrors = 0

  for (let i = 0; i < dirty.length; i++) {
    const movie = dirty[i]
    phaseDetail.value = movie.title
    progressPct.value = Math.round((i / Math.max(dirty.length, 1)) * 100)

    try {
      if (movie.is_deleted) {
        if (movie.remote_id) await apiDelete(`/admin/movies/${movie.remote_id}`)
        await window.electron.db.movies.sync.hardDelete(movie.id)
      } else if (!movie.remote_id) {
        let res
        if (movie.tmdb_id) {
          res = await apiPost('/tmdb/import', { tmdb_id: movie.tmdb_id, type: movie.collection_type === 'Serie' ? 'tv' : 'movie', in_collection: movie.in_collection ?? 1 })
        } else {
          res = await apiPost('/admin/movies', {
            title: movie.title, year: movie.year, genre: movie.genre, director: movie.director,
            runtime: movie.runtime, rating: movie.rating, rating_age: movie.rating_age,
            overview: movie.overview, collection_type: movie.collection_type,
            tag: movie.tag, tmdb_id: movie.tmdb_id, trailer_url: movie.trailer_url,
            in_collection: movie.in_collection ?? 1,
          })
        }
        await window.electron.db.movies.sync.markSynced({ id: movie.id, remote_id: res.data.id, synced_at: new Date().toISOString() })
        pushed++
      } else {
        await apiPut(`/admin/movies/${movie.remote_id}`, {
          title: movie.title, year: movie.year, genre: movie.genre, director: movie.director,
          runtime: movie.runtime, rating: movie.rating, rating_age: movie.rating_age,
          overview: movie.overview, collection_type: movie.collection_type,
          tag: movie.tag, tmdb_id: movie.tmdb_id, trailer_url: movie.trailer_url,
          in_collection: movie.in_collection ?? 1,
        })
        await window.electron.db.movies.sync.markSynced({ id: movie.id, remote_id: movie.remote_id, synced_at: new Date().toISOString() })
        pushed++
      }
    } catch (e: any) {
      errors.value.push(`Push ${movie.title}: ${e.message}`)
      pushErrors++
    }
  }

  progressPct.value = 100
  return { pushed, pushErrors }
}

// ── List Sync ─────────────────────────────────────────────────────────────────

async function syncLists(): Promise<{ listsSynced: number; listErrors: number }> {
  setPhase('lists', 'Listen synchronisieren', '', 0)
  let listsSynced = 0, listErrors = 0

  try {
    const localLists = await window.electron.db.lists.syncState()
    const serverData = await apiGet('/lists')
    const serverLists: Array<{ id: number; name: string; movie_remote_ids: number[] }> = serverData.lists ?? []

    const preKnownRemoteIds = new Set(localLists.map(l => l.remote_id).filter(Boolean))
    const pushedRemoteIds = new Set<number>()

    // Push: local → server
    for (const list of localLists) {
      phaseDetail.value = list.name
      try {
        if (!list.remote_id) {
          const res = await apiPost('/lists', { name: list.name, movie_remote_ids: list.movie_remote_ids })
          await window.electron.db.lists.setRemoteId(list.id, res.id)
          pushedRemoteIds.add(res.id)
        } else {
          await apiPut(`/lists/${list.remote_id}`, { name: list.name, movie_remote_ids: list.movie_remote_ids })
          await window.electron.db.lists.markSynced(list.id)
          pushedRemoteIds.add(list.remote_id)
        }
        listsSynced++
      } catch (e: any) {
        errors.value.push(`List push "${list.name}": ${e.message}`)
        listErrors++
      }
    }

    // Pull: server-only lists → create locally
    for (const serverList of serverLists) {
      if (!preKnownRemoteIds.has(serverList.id)) {
        try {
          const created = await window.electron.db.lists.create(serverList.name)
          await window.electron.db.lists.setRemoteId(created.id, serverList.id)
          for (const remoteId of serverList.movie_remote_ids) {
            const movie = await window.electron.db.movies.getByRemoteId(remoteId) as any
            if (movie?.id) await window.electron.db.lists.addMovie(created.id, movie.id)
          }
          listsSynced++
        } catch (e: any) {
          errors.value.push(`List pull "${serverList.name}": ${e.message}`)
          listErrors++
        }
      }
    }

    // Delete server lists that were deleted locally
    for (const serverList of serverLists) {
      if (preKnownRemoteIds.has(serverList.id) && !pushedRemoteIds.has(serverList.id)) {
        try {
          await apiDelete(`/lists/${serverList.id}`)
        } catch (e: any) {
          listErrors++
        }
      }
    }
  } catch (e: any) {
    errors.value.push(`Listen-Sync: ${e.message}`)
    listErrors++
  }

  return { listsSynced, listErrors }
}

// ── Actions ───────────────────────────────────────────────────────────────────

async function runPull() {
  errors.value = []
  result.value = null
  const start = Date.now()
  try {
    const { pulled, deleted, media, pullErrors } = await pull()
    const { listsSynced, listErrors } = await syncLists()
    result.value = { pulled, deleted, pushed: 0, media, errors: pullErrors + listErrors, duration: ((Date.now() - start) / 1000).toFixed(1) }
    await saveSyncTime()
  } catch (e: any) {
    errors.value.push(e.message)
  } finally {
    phase.value = 'idle'
    await loadStats()
  }
}

async function runPush() {
  errors.value = []
  result.value = null
  const start = Date.now()
  try {
    const { pushed, pushErrors } = await push()
    const { listsSynced, listErrors } = await syncLists()
    result.value = { pulled: 0, deleted: 0, pushed, media: 0, errors: pushErrors + listErrors, duration: ((Date.now() - start) / 1000).toFixed(1) }
    await saveSyncTime()
  } catch (e: any) {
    errors.value.push(e.message)
  } finally {
    phase.value = 'idle'
    await loadStats()
  }
}

async function runFullSync() {
  errors.value = []
  result.value = null
  preview.value = null
  const start = Date.now()
  let pulled = 0, deleted = 0, pushed = 0, media = 0, totalErrors = 0

  try {
    const pullResult = await pull(true)
    pulled  = pullResult.pulled
    deleted = pullResult.deleted
    media   = pullResult.media
    totalErrors += pullResult.pullErrors

    const pushResult = await push()
    pushed = pushResult.pushed
    totalErrors += pushResult.pushErrors

    const { listErrors } = await syncLists()
    totalErrors += listErrors

    result.value = { pulled, deleted, pushed, media, errors: totalErrors, duration: ((Date.now() - start) / 1000).toFixed(1) }
    await saveSyncTime()
  } catch (e: any) {
    errors.value.push(e.message)
  } finally {
    phase.value = 'idle'
    await loadStats()
  }
}

async function saveSyncTime() {
  await window.electron.settings.set('last_sync_at', new Date().toISOString())
}
</script>
