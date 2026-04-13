<template>
  <div class="flex flex-col h-screen bg-[#0a0a0f] text-white overflow-hidden">
    <!-- Custom Title Bar -->
    <TitleBar />

    <!-- Main Layout -->
    <div class="flex flex-1 overflow-hidden">
      <!-- Sidebar -->
      <Sidebar />

      <!-- Content -->
      <main class="flex-1 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import TitleBar from '@/components/layout/TitleBar.vue'
import Sidebar from '@/components/layout/Sidebar.vue'
</script>
