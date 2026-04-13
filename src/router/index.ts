import { createRouter, createWebHashHistory } from 'vue-router'

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
    },
    {
      path: '/movies',
      name: 'movies',
      component: () => import('@/views/MoviesView.vue'),
    },
    {
      path: '/movies/new',
      name: 'movies.new',
      component: () => import('@/views/MovieFormView.vue'),
    },
    {
      path: '/movies/:id/edit',
      name: 'movies.edit',
      component: () => import('@/views/MovieFormView.vue'),
    },
    {
      path: '/movies/:id',
      name: 'movies.show',
      component: () => import('@/views/MovieDetailView.vue'),
    },
    {
      path: '/tmdb',
      name: 'tmdb',
      component: () => import('@/views/TmdbSearchView.vue'),
    },
    {
      path: '/sync',
      name: 'sync',
      component: () => import('@/views/SyncView.vue'),
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('@/views/SettingsView.vue'),
    },
  ],
})

export default router
