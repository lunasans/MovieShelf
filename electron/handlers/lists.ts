import { ipcMain } from 'electron'
import { getDb } from '../database'

export function registerListHandlers(): void {
  const db = () => getDb()

  // All lists with movie count
  ipcMain.handle('db:lists:list', () => {
    return db().prepare(`
      SELECT l.*, COUNT(lm.movie_id) as movie_count
      FROM lists l
      LEFT JOIN list_movies lm ON l.id = lm.list_id
      GROUP BY l.id
      ORDER BY l.name ASC
    `).all()
  })

  // Single list with its movies
  ipcMain.handle('db:lists:get', (_event, id: number) => {
    const list = db().prepare('SELECT * FROM lists WHERE id = ?').get(id)
    if (!list) return null
    const movies = db().prepare(`
      SELECT m.* FROM movies m
      JOIN list_movies lm ON m.id = lm.movie_id
      WHERE lm.list_id = ? AND m.is_deleted = 0
      ORDER BY m.title ASC
    `).all(id)
    return { ...(list as object), movies }
  })

  ipcMain.handle('db:lists:create', (_event, name: string) => {
    const now = new Date().toISOString()
    const result = db().prepare(
      'INSERT INTO lists (name, created_at, updated_at) VALUES (?, ?, ?)'
    ).run(name.trim(), now, now)
    return db().prepare('SELECT * FROM lists WHERE id = ?').get(result.lastInsertRowid)
  })

  ipcMain.handle('db:lists:update', (_event, id: number, name: string) => {
    const now = new Date().toISOString()
    db().prepare('UPDATE lists SET name = ?, updated_at = ? WHERE id = ?').run(name.trim(), now, id)
    return db().prepare('SELECT * FROM lists WHERE id = ?').get(id)
  })

  ipcMain.handle('db:lists:delete', (_event, id: number) => {
    db().prepare('DELETE FROM lists WHERE id = ?').run(id)
    return { success: true }
  })

  // Returns all lists with their movie remote_ids (for sync)
  ipcMain.handle('db:lists:sync-state', () => {
    const lists = db().prepare('SELECT * FROM lists').all() as any[]
    return lists.map(list => {
      const movies = db().prepare(`
        SELECT m.remote_id FROM movies m
        JOIN list_movies lm ON m.id = lm.movie_id
        WHERE lm.list_id = ? AND m.remote_id IS NOT NULL
      `).all(list.id) as { remote_id: number }[]
      return {
        id: list.id,
        name: list.name,
        remote_id: list.remote_id ?? null,
        synced_at: list.synced_at ?? null,
        updated_at: list.updated_at,
        movie_remote_ids: movies.map(m => m.remote_id),
      }
    })
  })

  ipcMain.handle('db:lists:set-remote-id', (_event, id: number, remoteId: number) => {
    const now = new Date().toISOString()
    db().prepare('UPDATE lists SET remote_id = ?, synced_at = ? WHERE id = ?').run(remoteId, now, id)
    return { success: true }
  })

  ipcMain.handle('db:lists:mark-synced', (_event, id: number) => {
    const now = new Date().toISOString()
    db().prepare('UPDATE lists SET synced_at = ? WHERE id = ?').run(now, id)
    return { success: true }
  })

  ipcMain.handle('db:lists:delete-by-remote-id', (_event, remoteId: number) => {
    const list = db().prepare('SELECT id FROM lists WHERE remote_id = ?').get(remoteId) as { id: number } | undefined
    if (!list) return { success: false }
    db().prepare('DELETE FROM lists WHERE id = ?').run(list.id)
    return { success: true }
  })

  ipcMain.handle('db:lists:add-movie', (_event, listId: number, movieId: number) => {
    const now = new Date().toISOString()
    db().prepare(
      'INSERT OR IGNORE INTO list_movies (list_id, movie_id, added_at) VALUES (?, ?, ?)'
    ).run(listId, movieId, now)
    db().prepare('UPDATE lists SET updated_at = ? WHERE id = ?').run(now, listId)
    return { success: true }
  })

  ipcMain.handle('db:lists:remove-movie', (_event, listId: number, movieId: number) => {
    db().prepare('DELETE FROM list_movies WHERE list_id = ? AND movie_id = ?').run(listId, movieId)

    // Film mit in_collection=0 der nirgendwo mehr in einer Liste ist → löschen
    const movie = db().prepare(
      'SELECT in_collection FROM movies WHERE id = ?'
    ).get(movieId) as { in_collection: number } | undefined

    if (movie?.in_collection === 0) {
      const remaining = (db().prepare(
        'SELECT COUNT(*) as count FROM list_movies WHERE movie_id = ?'
      ).get(movieId) as { count: number }).count

      if (remaining === 0) {
        db().prepare('DELETE FROM movies WHERE id = ?').run(movieId)
      }
    }

    return { success: true }
  })

  // Returns list IDs that contain a given movie
  ipcMain.handle('db:lists:for-movie', (_event, movieId: number) => {
    const rows = db().prepare(
      'SELECT list_id FROM list_movies WHERE movie_id = ?'
    ).all(movieId) as { list_id: number }[]
    return rows.map(r => r.list_id)
  })
}
