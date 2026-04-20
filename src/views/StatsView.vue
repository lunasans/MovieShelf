<template>
  <div class="flex flex-col h-screen bg-[var(--bg-app)]">

    <!-- Titlebar with Tab Navigation -->
    <div
      class="flex items-center h-11 px-4 bg-[var(--bg-sidebar)] border-b border-[var(--border-ui)] select-none flex-shrink-0 gap-4"
      style="-webkit-app-region: drag"
    >
      <!-- Logo -->
      <div class="flex items-center gap-2 flex-shrink-0" style="-webkit-app-region: no-drag">
        <img src="/logo_small.png" alt="MovieShelf" class="h-4 w-auto opacity-50" />
      </div>

      <!-- Tabs -->
      <div class="flex items-center gap-1 flex-1" style="-webkit-app-region: no-drag">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-black uppercase tracking-widest transition-all"
          :class="activeTab === tab.id
            ? 'bg-red-600/10 text-red-500 border border-red-500/20'
            : 'text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--border-ui)] border border-transparent'"
        >
          <i :class="`bi bi-${tab.icon} text-xs`"></i>
          {{ tab.label }}
        </button>
      </div>

      <!-- Close -->
      <div style="-webkit-app-region: no-drag">
        <button @click="close" class="w-8 h-8 rounded-lg hover:bg-red-500/80 flex items-center justify-center text-[var(--text-muted)] hover:text-white transition-colors">
          <i class="bi bi-x-lg text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex-1 flex items-center justify-center">
      <div class="w-8 h-8 border-2 border-red-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Error -->
    <div v-else-if="loadError" class="flex-1 flex flex-col items-center justify-center gap-3 text-center px-8">
      <i class="bi bi-exclamation-triangle text-3xl text-[var(--status-yellow)]"></i>
      <p class="text-sm font-black text-[var(--text-main)]">Statistiken konnten nicht geladen werden</p>
      <p class="text-xs text-[var(--text-muted)] opacity-60 max-w-xs">{{ loadError }}</p>
      <button @click="reload" class="mt-2 px-5 py-2.5 bg-[var(--bg-elevated)] border border-[var(--border-ui)] rounded-xl text-sm font-bold text-[var(--text-main)] hover:bg-[var(--bg-card)] transition-colors">
        Neu laden
      </button>
    </div>

    <!-- Empty -->
    <div v-else-if="stats && stats.totalMovies === 0" class="flex-1 flex flex-col items-center justify-center gap-3 text-center px-8">
      <i class="bi bi-film text-3xl text-[var(--text-muted)] opacity-30"></i>
      <p class="text-sm font-black text-[var(--text-main)]">Noch keine Filme in der Sammlung</p>
      <p class="text-xs text-[var(--text-muted)] opacity-50 max-w-xs">Synchronisiere zuerst mit deiner MovieShelf oder füge Filme hinzu.</p>
    </div>

    <div v-else-if="stats" class="flex-1 overflow-y-auto">

      <!-- Tab: Übersicht -->
      <div v-if="activeTab === 'overview'" class="p-8 space-y-6">
        <div class="grid grid-cols-3 gap-4">
          <StatCard icon="film"       label="Filme gesamt"   :value="stats.totalMovies" />
          <StatCard icon="clock-fill" label="Gesamtspielzeit" :value="formattedRuntime" />
          <StatCard icon="calendar3"  label="Jahresspanne"   :value="yearRange" />
        </div>

        <!-- Laufzeit-Verteilung -->
        <div class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-6">
          <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] opacity-50 mb-6">Filme nach Laufzeit</h2>
          <div class="flex items-end gap-3 h-36 mb-3">
            <div
              v-for="b in stats.byRuntime"
              :key="b.label"
              class="flex-1 bg-red-600/40 hover:bg-red-600 rounded-t-sm transition-colors cursor-default"
              :style="{ height: `${(b.count / maxRuntimeCount) * 100}%`, minHeight: b.count > 0 ? '4px' : '0' }"
              :title="`${b.label}: ${b.count} Filme`"
            ></div>
          </div>
          <div class="flex gap-3">
            <div v-for="b in stats.byRuntime" :key="b.label" class="flex-1 text-center">
              <p class="text-[9px] font-black text-[var(--text-muted)] opacity-50 leading-tight">{{ b.label }}</p>
              <p class="text-[11px] font-black text-[var(--text-main)]">{{ b.count }}</p>
            </div>
          </div>
        </div>

        <!-- Sammlungstypen -->
        <div class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-6">
          <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] opacity-50 mb-5">Sammlungstypen</h2>
          <div class="space-y-3">
            <div v-for="t in stats.byType" :key="t.collection_type" class="flex items-center gap-3">
              <div class="w-32 text-[11px] text-[var(--text-muted)] font-bold truncate flex-shrink-0">{{ t.collection_type }}</div>
              <div class="flex-1 h-2 bg-[var(--bg-app)] rounded-full overflow-hidden">
                <div class="h-full bg-red-600/60 rounded-full" :style="{ width: `${(t.count / stats.totalMovies) * 100}%` }"></div>
              </div>
              <div class="w-8 text-right text-[11px] font-black text-[var(--text-main)] flex-shrink-0">{{ t.count }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab: Genres -->
      <div v-else-if="activeTab === 'genres'" class="p-8">
        <div class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-6">
          <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] opacity-50 mb-6">Filme pro Genre</h2>
          <div class="flex items-end gap-2 h-48 mb-3">
            <div
              v-for="g in stats.genres"
              :key="g.name"
              class="flex-1 bg-red-600/40 hover:bg-red-600 rounded-t-sm transition-colors cursor-default min-w-[20px]"
              :style="{ height: `${(g.count / maxGenreCount) * 100}%` }"
              :title="`${g.name}: ${g.count}`"
            ></div>
          </div>
          <div class="flex gap-2">
            <div
              v-for="g in stats.genres"
              :key="g.name"
              class="flex-1 min-w-[20px] text-center"
            >
              <p class="text-[9px] font-black text-[var(--text-muted)] opacity-50 truncate" :title="g.name">{{ g.name }}</p>
              <p class="text-[10px] font-black text-[var(--text-main)]">{{ g.count }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab: Schauspieler -->
      <div v-else-if="activeTab === 'actors'" class="p-8">
        <div v-if="stats.topActors.length > 0" class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-6">
          <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] opacity-50 mb-6">Häufigste Schauspieler</h2>
          <div class="space-y-3">
            <div
              v-for="(actor, i) in stats.topActors"
              :key="actor.name"
              class="flex items-center gap-4 p-3 rounded-xl hover:bg-[var(--bg-elevated)] transition-colors"
            >
              <span class="w-5 text-[11px] font-black text-[var(--text-muted)] opacity-40 text-right flex-shrink-0">{{ i + 1 }}</span>
              <div class="w-10 h-10 rounded-full bg-[var(--bg-app)] border border-[var(--border-ui)] overflow-hidden flex-shrink-0 flex items-center justify-center text-[var(--text-muted)] opacity-40">
                <img
                  v-if="actor.remote_id"
                  :src="`movie-resource://actor_${actor.remote_id}.jpg`"
                  class="w-full h-full object-cover opacity-100"
                />
                <i v-else class="bi bi-person-fill text-lg"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-black text-[var(--text-main)] truncate">{{ actor.name }}</p>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <div class="w-24 h-1.5 bg-[var(--bg-app)] rounded-full overflow-hidden">
                  <div
                    class="h-full bg-red-600 rounded-full"
                    :style="{ width: `${(actor.movie_count / stats!.topActors[0].movie_count) * 100}%` }"
                  ></div>
                </div>
                <span class="text-[11px] font-black text-[var(--text-muted)] w-16 text-right">
                  {{ actor.movie_count }} {{ actor.movie_count === 1 ? 'Film' : 'Filme' }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-20 text-[var(--text-muted)] opacity-40 text-sm">
          Noch keine verknüpften Schauspieler vorhanden.
        </div>
      </div>

      <!-- Tab: Jahre -->
      <div v-else-if="activeTab === 'years'" class="p-8 space-y-6">

        <!-- Dekaden -->
        <div class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-6">
          <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] opacity-50 mb-6">Filme pro Dekade</h2>
          <div class="flex items-end gap-3 h-36 mb-3">
            <div
              v-for="d in byDecade"
              :key="d.decade"
              class="flex-1 bg-red-600/40 hover:bg-red-600 rounded-t-sm transition-colors cursor-default"
              :style="{ height: `${(d.count / maxDecadeCount) * 100}%` }"
              :title="`${d.decade}er: ${d.count} Filme`"
            ></div>
          </div>
          <div class="flex gap-3">
            <div v-for="d in byDecade" :key="d.decade" class="flex-1 text-center">
              <p class="text-[9px] font-black text-[var(--text-muted)] opacity-50">{{ d.decade }}er</p>
              <p class="text-[11px] font-black text-[var(--text-main)]">{{ d.count }}</p>
            </div>
          </div>
        </div>

        <div class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-6">
          <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] opacity-50 mb-6">Filme pro Erscheinungsjahr</h2>
          <div class="flex items-end gap-1 h-40 mb-2">
            <div
              v-for="y in stats.byYear"
              :key="y.year"
              class="flex-1 bg-red-600/40 hover:bg-red-600 rounded-t-sm transition-colors cursor-default min-w-[4px]"
              :style="{ height: `${(y.count / maxYearCount) * 100}%` }"
              :title="`${y.year}: ${y.count} Film${y.count !== 1 ? 'e' : ''}`"
            ></div>
          </div>
          <div class="flex justify-between text-[10px] text-[var(--text-muted)] opacity-40 font-bold">
            <span>{{ stats.byYear[0]?.year }}</span>
            <span>{{ stats.byYear[stats.byYear.length - 1]?.year }}</span>
          </div>

          <!-- Year list -->
          <div class="mt-8 grid grid-cols-4 gap-2">
            <div
              v-for="y in [...stats.byYear].reverse()"
              :key="y.year"
              class="flex items-center justify-between bg-[var(--bg-app)] rounded-lg px-3 py-2"
            >
              <span class="text-[11px] font-black text-[var(--text-muted)]">{{ y.year }}</span>
              <span class="text-[11px] font-black text-[var(--text-main)]">{{ y.count }}</span>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import StatCard from '@/components/ui/StatCard.vue'

