<template>
  <div v-if="movie" class="relative min-h-screen bg-[var(--bg-app)]">
    <!-- Backdrop Header -->
    <div class="relative min-h-[400px] h-[50vh] w-full overflow-hidden group">
      <img
        v-if="resolveMediaUrl((movie.backdrop_url || movie.backdrop_path) as string, Number(movie.remote_id), 'backdrop')"
        :src="resolveMediaUrl((movie.backdrop_url || movie.backdrop_path) as string, Number(movie.remote_id), 'backdrop')!"
        class="w-full h-full object-cover object-top opacity-40 shadow-inner group-hover:scale-105 transition-transform duration-1000"
      />
      <div v-else class="w-full h-full bg-gradient-to-br from-[var(--bg-sidebar)] to-[var(--bg-app)] opacity-50"></div>
      
      <!-- Gradient Overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-[var(--bg-app)] via-[var(--bg-app)]/60 to-transparent"></div>

      <!-- Play Button -->
      <div v-if="movie.trailer_url" class="absolute inset-0 flex items-center justify-center z-20">
        <button
          @click="openTrailer"
          class="w-20 h-20 bg-red-600/80 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-all hover:scale-110 shadow-2xl shadow-red-600/40 border-4 border-white/20"
        >
          <i class="bi bi-play-fill text-4xl ml-1"></i>
        </button>
      </div>

      <!-- Manual Search Button (If no trailer) -->
      <div v-else class="absolute inset-0 flex items-center justify-center z-20">
        <button 
          @click="searchYouTube"
          class="flex items-center gap-3 px-6 py-3 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white rounded-2xl border border-white/20 transition-all hover:scale-105 active:scale-95 group font-bold shadow-2xl"
        >
          <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center group-hover:bg-red-500 transition-colors">
            <i class="bi bi-youtube"></i>
          </div>
          <span>Trailer suchen</span>
        </button>
      </div>
    </div>


    <!-- Content Over Backdrop -->
    <div class="px-12 -mt-32 relative z-10 pb-20">
      <div class="flex gap-10 items-end">
        <!-- Cover -->
        <div class="w-64 flex-shrink-0 shadow-[var(--shadow-main)] rounded-2xl overflow-hidden border border-[var(--border-ui)] aspect-[2/3] bg-[var(--bg-card)]">
          <img
            v-if="resolveMediaUrl((movie.cover_url || movie.cover_path) as string, Number(movie.remote_id))"
            :src="resolveMediaUrl((movie.cover_url || movie.cover_path) as string, Number(movie.remote_id))!"
            :alt="movie.title as string"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-[var(--text-muted)] opacity-20 text-4xl">🎬</div>
        </div>

        <!-- Info (Sticky Container) -->
        <div 
          ref="titleRef"
          class="flex-1 pb-4 sticky top-0 z-30 transition-all duration-300 -mx-12 px-12 py-4"
          :class="{ 'pointer-events-none': isSticky }"
        >
          <div class="flex gap-4 mb-2 transition-all duration-500" :class="{ 'opacity-0 scale-95 translate-y-[-10px]': isSticky }">
            <span v-if="movie.collection_type" 
              class="px-2 py-0.5 text-[10px] font-black uppercase tracking-widest rounded border"
              style="background-color: var(--status-green-bg); color: var(--status-green); border-color: transparent;"
            >
              {{ movie.collection_type }}
            </span>
            <span v-if="movie.tag" 
              class="px-2 py-0.5 text-[10px] font-black uppercase tracking-widest rounded border"
              style="background-color: var(--status-yellow-bg); color: var(--status-yellow); border-color: transparent;"
            >
              {{ movie.tag }}
            </span>
          </div>
          
          <h1 
            class="font-black text-[var(--text-main)] uppercase tracking-tighter transition-all duration-500"
            :class="isSticky ? 'text-2xl opacity-0 translate-y-[-10px]' : 'text-5xl mb-4 opacity-100'"
          >
            {{ movie.title }}
          </h1>
          
          <div class="flex items-center gap-6 text-sm font-bold text-[var(--text-muted)] transition-all duration-500" :class="{ 'opacity-0 scale-95 translate-y-[-10px]': isSticky }">
            <div class="flex items-center gap-2">
              <span class="text-[var(--text-muted)] opacity-50 text-xs uppercase tracking-widest">Jahr</span>
              <span class="text-[var(--text-main)] opacity-70">{{ movie.year }}</span>
            </div>
            <div v-if="movie.genre" class="flex items-center gap-2">
              <span class="text-[var(--text-muted)] opacity-50 text-xs uppercase tracking-widest">Genre</span>
              <span class="text-[var(--text-main)] opacity-70">{{ movie.genre }}</span>
            </div>
            <div v-if="movie.runtime" class="flex items-center gap-2">
              <span class="text-[var(--text-muted)] opacity-50 text-xs uppercase tracking-widest">Dauer</span>
              <span class="text-[var(--text-main)] opacity-70">{{ movie.runtime }} min</span>
            </div>
            <div v-if="movie.rating" class="flex items-center gap-2 bg-yellow-500/10 px-3 py-1 rounded-full border border-yellow-500/20">
              <span class="text-yellow-600">★ {{ movie.rating }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Extended Info -->
      <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-12">
        <div class="lg:col-span-2 space-y-12">
          <!-- Description -->
          <div>
            <h3 class="text-[var(--text-muted)] opacity-40 text-xs font-black uppercase tracking-[0.2em] mb-4">Handlung</h3>
            <div v-if="movie.overview" class="text-lg text-[var(--text-main)] opacity-90 leading-relaxed font-medium">
               <template v-for="(segment, i) in parsedOverview" :key="i">
                <router-link 
                  v-if="segment.type === 'actor'" 
                  :to="(segment as any).id ? { name: 'actors.show', params: { id: (segment as any).id } } : { name: 'movies', query: { q: segment.value } }"
                  class="text-red-500 hover:text-red-600 underline decoration-red-500/30 underline-offset-4 hover:decoration-red-600 transition-all font-bold"
                >
                  {{ segment.value }}
                </router-link>
                <span v-else>{{ segment.value }}</span>
              </template>
            </div>
            <p v-else class="text-[var(--text-muted)] opacity-40 italic">Keine Beschreibung verfügbar.</p>
          </div>
          
          <!-- Cast Section -->
          <div v-if="linkedActors.length > 0">
            <h3 class="text-[var(--text-muted)] opacity-40 text-xs font-black uppercase tracking-[0.2em] mb-6">Besetzung</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <router-link 
                v-for="actor in linkedActors" 
                :key="actor.id"
                :to="{ name: 'actors.show', params: { id: actor.id } }"
                class="flex items-center gap-4 bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-3 transition-all hover:scale-[1.02] active:scale-95 group shadow-sm"
              >
                <div class="w-12 h-12 rounded-full overflow-hidden border border-[var(--border-ui)] flex-shrink-0 bg-[var(--bg-sidebar)]">
                  <img
                    v-if="resolveMediaUrl(actor.image_path, actor.remote_id, 'actor')"
                    :src="resolveMediaUrl(actor.image_path, actor.remote_id, 'actor')!"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center text-xs text-[var(--text-muted)] opacity-40">👤</div>
                </div>
                <div>
                  <p class="text-[var(--text-main)] font-bold text-sm leading-tight">{{ actor.name }}</p>
                  <p v-if="actor.role" class="text-[var(--text-muted)] text-xs mt-0.5">{{ actor.role }}</p>
                </div>
              </router-link>
            </div>
          </div>

          <div v-if="movie.director">
            <h3 class="text-[var(--text-muted)] opacity-40 text-xs font-black uppercase tracking-[0.2em] mb-4">Regie</h3>
            <p class="text-[var(--text-main)] text-xl font-bold">{{ movie.director }}</p>
          </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-4">
          <router-link :to="`/movies/${movie.id}/edit`"
            class="flex items-center justify-center gap-2 w-full bg-red-600 hover:bg-red-500 text-white font-black py-4 rounded-2xl transition-all hover:scale-[1.02] active:scale-95 shadow-lg shadow-red-600/20">
            Film bearbeiten
          </router-link>

          <!-- Listen -->
          <div class="bg-[var(--bg-card)] border border-[var(--border-ui)] rounded-2xl p-4 space-y-3">
            <p class="text-[var(--text-muted)] opacity-50 text-[10px] font-black uppercase tracking-[0.15em]">Listen</p>

            <div v-if="listStore.lists.length === 0" class="text-xs text-[var(--text-muted)] opacity-40 italic">
              Noch keine Listen vorhanden.
            </div>

            <div
              v-for="list in listStore.lists"
              :key="list.id"
              @click="toggleList(list.id)"
              class="flex items-center gap-3 cursor-pointer group"
            >
              <div
                class="w-4 h-4 rounded border-2 flex items-center justify-center transition-colors flex-shrink-0"
                :class="movieListIds.has(list.id)
                  ? 'bg-red-600 border-red-600'
                  : 'border-[var(--border-ui)] group-hover:border-red-500/50'"
              >
                <i v-if="movieListIds.has(list.id)" class="bi bi-check text-white text-[10px] leading-none"></i>
              </div>
              <span class="text-sm text-[var(--text-main)] truncate">{{ list.name }}</span>
            </div>

            <router-link to="/lists"
              class="flex items-center gap-2 text-xs text-[var(--text-muted)] hover:text-red-500 opacity-60 hover:opacity-100 transition-all pt-1">
              <i class="bi bi-plus-circle"></i> Listen verwalten
            </router-link>
          </div>

          <router-link to="/movies"
            class="flex items-center justify-center w-full bg-[var(--bg-card)] hover:bg-[var(--bg-elevated)] border border-[var(--border-ui)] text-[var(--text-muted)] font-bold py-4 rounded-2xl transition-colors">
            Zurück zur Sammlung
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useApi } from '@/composables/useApi'
import { useUiStore } from '@/stores/ui'
import { useListStore } from '@/stores/lists'
const route = useRoute()
const { resolveMediaUrl } = useApi()
const ui = useUiStore()
const listStore = useListStore()
const movie = ref<any>(null)
const localMovieId = ref<number | null>(null)
const movieListIds = ref<Set<number>>(new Set())
const linkedActors = ref<any[]>([])
const isSticky = ref(false)

