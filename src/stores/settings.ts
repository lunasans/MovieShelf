import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useSettingsStore = defineStore('settings', () => {
  const mode     = ref<'standalone' | 'online'>('standalone')
  const shelfUrl = ref('')
  const token    = ref('')

  const isOnline = computed(() => mode.value === 'online' && !!token.value)

  async function load() {
    const all = await window.electron.settings.getAll()
    mode.value     = all.mode     === 'online' ? 'online' : 'standalone'
    shelfUrl.value = all.shelf_url  ?? ''
    token.value    = all.shelf_token ?? ''
  }

  async function save() {
    await window.electron.settings.set('mode',         mode.value)
    await window.electron.settings.set('shelf_url',    shelfUrl.value)
    await window.electron.settings.set('shelf_token',  token.value)
  }

  return { mode, shelfUrl, token, isOnline, load, save }
})
