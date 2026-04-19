<template>
  <div class="group cursor-pointer" @click="router.push(`/movies/${movie.id}`)">
    <!-- Wrapper for card image area -->
    <div class="relative aspect-[2/3]">
      <div class="absolute inset-0 rounded-2xl overflow-hidden bg-[var(--bg-card)] border border-[var(--border-ui)] group-hover:border-blue-500/50 group-hover:scale-105 transition-all duration-300 shadow-[var(--shadow-main)]">
        <img
          v-if="resolveMediaUrl(movie.cover_url || movie.cover_path, movie.remote_id ?? undefined)"
          :src="resolveMediaUrl(movie.cover_url || movie.cover_path, movie.remote_id ?? undefined)!"
          :alt="movie.title"
          class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
        />
        <div v-else class="w-full h-full bg-gradient-to-br from-[var(--bg-app)] via-[var(--bg-sidebar)] to-[var(--bg-app)] flex flex-col items-center justify-center p-4 text-center">
          <i class="bi bi-film text-[var(--text-muted)] opacity-20 text-4xl mb-3"></i>
          <span class="text-[11px] font-black text-[var(--text-muted)] uppercase tracking-tight leading-snug line-clamp-4">{{ movie.title }}</span>
          <span v-if="movie.year" class="text-[9px] text-[var(--text-muted)] opacity-40 font-bold mt-2">{{ movie.year }}</span>
        </div>

        <!-- Cover gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>

        <!-- Tag Banderole -->
        <div v-if="movie.tag" :class="['absolute top-[18px] -right-[38px] z-30 w-[150px] py-[5px] rotate-45 text-center pointer-events-none', tagStyle(movie.tag).bg]">
          <span class="text-[8px] font-black text-white uppercase tracking-widest drop-shadow-sm">{{ tagStyle(movie.tag).label }}</span>
        </div>

        <!-- Rating Badge (top right, visible on hover) -->
        <div v-if="movie.rating" class="absolute top-3 left-3 z-20 -translate-x-4 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all duration-500">
          <div class="bg-blue-600 px-2 py-1 rounded-lg border border-white/20 flex items-center gap-1 shadow-xl">
            <i class="bi bi-star-fill text-[10px] text-yellow-400"></i>
            <span class="text-[11px] font-black text-white">{{ movie.rating.toFixed(1) }}</span>
          </div>
        </div>

        <!-- Collection Type Badge (bottom left) -->
        <div v-if="movie.collection_type" class="absolute bottom-3 left-3 z-20">
          <span class="text-[9px] font-black text-white/90 uppercase tracking-widest bg-white/10 backdrop-blur-md px-2 py-1 rounded-lg border border-white/10 shadow-lg">
            {{ movie.collection_type }}
          </span>
        </div>

        <!-- Hover overlay with actions -->
        <div class="absolute inset-0 bg-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3 gap-2">
          <button
            @click.stop="router.push(`/movies/${movie.id}/edit`)"
            class="flex-1 bg-blue-600/80 hover:bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest py-1.5 rounded-lg transition-colors flex items-center justify-center gap-1.5"
          >
            <i class="bi bi-pencil-fill"></i> Bearbeiten
          </button>
          <button
            @click.stop="$emit('delete')"
            class="w-8 h-8 bg-blue-600/80 hover:bg-blue-600 text-white rounded-lg flex items-center justify-center transition-colors text-sm"
            title="Löschen"
          >
            <i class="bi bi-trash3-fill"></i>
          </button>
        </div>
      </div>

    </div>

    <div class="mt-2 px-1">
      <p class="text-[12px] font-black text-[var(--text-main)] truncate uppercase tracking-tight opacity-90">{{ movie.title }}</p>
      <p class="text-[10px] text-[var(--text-muted)] font-bold">{{ movie.year }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useApi } from '@/composables/useApi'
import type { Movie } from '@/stores/movies'

defineProps<{ movie: Movie }>()
defineEmits<{ delete: [] }>()

const router = useRouter()
const { resolveMediaUrl } = useApi()

const TAG_MAP: Record<string, { label: string; bg: string }> = {
  DVD:       { label: 'DVD',     bg: 'bg-orange-800/80' },
  BluRay:    { label: 'Blu-ray', bg: 'bg-blue-800/80'   },
  '4K':      { label: '4K UHD',  bg: 'bg-cyan-800/80'   },
  Streaming: { label: 'Stream',  bg: 'bg-emerald-800/80' },
  Digital:   { label: 'Digital', bg: 'bg-violet-800/80' },
  VHS:       { label: 'VHS',     bg: 'bg-stone-600/80'  },
  Leihe:     { label: 'Leihe',   bg: 'bg-amber-800/80'  },
}

function tagStyle(tag: string) {
  return TAG_MAP[tag] ?? { label: tag, bg: 'bg-black/50' }
}
</script>