const titleRef = ref<HTMLElement | null>(null)

const handleScroll = (e: Event) => {
  const target = e.target as HTMLElement
  
  if (titleRef.value) {
    const rect = titleRef.value.getBoundingClientRect()
    isSticky.value = rect.top <= 64 // Etwas mehr Puffer
  } else {
    isSticky.value = target.scrollTop > 200
  }
  
  if (movie.value) {
    const newTitle = isSticky.value ? movie.value.title : ''
    // Nur aktualisieren, wenn sich der Titel wirklich ändert (Performance)
    if (ui.headerTitle !== newTitle) {
      ui.headerTitle = String(newTitle)
    }
  }
}

const embedUrl = computed(() => {
  if (!movie.value?.trailer_url) return null
  const url = movie.value.trailer_url
  let videoId = ''
  if (url.includes('v=')) {
    videoId = url.split('v=')[1].split('&')[0]
  } else if (url.includes('youtu.be/')) {
    videoId = url.split('youtu.be/')[1].split('?')[0]
  } else if (url.includes('embed/')) {
    videoId = url.split('embed/')[1].split('?')[0]
  } else {
    videoId = url
  }
  return `https://www.youtube.com/embed/${videoId}?autoplay=1`
})

function openTrailer() {
  const url = movie.value?.trailer_url
  if (!url) return

  let videoId = ''
  if (url.includes('v='))        videoId = url.split('v=')[1].split('&')[0]
  else if (url.includes('youtu.be/')) videoId = url.split('youtu.be/')[1].split('?')[0]
  else if (url.includes('embed/'))    videoId = url.split('embed/')[1].split('?')[0]
  else                                videoId = url

  const watchUrl = `https://www.youtube.com/watch?v=${videoId}`

  if (window.electron?.trailer) {
    window.electron.trailer.open(watchUrl)
  } else {
    window.open(watchUrl, '_blank')
  }
}

