<template>
  <div class="p-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-black text-white uppercase tracking-tight">Filme</h1>
        <p class="text-sm text-white/40">{{ store.total }} Filme in der Sammlung</p>
      </div>
      <router-link
        to="/movies/new"
        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold px-4 py-2 rounded-xl transition-colors"
      >
        <span>+</span> Film hinzufügen
      </router-link>
    </div>

    <!-- Search -->
    <div class="relative mb-6">
      <input
        v-model="query"
        @input="onSearch"
        type="text"
        placeholder="Titel, Regisseur, Genre suchen..."
        class="w-full bg-[#12121a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-blue-500/50"
      />
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="flex items-center justify-center py-20">
      <div class="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-4 xl:grid-cols-6 gap-4">
      <MovieCard
        v-for="movie in store.movies"
        :key="movie.id"
        :movie="movie"
        @delete="store.deleteMovie(movie.id)"
      />
    </div>

    <!-- Empty -->
    <div v-if="!store.loading && store.movies.length === 0" class="text-center py-20 text-white/20 text-sm">
      Keine Filme gefunden.
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useMovieStore } from '@/stores/movies'
import MovieCard from '@/components/movies/MovieCard.vue'

const store = useMovieStore()
const query = ref('')

let searchTimeout: ReturnType<typeof setTimeout>

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    store.fetchMovies({ q: query.value || undefined })
  }, 300)
}

onMounted(() => store.fetchMovies())
</script>
