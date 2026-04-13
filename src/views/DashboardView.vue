<template>
  <div class="p-8">
    <h1 class="text-2xl font-black text-white mb-1 uppercase tracking-tight">Dashboard</h1>
    <p class="text-sm text-white/40 mb-8">Willkommen bei MovieShelf Desktop</p>

    <div class="grid grid-cols-3 gap-4 mb-8">
      <StatCard label="Filme gesamt" :value="stats.total" icon="🎬" />
      <StatCard label="Gesehen"      :value="stats.watched" icon="👁️" />
      <StatCard label="Bewertungen"  :value="stats.rated" icon="⭐" />
    </div>

    <div class="bg-[#12121a] rounded-2xl border border-white/5 p-6">
      <h2 class="text-sm font-black uppercase tracking-widest text-white/40 mb-4">Zuletzt hinzugefügt</h2>
      <div class="space-y-2">
        <div v-if="recent.length === 0" class="text-sm text-white/20 text-center py-8">
          Noch keine Filme vorhanden.
        </div>
        <MovieListRow v-for="movie in recent" :key="movie.id" :movie="movie" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import StatCard from '@/components/ui/StatCard.vue'
import MovieListRow from '@/components/movies/MovieListRow.vue'
import { useMovieStore } from '@/stores/movies'

const store = useMovieStore()
const recent = ref(store.movies.slice(0, 10))
const stats  = ref({ total: 0, watched: 0, rated: 0 })

onMounted(async () => {
  await store.fetchMovies()
  recent.value = store.movies.slice(0, 10)
  stats.value.total = store.total
})
</script>
