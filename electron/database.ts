import Database from 'better-sqlite3'
import { app } from 'electron'
import { join } from 'path'
import { mkdirSync } from 'fs'

let db: Database.Database

export function getDb(): Database.Database {
  return db
}

export function setupDatabase(): void {
  const userDataPath = app.getPath('userData')
  mkdirSync(userDataPath, { recursive: true })

  db = new Database(join(userDataPath, 'movieshelf.db'))
  db.pragma('journal_mode = WAL')
  db.pragma('foreign_keys = ON')

  runMigrations()
}

function runMigrations(): void {
  db.exec(`
    CREATE TABLE IF NOT EXISTS movies (
      id          INTEGER PRIMARY KEY AUTOINCREMENT,
      title       TEXT    NOT NULL,
      year        INTEGER,
      genre       TEXT,
      director    TEXT,
      runtime     INTEGER,
      rating      REAL,
      rating_age  INTEGER,
      overview    TEXT,
      cover_path  TEXT,
      backdrop_path TEXT,
      trailer_url TEXT,
      collection_type TEXT DEFAULT 'Film',
      tag         TEXT,
      tmdb_id     INTEGER,
      remote_id   INTEGER,
      synced_at   TEXT,
      created_at  TEXT    DEFAULT (datetime('now')),
      updated_at  TEXT    DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS settings (
      key   TEXT PRIMARY KEY,
      value TEXT
    );

    -- Default settings
    INSERT OR IGNORE INTO settings (key, value) VALUES
      ('mode', 'standalone'),
      ('shelf_url', ''),
      ('shelf_token', '');
  `)
}
