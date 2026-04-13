<template>
  <div class="p-8 max-w-xl">
    <h1 class="text-2xl font-black text-white uppercase tracking-tight mb-1">Einstellungen</h1>
    <p class="text-sm text-white/40 mb-8">Verbindung zu deiner MovieShelf konfigurieren</p>

    <!-- Mode Toggle -->
    <div class="bg-[#12121a] rounded-2xl border border-white/5 p-6 mb-4">
      <h2 class="text-sm font-black uppercase tracking-widest text-white/40 mb-4">Modus</h2>
      <div class="flex gap-3">
        <button
          @click="settings.mode = 'standalone'"
          :class="settings.mode === 'standalone' ? 'bg-blue-600 border-blue-500 text-white' : 'bg-white/5 border-white/10 text-white/50'"
          class="flex-1 py-3 rounded-xl border text-sm font-bold transition-all"
        >
          Standalone
        </button>
        <button
          @click="settings.mode = 'online'"
          :class="settings.mode === 'online' ? 'bg-blue-600 border-blue-500 text-white' : 'bg-white/5 border-white/10 text-white/50'"
          class="flex-1 py-3 rounded-xl border text-sm font-bold transition-all"
        >
          Mit MovieShelf verbinden
        </button>
      </div>
    </div>

    <!-- Online Settings -->
    <div v-if="settings.mode === 'online'" class="bg-[#12121a] rounded-2xl border border-white/5 p-6 mb-4">
      <h2 class="text-sm font-black uppercase tracking-widest text-white/40 mb-4">Shelf-Verbindung</h2>

      <div class="space-y-4">
        <div>
          <label class="text-xs text-white/40 font-bold uppercase tracking-widest block mb-1">Shelf URL</label>
          <input
            v-model="settings.shelfUrl"
            type="url"
            placeholder="https://dein-name.movieshelf.info"
            class="w-full bg-[#0a0a0f] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-blue-500/50"
          />
        </div>

        <div>
          <label class="text-xs text-white/40 font-bold uppercase tracking-widest block mb-1">E-Mail</label>
          <input v-model="loginEmail" type="email" placeholder="deine@email.de"
            class="w-full bg-[#0a0a0f] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-blue-500/50" />
        </div>

        <div>
          <label class="text-xs text-white/40 font-bold uppercase tracking-widest block mb-1">Passwort</label>
          <input v-model="loginPassword" type="password" placeholder="••••••••"
            class="w-full bg-[#0a0a0f] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-blue-500/50" />
        </div>

        <button
          @click="doLogin"
          :disabled="loginLoading"
          class="w-full bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-bold py-3 rounded-xl transition-colors text-sm"
        >
          {{ loginLoading ? 'Verbinde...' : 'Anmelden & Verbinden' }}
        </button>

        <p v-if="loginError" class="text-red-400 text-xs text-center">{{ loginError }}</p>
        <p v-if="loginSuccess" class="text-green-400 text-xs text-center">✓ Erfolgreich verbunden!</p>
      </div>
    </div>

    <!-- Save Button -->
    <button
      @click="save"
      class="w-full bg-white/5 hover:bg-white/10 border border-white/10 text-white font-bold py-3 rounded-xl transition-colors text-sm"
    >
      Einstellungen speichern
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { useApi } from '@/composables/useApi'

const settings = useSettingsStore()
const { login } = useApi()

const loginEmail    = ref('')
const loginPassword = ref('')
const loginLoading  = ref(false)
const loginError    = ref('')
const loginSuccess  = ref(false)

onMounted(() => settings.load())

async function doLogin() {
  loginError.value   = ''
  loginSuccess.value = false
  loginLoading.value = true
  try {
    const token = await login(settings.shelfUrl, loginEmail.value, loginPassword.value)
    settings.token = token
    await settings.save()
    loginSuccess.value = true
    loginPassword.value = ''
  } catch (e: unknown) {
    loginError.value = (e as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Anmeldung fehlgeschlagen.'
  } finally {
    loginLoading.value = false
  }
}

async function save() {
  await settings.save()
}
</script>
