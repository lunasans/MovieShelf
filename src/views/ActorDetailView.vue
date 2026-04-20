<template>
  <div v-if="actor" class="relative min-h-screen bg-[var(--bg-app)]">
    <!-- Actor Profile Header -->
    <div class="px-12 relative z-10 pb-20 pt-8">
      <div class="flex gap-10 items-start">
        <!-- Profile Image -->
        <div class="w-64 flex-shrink-0 shadow-[var(--shadow-main)] rounded-2xl overflow-hidden border border-[var(--border-ui)] aspect-[2/3] bg-[var(--bg-card)]">
          <img
            v-if="resolveMediaUrl(actor.image_path || actor.image_url, Number(actor.remote_id || actor.id), 'actor')"
            :src="resolveMediaUrl(actor.image_path || actor.image_url, Number(actor.remote_id || actor.id), 'actor')!"
            :alt="actor.name as string"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-[var(--text-muted)] opacity-20 text-4xl">👤</div>
        </div>

        <!-- Info -->
        <div class="flex-1 space-y-8">
          <div>
            <h1 class="text-5xl font-black text-[var(--text-main)] uppercase tracking-tighter mb-4">{{ actor.name }}</h1>
            
            <div class="flex items-center gap-6 text-sm font-bold text-[var(--text-muted)]">
              <div v-if="actor.birthday" class="flex items-center gap-2">
                <span class="text-[var(--text-muted)] opacity-50 text-xs uppercase tracking-widest">Geburtstag</span>
                <span class="text-[var(--text-main)] opacity-80">{{ actor.birthday }}</span>
              </div>
              <div v-if="actor.place_of_birth" class="flex items-center gap-2">
                <span class="text-[var(--text-muted)] opacity-50 text-xs uppercase tracking-widest">Geburtsort</span>
                <span class="text-[var(--text-main)] opacity-80">{{ actor.place_of_birth }}</span>
              </div>
            </div>
          </div>

          <div v-if="actor.bio">
            <h3 class="text-[var(--text-muted)] opacity-40 text-xs font-black uppercase tracking-[0.2em] mb-4">Biografie</h3>
            <p class="text-lg text-[var(--text-main)] opacity-70 leading-relaxed font-medium max-w-4xl whitespace-pre-line">
              {{ actor.bio }}
            </p>
          </div>

          <router-link to="/movies" class="inline-flex items-center gap-2 text-red-500 hover:text-red-600 font-bold transition-colors">
            ← Zurück zur Übersicht
          </router-link>
        </div>
      </div>

      <!-- Filmography -->
      <div class="mt-20">
        <h3 class="text-[var(--text-muted)] opacity-40 text-xs font-black uppercase tracking-[0.2em] mb-8">Filmografie in deiner Sammlung</h3>
        
        <div v-if="movies.length > 0" class="grid grid-cols-[repeat(auto-fill,minmax(160px,1fr))] gap-6">
          <MovieCard
            v-for="movie in movies"
            :key="movie.id"
            :movie="movie"
          />
        </div>
        <p v-else class="text-[var(--text-muted)] opacity-40 italic">Keine Filme mit diesem Schauspieler gefunden.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApi } from '@/composables/useApi'
import MovieCard from '@/components/movies/MovieCard.vue'

const route = useRoute()
const { resolveMediaUrl } = useApi()

const actor = ref<any>(null)
const movies = ref<any[]>([])

onMounted(async () => {
  const id = Number(route.params.id)
  actor.value = await window.electron.db.movies.actors.get(id)
  movies.value = await window.electron.db.movies.actors.movies(id)
})
</script>
