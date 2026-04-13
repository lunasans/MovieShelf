import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApi } from '@/composables/useApi'

export interface Movie {
  id: number
  title: string
  year: number | null
  genre: string | null
  director: string | null
  runtime: number | null
  rating: number | null
  rating_age: number | null
  overview: string | null
  cover_path: string | null
  cover_url?: string | null
  collection_type: string
  tag: string | null
  tmdb_id: number | null
  remote_id: number | null
  created_at: string
  updated_at: string
}

export const useMovieStore = defineStore('movies', () => {
  const movies  = ref<Movie[]>([])
  const total   = ref(0)
  const loading = ref(false)
  const page    = ref(1)
  const perPage = ref(30)

  const { isOnline, apiGet } = useApi()

  async function fetchMovies(params: { q?: string; page?: number } = {}) {
    loading.value = true
    try {
      if (isOnline.value) {
        const data = await apiGet('/movies', { per_page: perPage.value, ...params })
        movies.value = data.data
        total.value  = data.meta?.total ?? data.data.length
      } else {
        const result = await window.electron.db.movies.list({ ...params, perPage: perPage.value })
        movies.value = result.data
        total.value  = result.total
        page.value   = result.page
      }
    } finally {
      loading.value = false
    }
  }

  async function deleteMovie(id: number) {
    if (isOnline.value) {
      const { apiDelete } = useApi()
      await apiDelete(`/admin/movies/${id}`)
    } else {
      await window.electron.db.movies.delete(id)
    }
    movies.value = movies.value.filter(m => m.id !== id)
    total.value--
  }

  return { movies, total, loading, page, perPage, fetchMovies, deleteMovie }
})