type Stats = Awaited<ReturnType<typeof window.electron.stats.get>>

const stats     = ref<Stats | null>(null)
const loading   = ref(true)
const loadError = ref('')
const activeTab = ref('overview')

const tabs = [
  { id: 'overview', label: 'Übersicht',    icon: 'speedometer2' },
  { id: 'genres',   label: 'Genres',       icon: 'tags-fill' },
  { id: 'actors',   label: 'Schauspieler', icon: 'people-fill' },
  { id: 'years',    label: 'Jahre',        icon: 'calendar3' },
]

const close = () => window.electron.window.close()

const formattedRuntime = computed(() => {
  if (!stats.value) return '—'
  const total = stats.value.totalRuntime
  const h = Math.floor(total / 60)
  const m = total % 60
  if (h === 0) return `${m} min`
  return `${h}h ${m}m`
})

const yearRange = computed(() => {
  if (!stats.value || stats.value.byYear.length === 0) return '—'
  const years = stats.value.byYear
  return `${years[0].year} – ${years[years.length - 1].year}`
})

const maxGenreCount = computed(() =>
  stats.value ? Math.max(...stats.value.genres.map(g => g.count), 1) : 1
)

const maxYearCount = computed(() =>
  stats.value ? Math.max(...stats.value.byYear.map(y => y.count), 1) : 1
)

const byDecade = computed(() => {
  if (!stats.value) return []
  const map: Record<number, number> = {}
  for (const y of stats.value.byYear) {
    const decade = Math.floor(y.year / 10) * 10
    map[decade] = (map[decade] ?? 0) + y.count
  }
  return Object.entries(map)
    .map(([decade, count]) => ({ decade: Number(decade), count }))
    .sort((a, b) => a.decade - b.decade)
})

const maxDecadeCount = computed(() =>
  byDecade.value.length ? Math.max(...byDecade.value.map(d => d.count), 1) : 1
)

const maxRuntimeCount = computed(() =>
  stats.value ? Math.max(...stats.value.byRuntime.map(b => b.count), 1) : 1
)

async function reload() {
  loading.value   = true
  loadError.value = ''
  stats.value     = null
  try {
    stats.value = await window.electron.stats.get()
  } catch (e: any) {
    loadError.value = e?.message ?? 'Unbekannter Fehler'
  } finally {
    loading.value = false
  }
}

onMounted(reload)
</script>
