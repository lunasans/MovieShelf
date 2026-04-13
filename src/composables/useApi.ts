import axios from 'axios'
import { computed } from 'vue'
import { useSettingsStore } from '@/stores/settings'

export function useApi() {
  const settings = useSettingsStore()
  const isOnline = computed(() => settings.isOnline)

  function client() {
    return axios.create({
      baseURL: `${settings.shelfUrl}/api`,
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

  return { isOnline, apiGet, apiPost, apiPut, apiDelete, login }
}
