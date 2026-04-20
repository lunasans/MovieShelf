import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Movie } from '@/stores/movies'

export interface MovieList {
  id: number
  name: string
  movie_count: number
  created_at: string
  updated_at: string
}

export interface MovieListDetail extends MovieList {
  movies: Movie[]
}

export const useListStore = defineStore('lists', () => {
  const lists = ref<MovieList[]>([])
  const loading = ref(false)

  async function fetchLists() {
    loading.value = true
    try {
      lists.value = (await window.electron.db.lists.list()) as MovieList[]
    } finally {
      loading.value = false
    }
  }

  async function createList(name: string): Promise<MovieList> {
    const created = await window.electron.db.lists.create(name)
    const entry: MovieList = { ...created, movie_count: 0, created_at: '', updated_at: '' }
    lists.value.push(entry)
    lists.value.sort((a, b) => a.name.localeCompare(b.name))
    return entry
  }

  async function renameList(id: number, name: string): Promise<void> {
    await window.electron.db.lists.update(id, name)
    const entry = lists.value.find(l => l.id === id)
    if (entry) {
      entry.name = name
      lists.value.sort((a, b) => a.name.localeCompare(b.name))
    }
  }

  async function deleteList(id: number): Promise<void> {
    await window.electron.db.lists.delete(id)
    lists.value = lists.value.filter(l => l.id !== id)
  }

  async function addMovie(listId: number, movieId: number): Promise<void> {
    await window.electron.db.lists.addMovie(listId, movieId)
    const entry = lists.value.find(l => l.id === listId)
    if (entry) entry.movie_count++
  }

  async function removeMovie(listId: number, movieId: number): Promise<void> {
    await window.electron.db.lists.removeMovie(listId, movieId)
    const entry = lists.value.find(l => l.id === listId)
    if (entry && entry.movie_count > 0) entry.movie_count--
  }

  return { lists, loading, fetchLists, createList, renameList, deleteList, addMovie, removeMovie }
})
