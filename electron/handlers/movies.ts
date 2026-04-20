import { ipcMain } from 'electron'
import { getDb } from '../database'

const ALLOWED_MOVIE_COLUMNS = new Set([
  'title', 'year', 'genre', 'director', 'runtime', 'rating', 'rating_age',
  'overview', 'cover_path', 'backdrop_path', 'actors_names', 'trailer_url',
  'collection_type', 'tag', 'tmdb_id', 'remote_id', 'synced_at', 'is_deleted',
])

export function registerMovieHandlers(): void {
  const db = () => getDb()

  ipcMain.handle('db:movies:list', (_event, params: { page?: number; perPage?: number; q?: string } = {}) => {
    const { page = 1, perPage = 30, q } = params
    const offset = (page - 1) * perPage

    if (q) {
      const like = `%${q}%`
      const rows = db().prepare(`
        SELECT * FROM movies
        WHERE is_deleted = 0 AND in_collection = 1 AND (title LIKE ? OR director LIKE ? OR genre LIKE ?)
        ORDER BY title ASC LIMIT ? OFFSET ?
      `).all(like, like, like, perPage, offset)

      const total = (db().prepare(`
        SELECT COUNT(*) as count FROM movies
        WHERE is_deleted = 0 AND in_collection = 1 AND (title LIKE ? OR director LIKE ? OR genre LIKE ?)
      `).get(like, like, like) as { count: number }).count

      return { data: rows, total, page, perPage }
    }

    const rows = db().prepare('SELECT * FROM movies WHERE is_deleted = 0 AND in_collection = 1 ORDER BY title ASC LIMIT ? OFFSET ?').all(perPage, offset)
    const total = (db().prepare('SELECT COUNT(*) as count FROM movies WHERE is_deleted = 0 AND in_collection = 1').get() as { count: number }).count

    return { data: rows, total, page, perPage }
  })

  ipcMain.handle('db:movies:recent', (_event, limit = 10) => {
    return db().prepare(
      "SELECT * FROM movies WHERE is_deleted = 0 AND is_boxset = 0 AND in_collection = 1 ORDER BY created_at DESC LIMIT ?"
    ).all(limit)
  })

  ipcMain.handle('db:movies:get', (_event, id: number) => {
    return db().prepare('SELECT * FROM movies WHERE id = ? AND is_deleted = 0').get(id)
        ?? db().prepare('SELECT * FROM movies WHERE remote_id = ? AND is_deleted = 0').get(id)
  })

  ipcMain.handle('db:movies:get-by-remote-id', (_event, remoteId: number) => {
    return db().prepare('SELECT * FROM movies WHERE remote_id = ? AND is_deleted = 0').get(remoteId) ?? null
  })

  ipcMain.handle('db:movies:create', (_event, data: Record<string, unknown>) => {
    const now = new Date().toISOString()

    // If the incoming record has both remote_id and tmdb_id, check whether a local-only
    // film with the same tmdb_id exists (remote_id IS NULL). If so, merge into it instead
    // of inserting a duplicate.
    if (data.remote_id != null && data.tmdb_id != null) {
      const orphan = db().prepare(
        'SELECT id FROM movies WHERE tmdb_id = ? AND remote_id IS NULL AND is_deleted = 0'
      ).get(data.tmdb_id) as { id: number } | undefined

      if (orphan) {
        db().prepare(`
          UPDATE movies SET
            remote_id = @remote_id, title = @title, year = @year, genre = @genre,
            director = @director, runtime = @runtime, rating = @rating, rating_age = @rating_age,
            overview = @overview, cover_path = @cover_path, backdrop_path = @backdrop_path,
            actors_names = @actors_names, trailer_url = @trailer_url,
            collection_type = @collection_type, tag = @tag,
            is_boxset = @is_boxset, boxset_parent_id = @boxset_parent_id, updated_at = @updated_at
          WHERE id = @id
        `).run({ ...data, updated_at: data.updated_at || now, id: orphan.id })
        return db().prepare('SELECT * FROM movies WHERE id = ?').get(orphan.id)
      }
    }

    const stmt = db().prepare(`
      INSERT INTO movies (title, year, genre, director, runtime, rating, rating_age, overview,
        cover_path, backdrop_path, actors_names, trailer_url, collection_type, tag, tmdb_id, remote_id,
        is_boxset, boxset_parent_id, view_count, is_watched, in_collection, created_at, updated_at)
      VALUES (@title, @year, @genre, @director, @runtime, @rating, @rating_age, @overview,
        @cover_path, @backdrop_path, @actors_names, @trailer_url, @collection_type, @tag, @tmdb_id, @remote_id,
        @is_boxset, @boxset_parent_id, @view_count, @is_watched, @in_collection, @created_at, @updated_at)
      ON CONFLICT(remote_id) DO UPDATE SET
        title = EXCLUDED.title,
        year = EXCLUDED.year,
        genre = EXCLUDED.genre,
        director = EXCLUDED.director,
        runtime = EXCLUDED.runtime,
        rating = EXCLUDED.rating,
        rating_age = EXCLUDED.rating_age,
        overview = EXCLUDED.overview,
        cover_path = EXCLUDED.cover_path,
        backdrop_path = EXCLUDED.backdrop_path,
        actors_names = EXCLUDED.actors_names,
        trailer_url = EXCLUDED.trailer_url,
        collection_type = EXCLUDED.collection_type,
        tag = EXCLUDED.tag,
        tmdb_id = EXCLUDED.tmdb_id,
        is_boxset = EXCLUDED.is_boxset,
        boxset_parent_id = EXCLUDED.boxset_parent_id,
        view_count = EXCLUDED.view_count,
        is_watched = EXCLUDED.is_watched,
        updated_at = EXCLUDED.updated_at
      WHERE EXCLUDED.updated_at >= movies.updated_at
    `)
    const result = stmt.run({
      genre: null, director: null, runtime: null, rating: null, rating_age: null,
      overview: null, cover_path: null, backdrop_path: null, actors_names: null,
      trailer_url: null, collection_type: 'Film', tag: null, tmdb_id: null, remote_id: null,
      is_boxset: 0, boxset_parent_id: null, view_count: 0, is_watched: 0, in_collection: 1,
      ...data,
      created_at: data.created_at || now, updated_at: data.updated_at || now,
    })
    // is_boxset, boxset_parent_id und view_count immer aktualisieren (unabhängig vom Timestamp-Vergleich)
    if (data.remote_id != null) {
      db().prepare(
        'UPDATE movies SET is_boxset = ?, boxset_parent_id = ?, view_count = ?, is_watched = ?, created_at = COALESCE(?, created_at) WHERE remote_id = ?'
      ).run(data.is_boxset ?? 0, data.boxset_parent_id ?? null, data.view_count ?? 0, data.is_watched ?? 0, data.created_at ?? null, data.remote_id)
      return db().prepare('SELECT * FROM movies WHERE remote_id = ?').get(data.remote_id)
    }
    return db().prepare('SELECT * FROM movies WHERE id = ?').get(result.lastInsertRowid)
  })

  ipcMain.handle('db:movies:actors', (_event, movieId: number) => {
    return db().prepare(`
      SELECT a.*, fa.role, fa.is_main_role FROM actors a
      JOIN film_actor fa ON a.id = fa.actor_id
      WHERE fa.film_id = ?
      ORDER BY fa.is_main_role DESC, a.name ASC
    `).all(movieId)
  })

  ipcMain.handle('db:actors:upsert', (_event, data: any) => {
    const now = new Date().toISOString()
    const stmt = db().prepare(`
      INSERT INTO actors (remote_id, name, bio, birthday, place_of_birth, image_path, tmdb_id, created_at, updated_at)
      VALUES (@remote_id, @name, @bio, @birthday, @place_of_birth, @image_path, @tmdb_id, @created_at, @updated_at)
      ON CONFLICT(remote_id) DO UPDATE SET
        name = EXCLUDED.name,
        bio = EXCLUDED.bio,
        birthday = EXCLUDED.birthday,
        place_of_birth = EXCLUDED.place_of_birth,
        image_path = EXCLUDED.image_path,
        tmdb_id = EXCLUDED.tmdb_id,
        updated_at = EXCLUDED.updated_at
    `)
    const defaults = {
      bio: null,
      birthday: null,
      place_of_birth: null,
      image_path: null,
      tmdb_id: null
    }

    const result = stmt.run({ ...defaults, ...data, created_at: now, updated_at: now })

    if (data.remote_id != null) {
      const actor = db().prepare('SELECT id FROM actors WHERE remote_id = ?').get(data.remote_id) as { id: number }
      return actor?.id
    }
    return result.lastInsertRowid ? Number(result.lastInsertRowid) : undefined
  })

  ipcMain.handle('db:actors:link', (_event, { film_id, actor_id, role, is_main_role }: any) => {
    const stmt = db().prepare(`
      INSERT OR REPLACE INTO film_actor (film_id, actor_id, role, is_main_role)
      VALUES (?, ?, ?, ?)
    `)
    return stmt.run(film_id, actor_id, role, is_main_role ? 1 : 0)
  })

  ipcMain.handle('db:actors:get', (_event, id: number) => {
    return db().prepare('SELECT * FROM actors WHERE id = ?').get(id)
  })

  ipcMain.handle('db:actors:movies', (_event, actorId: number) => {
    return db().prepare(`
      SELECT m.* FROM movies m
      JOIN film_actor fa ON m.id = fa.film_id
      WHERE fa.actor_id = ?
      ORDER BY m.year DESC
    `).all(actorId)
  })

  ipcMain.handle('db:movies:update', (_event, id: number, data: Record<string, unknown>) => {
    const now = new Date().toISOString()
    const safeKeys = Object.keys(data).filter(k => ALLOWED_MOVIE_COLUMNS.has(k))
    if (safeKeys.length === 0) return db().prepare('SELECT * FROM movies WHERE id = ?').get(id)
    const fields = safeKeys.map(k => `${k} = @${k}`).join(', ')
    const safeData = Object.fromEntries(safeKeys.map(k => [k, data[k]]))
    db().prepare(`UPDATE movies SET ${fields}, updated_at = @updated_at WHERE id = @id`)
      .run({ ...safeData, updated_at: now, id })
    return db().prepare('SELECT * FROM movies WHERE id = ?').get(id)
  })

  ipcMain.handle('db:movies:delete', (_event, id: number) => {
    const now = new Date().toISOString()
    db().prepare('UPDATE movies SET is_deleted = 1, updated_at = ? WHERE id = ?').run(now, id)
    return { success: true }
  })

  ipcMain.handle('db:movies:search', (_event, query: string) => {
    const like = `%${query}%`
    return db().prepare(`
      SELECT * FROM movies
      WHERE is_deleted = 0 AND (title LIKE ? OR director LIKE ? OR genre LIKE ?)
      ORDER BY title ASC LIMIT 50
    `).all(like, like, like)
  })

  // Sync Handlers
  ipcMain.handle('db:sync:dirty', () => {
    return db().prepare(`
      SELECT * FROM movies 
      WHERE remote_id IS NULL 
         OR updated_at > synced_at 
         OR is_deleted = 1
    `).all()
  })

  ipcMain.handle('db:sync:mark-synced', (_event, { id, remote_id, synced_at }) => {
    return db().prepare('UPDATE movies SET remote_id = ?, synced_at = ?, updated_at = ?, is_deleted = 0 WHERE id = ?')
      .run(remote_id, synced_at, synced_at, id)
  })

  ipcMain.handle('db:sync:hard-delete', (_event, id: number) => {
    return db().prepare('DELETE FROM movies WHERE id = ?').run(id)
  })

  ipcMain.handle('db:movies:check-tmdb-ids', (_event, tmdbIds: number[]) => {
    if (!Array.isArray(tmdbIds) || tmdbIds.length === 0) return []
    const placeholders = tmdbIds.map(() => '?').join(', ')
    const rows = db().prepare(
      `SELECT tmdb_id FROM movies WHERE is_deleted = 0 AND in_collection = 1 AND tmdb_id IN (${placeholders})`
    ).all(...tmdbIds) as { tmdb_id: number }[]
    return rows.map(r => r.tmdb_id)
  })

  ipcMain.handle('db:movies:delete-by-remote-id', (_event, remoteId: number) => {
    const now = new Date().toISOString()
    const row = db().prepare('SELECT id FROM movies WHERE remote_id = ? AND is_deleted = 0').get(remoteId) as { id: number } | undefined
    if (!row) return { success: false }
    db().prepare('UPDATE movies SET is_deleted = 1, updated_at = ? WHERE id = ?').run(now, row.id)
    return { success: true, localId: row.id }
  })

  ipcMain.handle('db:movies:clear', (_event, confirmed?: boolean) => {
    if (!confirmed) return { success: false, error: 'Bestätigung erforderlich.' }
    db().prepare('DELETE FROM film_actor').run()
    db().prepare('DELETE FROM movies').run()
    db().prepare('DELETE FROM actors').run()
    return { success: true }
  })
}
