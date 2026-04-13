<template>
  <router-link
    :to="to"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200"
    :class="isActive
      ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30'
      : 'text-white/50 hover:text-white hover:bg-white/5 border border-transparent'"
  >
    <span class="text-base leading-none">{{ icon }}</span>
    <span>{{ label }}</span>
  </router-link>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps<{ to: string; icon: string; label: string }>()
const route = useRoute()

const isActive = computed(() =>
  props.to === '/' ? route.path === '/' : route.path.startsWith(props.to)
)
</script>
