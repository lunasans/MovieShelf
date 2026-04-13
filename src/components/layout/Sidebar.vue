<template>
  <aside class="w-56 flex-shrink-0 bg-[#0d0d14] border-r border-white/5 flex flex-col py-4">
    <nav class="flex-1 px-3 space-y-1">
      <SidebarItem to="/" icon="🏠" label="Dashboard" />
      <SidebarItem to="/movies" icon="🎬" label="Filme" />
      <SidebarItem to="/tmdb" icon="🔍" label="TMDb Suche" />
      <SidebarItem to="/sync" icon="🔄" label="Synchronisation" />
    </nav>

    <div class="px-3 mt-4 border-t border-white/5 pt-4">
      <SidebarItem to="/settings" icon="⚙️" label="Einstellungen" />
    </div>
  </aside>
</template>

<script setup lang="ts">
import SidebarItem from './SidebarItem.vue'
</script>
