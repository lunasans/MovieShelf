import { ipcMain } from 'electron'
import { getDb } from '../database'

const ALLOWED_SETTINGS_KEYS = new Set(['mode', 'theme', 'shelf_url', 'shelf_token', 'tmdb_api_key', 'last_sync_at'])

export function registerSettingsHandlers(): void {
  const db = () => getDb()

  ipcMain.handle('settings:get', (_event, key: string) => {
    if (!ALLOWED_SETTINGS_KEYS.has(key)) return null
    const row = db().prepare('SELECT value FROM settings WHERE key = ?').get(key) as { value: string } | undefined
    return row?.value ?? null
  })

  ipcMain.handle('settings:set', (_event, key: string, value: unknown) => {
    if (!ALLOWED_SETTINGS_KEYS.has(key)) return false
    db().prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)').run(key, String(value))
    return true
  })

  ipcMain.handle('settings:getAll', () => {
    const rows = db().prepare('SELECT key, value FROM settings').all() as { key: string; value: string }[]
    return Object.fromEntries(rows.map(r => [r.key, r.value]))
  })
}
