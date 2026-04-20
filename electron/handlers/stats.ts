import { ipcMain, BrowserWindow, app } from 'electron'
import { join } from 'path'
import { getDb } from '../database'

let statsWindow: BrowserWindow | null = null

export function registerStatsHandlers(): void {
  const db = () => getDb()

  ipcMain.handle('stats:open-window', () => {
    if (statsWindow && !statsWindow.isDestroyed()) {
      statsWindow.focus()
      return
    }

    const isDev = !app.isPackaged || process.env.NODE_ENV === 'development'

    statsWindow = new BrowserWindow({
      width: 1000,
      height: 750,
      minWidth: 800,
      minHeight: 600,
      frame: false,
      titleBarStyle: 'hidden',
      backgroundColor: '#0a0a0f',
      webPreferences: {
        preload: join(__dirname, 'preload.js'),
        contextIsolation: true,
        nodeIntegration: false,
      },
    })

    if (isDev) {
      statsWindow.loadURL('http://localhost:5173?popup=1#/stats')
    } else {
      statsWindow.loadFile(join(__dirname, '../dist/index.html'), {
        search: 'popup=1',
        hash: '/stats',
      })
    }

    statsWindow.on('closed', () => {
      statsWindow = null
    })
  })

  ipcMain.handle('db:stats:get', () => {
    const BASE = "is_deleted = 0 AND is_boxset = 0 AND in_collection = 1"

    const totalMovies = (db().prepare(
      `SELECT COUNT(*) as count FROM movies WHERE ${BASE}`
    ).get() as { count: number }).count

    const totalRuntime = (db().prepare(
      `SELECT COALESCE(SUM(runtime), 0) as total FROM movies WHERE ${BASE} AND runtime IS NOT NULL`
    ).get() as { total: number }).total

    // Genre counts — split comma-separated values
    const movieGenres = db().prepare(
      `SELECT genre FROM movies WHERE ${BASE} AND genre IS NOT NULL AND genre != ''`
    ).all() as { genre: string }[]

    const genreMap: Record<string, number> = {}
    for (const row of movieGenres) {
      for (const g of row.genre.split(',')) {
        const key = g.trim()
        if (key) genreMap[key] = (genreMap[key] ?? 0) + 1
      }
    }
    const genres = Object.entries(genreMap)
      .sort((a, b) => b[1] - a[1])
      .slice(0, 12)
      .map(([name, count]) => ({ name, count }))

    // Films per year
    const byYear = db().prepare(`
      SELECT year, COUNT(*) as count
      FROM movies
      WHERE ${BASE} AND year IS NOT NULL
      GROUP BY year
      ORDER BY year ASC
    `).all() as { year: number; count: number }[]

    // Top actors from actors table
    const topActors = db().prepare(`
      SELECT a.name, a.remote_id, a.image_path, COUNT(fa.film_id) as movie_count
      FROM actors a
      JOIN film_actor fa ON a.id = fa.actor_id
      JOIN movies m ON fa.film_id = m.id
      WHERE m.${BASE}
      GROUP BY a.id
      ORDER BY movie_count DESC
      LIMIT 10
    `).all() as { name: string; remote_id: number | null; image_path: string | null; movie_count: number }[]

    // Films per collection type (BoxSet included here intentionally for overview)
    const byType = db().prepare(`
      SELECT collection_type, COUNT(*) as count
      FROM movies
      WHERE is_deleted = 0
      GROUP BY collection_type
      ORDER BY count DESC
    `).all() as { collection_type: string; count: number }[]

    // Runtime distribution in buckets
    const runtimeBuckets = [
      { label: '< 60 min',    min: 0,   max: 59  },
      { label: '60–90 min',   min: 60,  max: 90  },
      { label: '90–120 min',  min: 91,  max: 120 },
      { label: '120–150 min', min: 121, max: 150 },
      { label: '> 150 min',   min: 151, max: 99999 },
    ]
    const byRuntime = runtimeBuckets.map(b => ({
      label: b.label,
      count: (db().prepare(
        `SELECT COUNT(*) as count FROM movies WHERE ${BASE} AND runtime >= ? AND runtime <= ?`
      ).get(b.min, b.max) as { count: number }).count,
    }))

    const watchedMovies = (db().prepare(
      `SELECT COUNT(*) as count FROM movies WHERE ${BASE} AND is_watched = 1`
    ).get() as { count: number }).count

    const avgRating = (db().prepare(
      `SELECT ROUND(AVG(rating), 1) as avg FROM movies WHERE ${BASE} AND rating > 0`
    ).get() as { avg: number | null }).avg ?? 0

    return { totalMovies, totalRuntime, genres, byYear, topActors, byType, byRuntime, watchedMovies, avgRating }
  })
}
