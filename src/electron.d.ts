interface Window {
  electron: {
    getIsDev:   () => Promise<boolean>
    getVersion: () => Promise<string>

    window: {
      minimize: () => void
      maximize: () => void
      close:    () => void
    }

    db: {
      movies: {
        list:         (params?: { page?: number; perPage?: number; q?: string }) => Promise<{ data: unknown[]; total: number; page: number; perPage: number }>
        get:          (id: number) => Promise<unknown>
        create:       (data: Record<string, unknown>) => Promise<unknown>
        update:       (id: number, data: Record<string, unknown>) => Promise<unknown>
        delete:       (id: number) => Promise<{ success: boolean }>
        download:     (url: string, id: number, type: 'cover' | 'backdrop' | 'actor') => Promise<{ success: boolean; path?: string; error?: string }>
        exists:       (id: number, type: 'cover' | 'backdrop' | 'actor') => Promise<boolean>
        checkTmdbIds: (ids: number[]) => Promise<number[]>
        clear:        () => Promise<{ success: boolean }>
        actors: {
          getForMovie: (movieId: number) => Promise<unknown[]>
          upsert:      (data: Record<string, unknown>) => Promise<number>
          link:        (params: { film_id: number; actor_id: number; role?: string; is_main_role?: boolean }) => Promise<unknown>
          get:         (id: number) => Promise<unknown>
          movies:      (actorId: number) => Promise<unknown[]>
        }
        sync: {
          dirty:      () => Promise<unknown[]>
          markSynced: (p: { id: number; remote_id: number; synced_at: string }) => Promise<unknown>
          hardDelete: (id: number) => Promise<unknown>
        }
      }
      lists: {
        list:        ()                                 => Promise<unknown[]>
        get:         (id: number)                      => Promise<unknown>
        create:      (name: string)                    => Promise<unknown>
        update:      (id: number, name: string)        => Promise<unknown>
        delete:      (id: number)                      => Promise<{ success: boolean }>
        addMovie:    (listId: number, movieId: number) => Promise<{ success: boolean }>
        removeMovie: (listId: number, movieId: number) => Promise<{ success: boolean }>
        forMovie:    (movieId: number)                 => Promise<number[]>
      }
    }

    trailer?: {
      open: (url: string) => Promise<void>
    }

    stats: {
      openWindow: () => Promise<void>
      get: () => Promise<{
        totalMovies:   number
        totalRuntime:  number
        watchedMovies: number
        ratedMovies:   number
        genres:    { name: string; count: number }[]
        byYear:    { year: number; count: number }[]
        topActors: { name: string; image_path: string | null; movie_count: number }[]
        byType:    { collection_type: string; count: number }[]
        byRuntime: { label: string; count: number }[]
      }>
    }

    oauth: {
      openBrowser: (url: string) => Promise<void>
      onCallback:  (callback: (payload: { code: string; state: string }) => void) => void
    }

    onNavigate: (callback: (path: string) => void) => void

    update: {
      install:    (url: string, sha256?: string) => Promise<{ success: boolean; error?: string }>
      onProgress: (callback: (percent: number) => void) => void
    }

    settings: {
      get:    (key: string) => Promise<string | null>
      set:    (key: string, value: unknown) => Promise<boolean>
      getAll: () => Promise<Record<string, string>>
    }
  }
}
