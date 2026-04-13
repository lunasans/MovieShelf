export {}

declare global {
  interface Window {
    electron: {
      window: {
        minimize: () => void
        maximize: () => void
        close: () => void
      }
      db: {
        movies: {
          list:   (params?: { page?: number; perPage?: number; q?: string }) => Promise<{ data: unknown[]; total: number; page: number; perPage: number }>
          get:    (id: number) => Promise<unknown>
          create: (data: Record<string, unknown>) => Promise<unknown>
          update: (id: number, data: Record<string, unknown>) => Promise<unknown>
          delete: (id: number) => Promise<{ success: boolean }>
          search: (query: string) => Promise<unknown[]>
        }
      }
      settings: {
        get:    (key: string) => Promise<string | null>
        set:    (key: string, value: unknown) => Promise<boolean>
        getAll: () => Promise<Record<string, string>>
      }
    }
  }
}
