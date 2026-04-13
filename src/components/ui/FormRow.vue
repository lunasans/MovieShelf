<template>
  <div>
    <label class="text-xs text-white/40 font-bold uppercase tracking-widest block mb-1">{{ label }}</label>
    <slot />
  </div>
</template>

<script setup lang="ts">
defineProps<{ label: string }>()
</script>
