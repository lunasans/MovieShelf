import { ipcMain } from 'electron'
import { getDb } from '../database'

export function registerMovieHandlers(): void {
  const db = () => getDb()

  ipcMain.handle('db:movies:list', (_event, params: { page?: number; perPage?: number; q?: string } = {}) => {
    const { page = 1, perPage = 30, q } = params
    const offset = (page - 1) * perPage

    if (q) {
      const like = `%${q}%`
      const rows = db().prepare(`
        SELECT * FROM movies
        WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?
        ORDER BY title ASC LIMIT ? OFFSET ?
      `).all(like, like, like, perPage, offset)

      const total = (db().prepare(`
        SELECT COUNT(*) as count FROM movies
        WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?
      `).get(like, like, like) as { count: number }).count

      return { data: rows, total, page, perPage }
    }

    const rows = db().prepare('SELECT * FROM movies ORDER BY title ASC LIMIT ? OFFSET ?').all(perPage, offset)
    const total = (db().prepare('SELECT COUNT(*) as count FROM movies').get() as { count: number }).count

    return { data: rows, total, page, perPage }
  })

  ipcMain.handle('db:movies:get', (_event, id: number) => {
    return db().prepare('SELECT * FROM movies WHERE id = ?').get(id)
  })

  ipcMain.handle('db:movies:create', (_event, data: Record<string, unknown>) => {
    const now = new Date().toISOString()
    const stmt = db().prepare(`
      INSERT INTO movies (title, year, genre, director, runtime, rating, rating_age, overview,
        cover_path, backdrop_path, trailer_url, collection_type, tag, tmdb_id, remote_id, created_at, updated_at)
      VALUES (@title, @year, @genre, @director, @runtime, @rating, @rating_age, @overview,
        @cover_path, @backdrop_path, @trailer_url, @collection_type, @tag, @tmdb_id, @remote_id, @created_at, @updated_at)
    `)
    const result = stmt.run({ ...data, created_at: now, updated_at: now })
    return db().prepare('SELECT * FROM movies WHERE id = ?').get(result.lastInsertRowid)
  })

  ipcMain.handle('db:movies:update', (_event, id: number, data: Record<string, unknown>) => {
    const now = new Date().toISOString()
    const fields = Object.keys(data).map(k => `${k} = @${k}`).join(', ')
    db().prepare(`UPDATE movies SET ${fields}, updated_at = @updated_at WHERE id = @id`)
      .run({ ...data, updated_at: now, id })
    return db().prepare('SELECT * FROM movies WHERE id = ?').get(id)
  })

  ipcMain.handle('db:movies:delete', (_event, id: number) => {
    db().prepare('DELETE FROM movies WHERE id = ?').run(id)
    return { success: true }
  })

  ipcMain.handle('db:movies:search', (_event, query: string) => {
    const like = `%${query}%`
    return db().prepare(`
      SELECT * FROM movies
      WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?
      ORDER BY title ASC LIMIT 50
    `).all(like, like, like)
  })
}
