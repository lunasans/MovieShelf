<template>
  <!-- Popup mode (stats window etc.) — no sidebar/titlebar -->
  <div v-if="isPopup" class="h-screen overflow-y-auto bg-[var(--bg-app)]">
    <router-view />
  </div>

  <!-- Normal app layout -->
  <div v-else class="flex flex-col h-screen overflow-hidden">
    <TitleBar />
    <div class="flex flex-1 overflow-hidden">
      <Sidebar />
      <main class="flex-1 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { useUpdateService } from '@/services/updateService'
import TitleBar from '@/components/layout/TitleBar.vue'
import Sidebar from '@/components/layout/Sidebar.vue'

const isPopup = new URLSearchParams(window.location.search).get('popup') === '1'

const settings = useSettingsStore()
const { checkForUpdates } = useUpdateService()

const applyTheme = (theme: string) => {
  let finalTheme = theme
  if (theme === 'system') {
    finalTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }
  document.documentElement.classList.remove('theme-light', 'theme-dark')
  document.documentElement.classList.add(`theme-${finalTheme}`)
}

// Theme sofort anwenden bevor Settings async geladen werden
applyTheme('dark')

onMounted(async () => {
  await settings.load()
  applyTheme(settings.theme)
  
  // Automatischer Update-Check beim Start
  checkForUpdates()
  
  // Watch for OS theme changes if in system mode
  const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
  mediaQuery.addEventListener('change', () => {
    if (settings.theme === 'system') {
      applyTheme('system')
    }
  })
})

watch(() => settings.theme, (newTheme) => {
  applyTheme(newTheme)
})
</script>
