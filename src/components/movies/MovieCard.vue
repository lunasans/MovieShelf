<template>
  <div class="group cursor-pointer" @click="router.push(`/movies/${movie.id}`)">
    <div class="relative aspect-[2/3] rounded-2xl overflow-hidden bg-[#12121a] border border-white/10 group-hover:border-blue-500/50 group-hover:scale-105 transition-all duration-300 shadow-xl">
      <img
        v-if="movie.cover_url || movie.cover_path"
        :src="movie.cover_url ?? movie.cover_path"
        :alt="movie.title"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
      />
      <div v-else class="w-full h-full bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex flex-col items-center justify-center p-4 text-center">
        <span class="text-white/15 text-3xl mb-3">🎬</span>
        <span class="text-[11px] font-black text-white/50 uppercase tracking-tight leading-snug line-clamp-4">{{ movie.title }}</span>
        <span v-if="movie.year" class="text-[9px] text-white/25 font-bold mt-2">{{ movie.year }}</span>
      </div>

      <!-- Tag banderole -->
      <div v-if="movie.tag" class="absolute top-[22px] -right-[55px] z-20 w-[180px] py-[5px] bg-blue-800/80 rotate-45 text-center pointer-events-none">
        <span class="text-[9px] font-black text-white uppercase tracking-widest">{{ movie.tag }}</span>
      </div>

      <!-- Hover overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3 gap-2">
        <button
          @click.stop="router.push(`/movies/${movie.id}/edit`)"
          class="flex-1 bg-blue-600/80 hover:bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest py-1.5 rounded-lg transition-colors"
        >
          Bearbeiten
        </button>
        <button
          @click.stop="$emit('delete')"
          class="w-8 h-8 bg-red-600/80 hover:bg-red-600 text-white rounded-lg flex items-center justify-center transition-colors text-sm"
        >
          ✕
        </button>
      </div>
    </div>

    <div class="mt-2 px-1">
      <p class="text-[12px] font-black text-white/80 truncate uppercase tracking-tight">{{ movie.title }}</p>
      <p class="text-[10px] text-white/30 font-bold">{{ movie.year }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import type { Movie } from '@/stores/movies'

defineProps<{ movie: Movie }>()
defineEmits<{ delete: [] }>()

const router = useRouter()
</script>
