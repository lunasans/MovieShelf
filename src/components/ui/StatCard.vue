<template>
  <div class="bg-[#12121a] rounded-2xl border border-white/5 p-5 flex items-center gap-4">
    <div class="w-12 h-12 bg-blue-600/20 rounded-xl flex items-center justify-center text-xl">{{ icon }}</div>
    <div>
      <p class="text-2xl font-black text-white">{{ value }}</p>
      <p class="text-xs text-white/40 font-bold uppercase tracking-widest">{{ label }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ label: string; value: number | string; icon: string }>()
</script>
