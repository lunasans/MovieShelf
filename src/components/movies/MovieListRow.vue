<template>
  <div
    class="flex items-center gap-4 p-3 rounded-xl hover:bg-white/5 cursor-pointer transition-colors"
    @click="router.push(`/movies/${movie.id}`)"
  >
    <div class="w-10 aspect-[2/3] rounded-lg overflow-hidden bg-[#0a0a0f] border border-white/10 flex-shrink-0">
      <img v-if="movie.cover_url || movie.cover_path" :src="movie.cover_url ?? movie.cover_path" :alt="movie.title" class="w-full h-full object-cover" />
      <div v-else class="w-full h-full flex items-center justify-center text-white/10 text-xs">🎬</div>
    </div>
    <div class="flex-1 min-w-0">
      <p class="text-sm font-bold text-white truncate">{{ movie.title }}</p>
      <p class="text-xs text-white/30">{{ movie.year }} · {{ movie.genre }}</p>
    </div>
    <div class="text-xs text-white/20 font-bold">{{ movie.collection_type }}</div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import type { Movie } from '@/stores/movies'

defineProps<{ movie: Movie }>()
const router = useRouter()
</script>
