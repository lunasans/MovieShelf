<template>
  <div v-if="movie" class="p-8">
    <div class="flex gap-8">
      <!-- Cover -->
      <div class="w-48 flex-shrink-0">
        <div class="aspect-[2/3] rounded-2xl overflow-hidden bg-[#12121a] border border-white/10">
          <img v-if="movie.cover_url || movie.cover_path" :src="movie.cover_url ?? movie.cover_path" :alt="movie.title" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full flex items-center justify-center text-white/10 text-4xl">🎬</div>
        </div>
      </div>

      <!-- Info -->
      <div class="flex-1">
        <h1 class="text-3xl font-black text-white uppercase tracking-tight mb-1">{{ movie.title }}</h1>
        <div class="flex items-center gap-3 mb-4 text-sm text-white/40">
          <span>{{ movie.year }}</span>
          <span v-if="movie.genre">· {{ movie.genre }}</span>
          <span v-if="movie.runtime">· {{ movie.runtime }} min</span>
          <span v-if="movie.rating" class="text-yellow-400">★ {{ movie.rating }}</span>
        </div>

        <p v-if="movie.overview" class="text-sm text-white/60 leading-relaxed mb-6 max-w-xl">{{ movie.overview }}</p>

        <div class="flex gap-3">
          <router-link :to="`/movies/${movie.id}/edit`"
            class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-4 py-2 rounded-xl text-sm transition-colors">
            Bearbeiten
          </router-link>
          <router-link to="/movies"
            class="bg-white/5 hover:bg-white/10 border border-white/10 text-white font-bold px-4 py-2 rounded-xl text-sm transition-colors">
            Zurück
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApi } from '@/composables/useApi'

const route = useRoute()
const { isOnline, apiGet } = useApi()
const movie = ref<Record<string, unknown> | null>(null)

onMounted(async () => {
  const id = Number(route.params.id)
  if (isOnline.value) {
    movie.value = (await apiGet(`/movies/${id}`)).data
  } else {
    movie.value = await window.electron.db.movies.get(id)
  }
})
</script>
