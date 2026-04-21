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
      actors_names TEXT,
      trailer_url TEXT,
      collection_type TEXT DEFAULT 'Film',
      tag         TEXT,
      tmdb_id     INTEGER,
      remote_id   INTEGER,
      synced_at   TEXT,
      is_deleted  INTEGER DEFAULT 0,
      created_at  TEXT    DEFAULT (datetime('now')),
      updated_at  TEXT    DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS settings (
      key   TEXT PRIMARY KEY,
      value TEXT
    );

    CREATE TABLE IF NOT EXISTS actors (
      id              INTEGER PRIMARY KEY AUTOINCREMENT,
      remote_id       INTEGER UNIQUE,
      name            TEXT NOT NULL,
      bio             TEXT,
      birthday        TEXT,
      place_of_birth  TEXT,
      image_path      TEXT,
      tmdb_id         INTEGER,
      created_at      TEXT DEFAULT (datetime('now')),
      updated_at      TEXT DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS film_actor (
      film_id         INTEGER,
      actor_id        INTEGER,
      role            TEXT,
      is_main_role    INTEGER DEFAULT 0,
      PRIMARY KEY (film_id, actor_id),
      FOREIGN KEY (film_id) REFERENCES movies(id) ON DELETE CASCADE,
      FOREIGN KEY (actor_id) REFERENCES actors(id) ON DELETE CASCADE
    );

    -- Default settings
    INSERT OR IGNORE INTO settings (key, value) VALUES
      ('mode', 'standalone'),
      ('shelf_url', ''),
      ('shelf_token', '');
  `)

  // Migrations for existing tables
  try { db.exec('ALTER TABLE movies ADD COLUMN actors_names TEXT') } catch (e) {}
  try { db.exec('ALTER TABLE movies ADD COLUMN backdrop_path TEXT') } catch (e) {}
  try { db.exec('ALTER TABLE movies ADD COLUMN trailer_url TEXT') } catch (e) {}
  try { db.exec('ALTER TABLE movies ADD COLUMN is_deleted INTEGER DEFAULT 0') } catch (e) {}
  try { db.exec('ALTER TABLE movies ADD COLUMN tag TEXT') } catch (e) {}
  try { db.exec('ALTER TABLE movies ADD COLUMN is_boxset INTEGER DEFAULT 0') } catch (e) {}
  try { db.exec('ALTER TABLE movies ADD COLUMN boxset_parent_id INTEGER') } catch (e) {}
  try { db.exec('ALTER TABLE movies ADD COLUMN view_count INTEGER DEFAULT 0') } catch (e) {}
  try { db.exec('ALTER TABLE movies ADD COLUMN is_watched INTEGER DEFAULT 0') } catch (e) {}
  try { db.exec('ALTER TABLE movies ADD COLUMN in_collection INTEGER DEFAULT 1') } catch (e) {}
  
  // Cleanup duplicates before creating unique index
  try {
    db.exec(`
      DELETE FROM movies 
      WHERE id NOT IN (
        SELECT MAX(id) 
        FROM movies 
        WHERE remote_id IS NOT NULL 
        GROUP BY remote_id
      ) AND remote_id IS NOT NULL
    `)
    db.exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_movies_remote_id ON movies(remote_id)')
  } catch (e) {
    console.error('Migration failed:', e)
  }

  // Final Cleanup: Hide movies that are only on lists but marked as "in collection"
  // (Fix for unintended migration defaults)
  try {
    db.exec(`
      UPDATE movies 
      SET in_collection = 0 
      WHERE in_collection = 1 
      AND id IN (SELECT movie_id FROM list_movies)
      AND remote_id IS NULL
    `)
  } catch (e) {}

  // Custom lists
  db.exec(`
    CREATE TABLE IF NOT EXISTS lists (
      id         INTEGER PRIMARY KEY AUTOINCREMENT,
      name       TEXT    NOT NULL,
      remote_id  INTEGER,
      created_at TEXT    DEFAULT (datetime('now')),
      updated_at TEXT    DEFAULT (datetime('now')),
      synced_at  TEXT
    );

    CREATE TABLE IF NOT EXISTS list_movies (
      list_id    INTEGER NOT NULL,
      movie_id   INTEGER NOT NULL,
      added_at   TEXT    DEFAULT (datetime('now')),
      PRIMARY KEY (list_id, movie_id),
      FOREIGN KEY (list_id)  REFERENCES lists(id)  ON DELETE CASCADE,
      FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
    );
  `)

  try { db.exec('ALTER TABLE lists ADD COLUMN remote_id INTEGER') } catch (e) {}
  try { db.exec('ALTER TABLE lists ADD COLUMN synced_at TEXT') } catch (e) {}
}
