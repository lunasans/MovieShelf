import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import { useSettingsStore } from '@/stores/settings'
import './style.css'

async function init() {
  const app   = createApp(App)
  const pinia = createPinia()
  app.use(pinia)
  app.use(router)

  // App sofort mounten — Settings werden in App.vue nachgeladen
  app.mount('#app')

  // Settings im Hintergrund laden (blockiert nicht mehr das Rendering)
  useSettingsStore(pinia).load().catch(console.error)

  window.electron.onNavigate((path: string) => {
    router.push(path)
  })
}

init()
