import { contextBridge, ipcRenderer } from 'electron'

contextBridge.exposeInMainWorld('electron', {
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
      search:  (query: string)         => ipcRenderer.invoke('db:movies:search', query),
    },
  },

  // Settings
  settings: {
    get:    (key: string)              => ipcRenderer.invoke('settings:get', key),
    set:    (key: string, value: unknown) => ipcRenderer.invoke('settings:set', key, value),
    getAll: ()                         => ipcRenderer.invoke('settings:getAll'),
  },
})
