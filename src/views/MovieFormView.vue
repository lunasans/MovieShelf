<template>
  <div class="p-8 max-w-4xl">
    <h1 class="text-2xl font-black text-[var(--text-main)] uppercase tracking-tight mb-1">
      {{ isEdit ? 'Film bearbeiten' : 'Film hinzufügen' }}
    </h1>
    <p class="text-sm text-[var(--text-muted)] opacity-60 mb-8">{{ isEdit ? form.title : 'Neuen Film zur Sammlung hinzufügen' }}</p>

    <form @submit.prevent="save" class="space-y-4">
      <FormRow label="Titel *">
        <input v-model="form.title" required type="text" class="input" />
      </FormRow>
      <div class="grid grid-cols-2 gap-4">
        <FormRow label="Jahr">
          <input v-model.number="form.year" type="number" min="1900" max="2099" class="input" />
        </FormRow>
        <FormRow label="Laufzeit (min)">
          <input v-model.number="form.runtime" type="number" class="input" />
        </FormRow>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <FormRow label="Typ">
          <select v-model="form.collection_type" class="input">
            <option>Film</option>
            <option>Serie</option>
            <option>Dokumentation</option>
            <option>Kurzfilm</option>
          </select>
        </FormRow>
        <FormRow label="Format-Tag">
          <select v-model="form.tag" class="input">
            <option value="">—</option>
            <option>DVD</option>
            <option>BluRay</option>
            <option>4K</option>
            <option>Streaming</option>
            <option>Digital</option>
            <option>VHS</option>
            <option>Leihe</option>
          </select>
        </FormRow>
      </div>
      <FormRow label="Genre">
        <input v-model="form.genre" type="text" placeholder="Action, Drama, ..." class="input" />
      </FormRow>
      <FormRow label="Regisseur">
        <input v-model="form.director" type="text" class="input" />
      </FormRow>
      <div class="grid grid-cols-2 gap-4">
        <FormRow label="Bewertung (0–100)">
          <input v-model.number="form.rating" type="number" min="0" max="100" step="0.1" class="input" />
        </FormRow>
        <FormRow label="FSK">
          <input v-model.number="form.rating_age" type="number" class="input" />
        </FormRow>
      </div>
      <FormRow label="Beschreibung">
        <textarea v-model="form.overview" rows="4" class="input resize-none"></textarea>
      </FormRow>
      <FormRow label="Trailer URL">
        <div class="flex gap-2">
          <input v-model="form.trailer_url" type="url" class="input flex-1" placeholder="https://www.youtube.com/watch?v=..." />
          <button 
            type="button"
            @click="searchYouTube"
            class="px-4 bg-[var(--bg-card)] hover:bg-[var(--bg-elevated)] border border-[var(--border-ui)] rounded-xl text-xs font-bold text-[var(--text-main)] transition-colors flex items-center gap-2 whitespace-nowrap"
          >
            <i class="bi bi-youtube text-red-500"></i>
            YouTube Suche
          </button>
        </div>
      </FormRow>
      <FormRow label="TMDb ID">
        <input v-model.number="form.tmdb_id" type="number" class="input" />
      </FormRow>

      <div class="flex gap-3 pt-2">
        <button type="submit" :disabled="saving"
          class="flex-1 bg-red-600 hover:bg-red-500 disabled:opacity-50 text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-red-600/20 flex items-center justify-center gap-2 active:scale-95">
          <i class="bi bi-check-lg"></i>
          {{ saving ? 'Speichern...' : 'Speichern' }}
        </button>
        <router-link to="/movies"
          class="flex-1 text-center bg-[var(--bg-card)] hover:bg-[var(--bg-elevated)] border border-[var(--border-ui)] text-[var(--text-muted)] font-bold py-4 rounded-xl transition-colors flex items-center justify-center gap-2">
          <i class="bi bi-x-lg"></i>
          Abbrechen
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import FormRow from '@/components/ui/FormRow.vue'

const route  = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)
const saving = ref(false)

const form = ref({
  title: '', year: null as number | null, genre: '', director: '',
  runtime: null as number | null, rating: null as number | null,
  rating_age: null as number | null, overview: '', trailer_url: '',
  collection_type: 'Film', tag: '', tmdb_id: null as number | null,
})

function searchYouTube() {
  const query = `${form.value.title} ${form.value.year || ''} trailer`.trim()
  const url = `https://www.youtube.com/results?search_query=${encodeURIComponent(query)}`
  window.open(url, '_blank')
}

onMounted(async () => {
  if (isEdit.value) {
    const id = Number(route.params.id)
    const data = await window.electron.db.movies.get(id)
    Object.assign(form.value, data)
  }
})

async function save() {
  saving.value = true
  try {
    if (isEdit.value) {
      await window.electron.db.movies.update(Number(route.params.id), { ...form.value })
    } else {
      await window.electron.db.movies.create({ ...form.value })
    }
    router.push('/movies')
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.input {
  @apply w-full bg-[var(--bg-app)] border border-[var(--border-ui)] rounded-xl px-4 py-3 text-sm text-[var(--text-main)] placeholder-[var(--text-muted)] focus:outline-none focus:border-red-500/50 transition-colors;
}
</style>
