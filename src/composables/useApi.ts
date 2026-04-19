import axios from 'axios'
import { computed } from 'vue'
import { useSettingsStore } from '@/stores/settings'

export function useApi() {
  const settings = useSettingsStore()
  const isOnline = computed(() => settings.isOnline)

  function client() {
    return axios.create({
      baseURL: `${new URL(settings.shelfUrl).origin}/api`,
      headers: {
        Authorization: `Bearer ${settings.token}`,
        Accept: 'application/json',
      },
    })
  }

  async function apiGet(path: string, params: object = {}) {
    const res = await client().get(path, { params })
    return res.data
  }

  async function apiPost(path: string, data: object = {}) {
    const res = await client().post(path, data)
    return res.data
  }

  async function apiPut(path: string, data: object = {}) {
    const res = await client().put(path, data)
    return res.data
  }

  async function apiDelete(path: string) {
    const res = await client().delete(path)
    return res.data
  }

  async function login(shelfUrl: string, email: string, password: string): Promise<string> {
    const res = await axios.post(`${shelfUrl}/api/login`, {
      email,
      password,
      device_name: 'MovieShelf Desktop',
    })
    return res.data.token
  }

  function resolveMediaUrl(path: string | null | undefined, remoteId?: number, type: 'cover' | 'backdrop' | 'actor' = 'cover'): string | null {
    if (!path && !remoteId) return null

    // 1. Prioritize online shelf URL if we are online and have a path
    // This ensures covers are visible immediately after connecting, even before sync.
    if (settings.shelfUrl && settings.isOnline && path && !path.startsWith('http')) {
      return settings.shelfUrl.replace(/\/$/, '') + '/' + path.replace(/^\//, '')
    }

    // 2. If path is already a full URL, use it
    if (path && path.startsWith('http')) return path

    // 3. Fallback to local resources (synced files) if we have a remote ID
    if (remoteId) {
      let fileName = `${remoteId}.jpg`
      if (type === 'backdrop') fileName = `${remoteId}_backdrop.jpg`
      if (type === 'actor') fileName = `actor_${remoteId}.jpg`
      return `movie-resource://${fileName}`
    }

    // 4. Last resort: use shelf URL if path is relative (even if currently offline)
    if (path && settings.shelfUrl) {
      return settings.shelfUrl.replace(/\/$/, '') + '/' + path.replace(/^\//, '')
    }

    return null
  }

  return { isOnline, apiGet, apiPost, apiPut, apiDelete, login, resolveMediaUrl }
}
