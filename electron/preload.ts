import { contextBridge, ipcRenderer } from 'electron'

contextBridge.exposeInMainWorld('electron', {
  getIsDev: () => ipcRenderer.invoke('get-is-dev'),
  getVersion: () => ipcRenderer.invoke('app:get-version'),
  // Window controls
  window: {
    minimize: () => ipcRenderer.send('window:minimize'),
    maximize: () => ipcRenderer.send('window:maximize'),
    close:    () => ipcRenderer.send('window:close'),
  },

  // Local database (offline mode)
  db: {
    movies: {
      list:    (params?: object)       => ipcRenderer.invoke('db:movies:list', params),
      get:     (id: number)            => ipcRenderer.invoke('db:movies:get', id),
      create:  (data: object)          => ipcRenderer.invoke('db:movies:create', data),
      update:  (id: number, data: object) => ipcRenderer.invoke('db:movies:update', id, data),
      delete:  (id: number)            => ipcRenderer.invoke('db:movies:delete', id),
      download: (url: string, id: number, type: 'cover' | 'backdrop' | 'actor') => ipcRenderer.invoke('media:download', { url, id, type }),
      exists:   (id: number, type: 'cover' | 'backdrop' | 'actor')           => ipcRenderer.invoke('media:exists',   { id, type }),
      actors: {
        getForMovie: (movieId: number) => ipcRenderer.invoke('db:movies:actors', movieId),
        upsert: (data: any) => ipcRenderer.invoke('db:actors:upsert', data),
        link:   (params: any) => ipcRenderer.invoke('db:actors:link', params),
        get:    (id: number) => ipcRenderer.invoke('db:actors:get', id),
        movies: (actorId: number) => ipcRenderer.invoke('db:actors:movies', actorId),
      },
      sync: {
        dirty:      () => ipcRenderer.invoke('db:sync:dirty'),
        markSynced: (p: any) => ipcRenderer.invoke('db:sync:mark-synced', p),
        hardDelete: (id: number) => ipcRenderer.invoke('db:sync:hard-delete', id),
      },
      checkTmdbIds: (ids: number[]) => ipcRenderer.invoke('db:movies:check-tmdb-ids', ids),
      clear: () => ipcRenderer.invoke('db:movies:clear', true),
    },
    lists: {
      list:        ()                                => ipcRenderer.invoke('db:lists:list'),
      get:         (id: number)                     => ipcRenderer.invoke('db:lists:get', id),
      create:      (name: string)                   => ipcRenderer.invoke('db:lists:create', name),
      update:      (id: number, name: string)       => ipcRenderer.invoke('db:lists:update', id, name),
      delete:      (id: number)                     => ipcRenderer.invoke('db:lists:delete', id),
      addMovie:    (listId: number, movieId: number) => ipcRenderer.invoke('db:lists:add-movie', listId, movieId),
      removeMovie: (listId: number, movieId: number) => ipcRenderer.invoke('db:lists:remove-movie', listId, movieId),
      forMovie:    (movieId: number)                => ipcRenderer.invoke('db:lists:for-movie', movieId),
    },
  },

  // Navigation from main process
  onNavigate: (callback: (path: string) => void) => {
    ipcRenderer.removeAllListeners('navigate-to')
    ipcRenderer.on('navigate-to', (_event, path) => callback(path))
  },

  // Updater
  update: {
    install:    (url: string, sha256?: string) => ipcRenderer.invoke('update:install', url, sha256),
    onProgress: (callback: (percent: number) => void) => {
      ipcRenderer.on('update:progress', (_event, percent) => callback(percent))
    },
  },

  // Trailer
  trailer: {
    open: (url: string) => ipcRenderer.invoke('trailer:open', url),
  },

  // Stats
  stats: {
    openWindow: () => ipcRenderer.invoke('stats:open-window'),
    get:        () => ipcRenderer.invoke('db:stats:get'),
  },

  // Settings
  settings: {
    get:    (key: string)              => ipcRenderer.invoke('settings:get', key),
    set:    (key: string, value: unknown) => ipcRenderer.invoke('settings:set', key, value),
    getAll: ()                         => ipcRenderer.invoke('settings:getAll'),
  },

  // OAuth
  oauth: {
    openBrowser: (url: string) => ipcRenderer.invoke('oauth:open-browser', url),
    onCallback:  (cb: (payload: { code: string; state: string }) => void) => {
      ipcRenderer.removeAllListeners('oauth:callback')
      ipcRenderer.on('oauth:callback', (_event, payload) => cb(payload))
    },
  },
})