function searchYouTube() {
  const query = `${movie.value?.title} ${movie.value?.year || ''} trailer`.trim()
  window.open(`https://www.youtube.com/results?search_query=${encodeURIComponent(query)}`, '_blank')
}

const parsedOverview = computed(() => {
  const text = movie.value?.overview as string
  if (!text) return []

  const cleaned = text.replace(/<[^>]*>?/gm, '')
  const segments: { type: 'text' | 'actor', value: string, id?: number | null }[] = []
  const regex = /\{!Actor\}(.*?)\}|\(\[!Actor\](.*?)\)\)?/g
  let lastIndex = 0
  let match

  while ((match = regex.exec(cleaned)) !== null) {
    if (match.index > lastIndex) {
      segments.push({ type: 'text', value: cleaned.slice(lastIndex, match.index) })
    }
    const actorName = match[1] || match[2]
    const foundActor = linkedActors.value.find(a => a.name === actorName)
    segments.push({ 
      type: 'actor', 
      value: actorName, 
      id: foundActor ? foundActor.id : null 
    })
    lastIndex = regex.lastIndex
  }

  if (lastIndex < cleaned.length) {
    segments.push({ type: 'text', value: cleaned.slice(lastIndex) })
  }
  return segments
})

async function ensureLocalMovie(): Promise<number | null> {
  if (localMovieId.value !== null) return localMovieId.value
  if (!movie.value) return null
  // Film existiert noch nicht lokal — automatisch anlegen (ohne Cover-Download)
  const m = movie.value as any
  const created = await window.electron.db.movies.create({
    title:           m.title,
    year:            m.year,
    genre:           m.genre,
    director:        m.director,
    runtime:         m.runtime,
    rating:          m.rating,
    rating_age:      m.rating_age,
    overview:        m.overview,
    collection_type: m.collection_type ?? 'Film',
    tag:             m.tag,
    tmdb_id:         m.tmdb_id,
    remote_id:       m.id,
    cover_path:      m.cover_url ?? null,
    backdrop_path:   m.backdrop_url ?? null,
    actors_names:    m.actors_names ?? null,
    trailer_url:     m.trailer_url ?? null,
  }) as any
  if (!created?.id) return null
  localMovieId.value = created.id
  return created.id
}

async function toggleList(listId: number) {
  if (!movie.value) return
  const movieId = await ensureLocalMovie()
  if (movieId === null) return
  if (movieListIds.value.has(listId)) {
    await listStore.removeMovie(listId, movieId)
    movieListIds.value.delete(listId)
  } else {
    await listStore.addMovie(listId, movieId)
    movieListIds.value.add(listId)
  }
  // Force reactivity update
  movieListIds.value = new Set(movieListIds.value)
}

onMounted(async () => {
  const id = Number(route.params.id)

  const scroller = document.querySelector('main')
  if (scroller) {
    scroller.addEventListener('scroll', handleScroll)
  }

  movie.value = await window.electron.db.movies.get(id)
  linkedActors.value = await window.electron.db.movies.actors.getForMovie(id)
  localMovieId.value = id

  await listStore.fetchLists()
  if (localMovieId.value !== null) {
    const ids = await window.electron.db.lists.forMovie(localMovieId.value)
    movieListIds.value = new Set(ids)
  }
})

onUnmounted(() => {
  const scroller = document.querySelector('main')
  if (scroller) {
    scroller.removeEventListener('scroll', handleScroll)
  }
  ui.setHeaderTitle('')
})
</script>
