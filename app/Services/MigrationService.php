<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Movie;
use App\Models\Actor;
use App\Models\Setting;
use App\Models\Counter;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Season;
use App\Models\Episode;
use App\Models\UserRating;
use App\Models\UserWishlist;
use App\Models\UserWatched;
use App\Models\UserBackupCode;

class MigrationService
{
    protected $callback;
    protected $connection = 'mysql_v1';
    protected array $modules = [];
    protected array $movieFields = [];
    protected $v1Path = null;

    public function migrate($fresh = false, array $modules = [], array $movieFields = [], $v1Path = null, callable $callback = null)
    {
        $this->callback = $callback;
        $this->modules = $modules;
        $this->movieFields = $movieFields;
        $this->v1Path = $v1Path;

        $defaultModules = ['users', 'actors', 'movies', 'movie_actors', 'watched', 'ratings', 'wishlist', 'seasons', 'episodes', 'settings', 'counter', 'logs', 'backup_codes'];
        $activeModules = !empty($this->modules) ? $this->modules : $defaultModules;

        // Check if we have an exported SQLite file
        if (file_exists(database_path('v1_dump.sqlite'))) {
            $this->connection = 'sqlite_v1';
            $this->log('Nutze exportierte SQLite-Datei als Datenquelle (v1_dump.sqlite).');
        } else {
            $this->log('Starte Migration: v1.5 (MySQL) -> v2.0 (SQLite)');
        }

        // Disable foreign keys for the duration of the migration
        DB::statement('PRAGMA foreign_keys = OFF;');

        try {
            if ($fresh) {
                $this->log('Tabellen werden geleert...');
                $this->truncateTables();
            }

            if (in_array('users', $activeModules)) $this->migrateUsers();
            if (in_array('actors', $activeModules)) $this->migrateActors();
            if (in_array('movies', $activeModules)) $this->migrateMovies();
            if (in_array('movie_actors', $activeModules)) $this->migrateMovieActors();
            if (in_array('watched', $activeModules)) $this->migrateUserWatched();
            if (in_array('ratings', $activeModules)) $this->migrateUserRatings();
            if (in_array('wishlist', $activeModules)) $this->migrateUserWishlist();
            if (in_array('seasons', $activeModules)) $this->migrateSeasons();
            if (in_array('episodes', $activeModules)) $this->migrateEpisodes();
            if (in_array('settings', $activeModules)) $this->migrateSettings();
            if (in_array('counter', $activeModules)) $this->migrateCounter();
            if (in_array('logs', $activeModules)) $this->migrateLogs();
            if (in_array('backup_codes', $activeModules)) $this->migrateBackupCodes();

            $this->log('Migration erfolgreich abgeschlossen!');
        } finally {
            // Re-enable foreign keys
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        return true;
    }

    protected function tableExists($tableName)
    {
        return Schema::connection($this->connection)->hasTable($tableName);
    }

    protected function log($message)
    {
        if ($this->callback) {
            call_user_func($this->callback, $message);
        }
    }

    protected function truncateTables()
    {
        User::truncate();
        Movie::truncate();
        Actor::truncate();
        Setting::truncate();
        Counter::truncate();
        ActivityLog::truncate();
        AuditLog::truncate();
        Season::truncate();
        Episode::truncate();
        UserRating::truncate();
        UserWishlist::truncate();
        UserWatched::truncate();
        UserBackupCode::truncate();
        DB::table('film_actor')->truncate();
        DB::table('movie_user_watched')->truncate();
    }

    protected function migrateUsers()
    {
        if (!$this->tableExists('users')) {
            $this->log('Tabelle "users" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Benutzer...');
        $total = DB::connection($this->connection)->table('users')->count();
        $count = 0;

        DB::connection($this->connection)->table('users')->orderBy('id')->chunk(100, function ($oldUsers) use (&$count, $total) {
            foreach ($oldUsers as $oldUser) {
                try {
                    $user = User::firstOrNew(['id' => $oldUser->id]);
                    $user->timestamps = false;
                    $user->forceFill([
                        'name' => explode('@', $oldUser->email)[0],
                        'email' => $oldUser->email,
                        'password' => $oldUser->password,
                        'two_factor_secret' => property_exists($oldUser, 'twofa_secret') ? $oldUser->twofa_secret : null,
                        'two_factor_confirmed_at' => (property_exists($oldUser, 'twofa_enabled') && $oldUser->twofa_enabled) ? ($oldUser->twofa_activated_at ?? $oldUser->created_at) : null,
                        'created_at' => $oldUser->created_at,
                        'updated_at' => $oldUser->updated_at,
                    ])->save();
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Benutzer ID {$oldUser->id}: " . $e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Benutzer migriert.");
        });
    }

    protected function migrateActors()
    {
        if (!$this->tableExists('actors')) {
            $this->log('Tabelle "actors" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Schauspieler...');
        $total = DB::connection($this->connection)->table('actors')->count();
        $count = 0;

        DB::connection($this->connection)->table('actors')->orderBy('id')->chunk(100, function ($oldActors) use (&$count, $total) {
            foreach ($oldActors as $oldActor) {
                try {
                    $actor = Actor::firstOrNew(['id' => $oldActor->id]);
                    $actor->timestamps = false;
                    $actor->forceFill([
                        'first_name' => $oldActor->first_name,
                        'last_name' => $oldActor->last_name,
                        'birth_year' => $oldActor->birth_year,
                        'bio' => $oldActor->bio,
                        'created_at' => $oldActor->created_at,
                        'updated_at' => $oldActor->updated_at,
                    ])->save();
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Schauspieler ID {$oldActor->id}: " . $e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Schauspieler migriert.");
        });
    }

    protected function migrateMovies()
    {
        if (!$this->tableExists('dvds')) {
            $this->log('Tabelle "dvds" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Filme (dvds -> movies)...');
        $total = DB::connection($this->connection)->table('dvds')->count();
        $count = 0;

        // Pre-load TMDB mappings from activity_log
        $tmdbMapping = [];
        if ($this->tableExists('activity_log')) {
            $this->log('Lade TMDB-Mappings aus activity_log...');
            $logs = DB::connection($this->connection)
                ->table('activity_log')
                ->where('action', 'FILM_UPDATE_TMDB')
                ->get();
            
            foreach ($logs as $log) {
                $details = json_decode($log->details, true);
                if (isset($details['film_id']) && isset($details['tmdb_id'])) {
                    $tmdbMapping[$details['film_id']] = $details['tmdb_id'];
                }
            }
            $this->log(count($tmdbMapping) . ' TMDB-Mappings geladen.');
        }

        // Cache directory for TMDB JSON
        $v1CacheDir = $this->v1Path ? rtrim($this->v1Path, '/') . '/cache/tmdb' : null;

        if (!$v1CacheDir || !is_dir($v1CacheDir)) {
            // Try robust defaults
            $v1CacheDir = base_path('../dvdprofiler.liste/cache/tmdb');
        }
        
        if (!is_dir($v1CacheDir)) {
            $v1CacheDir = dirname(base_path()) . '/dvdprofiler.liste/cache/tmdb';
        }
        
        if (is_dir($v1CacheDir)) {
            $this->log('TMDB-Cache gefunden: ' . realpath($v1CacheDir));
        } else {
            $this->log('WARNUNG: TMDB-Cache Verzeichnis nicht gefunden! (' . ($v1CacheDir ?: 'kein Pfad angegeben') . ')');
        }

        DB::connection($this->connection)->table('dvds')->orderBy('id')->chunk(100, function ($oldDvds) use (&$count, $total, $tmdbMapping, $v1CacheDir) {
            foreach ($oldDvds as $oldDvd) {
                try {
                    $tmdbId = $tmdbMapping[$oldDvd->id] ?? null;
                    
                    // Try to extract from cover_id if not found in mapping
                    if (!$tmdbId && str_starts_with($oldDvd->cover_id, 'tmdb_')) {
                        $tmdbId = str_replace('tmdb_', '', $oldDvd->cover_id);
                    }

                    $tmdbJson = null;
                    if (is_dir($v1CacheDir)) {
                        $cacheKey = md5($oldDvd->title . ($oldDvd->year ?? ''));
                        $cacheFile = $v1CacheDir . '/' . $cacheKey . '.json';
                        if (file_exists($cacheFile)) {
                            $tmdbJson = file_get_contents($cacheFile);
                            $this->log("Cache-Hit: {$oldDvd->title} ({$cacheKey})");
                        } else {
                            // $this->log("Cache-Miss: {$oldDvd->title} ({$cacheKey})");
                        }
                    }

                    $tmdbData = $tmdbJson ? json_decode($tmdbJson, true) : null;

                    $movieData = [
                        'title' => $oldDvd->title,
                        'user_id' => $oldDvd->user_id,
                    ];

                    $rating = property_exists($oldDvd, 'rating') ? $oldDvd->rating : null;
                    if (($rating === null || $rating == 0) && $tmdbData) {
                        $rating = $tmdbData['vote_average'] ?? $tmdbData['tmdb_rating'] ?? null;
                    }

                    $selectableFields = [
                        'year' => $oldDvd->year,
                        'genre' => $oldDvd->genre,
                        'rating' => $rating,
                        'cover_id' => $oldDvd->cover_id,
                        'backdrop_id' => property_exists($oldDvd, 'backdrop_id') ? $oldDvd->backdrop_id : null,
                        'collection_type' => $oldDvd->collection_type,
                        'runtime' => $oldDvd->runtime,
                        'rating_age' => $oldDvd->rating_age,
                        'overview' => $oldDvd->overview,
                        'director' => property_exists($oldDvd, 'director') ? $oldDvd->director : null,
                        'trailer_url' => $oldDvd->trailer_url,
                        'boxset_parent' => $oldDvd->boxset_parent,
                        'is_deleted' => (isset($oldDvd->is_deleted) && $oldDvd->is_deleted) || (property_exists($oldDvd, 'deleted') ? $oldDvd->deleted : false),
                        'view_count' => $oldDvd->view_count,
                        'created_at' => $oldDvd->created_at,
                        'updated_at' => $oldDvd->updated_at,
                        'tmdb_id' => $tmdbId,
                        'tmdb_type' => 'movie',
                        'tmdb_json' => $tmdbData,
                    ];

                    // If fields are specified, filter the selectable fields
                    if (!empty($this->movieFields)) {
                        foreach ($this->movieFields as $field) {
                            if (array_key_exists($field, $selectableFields)) {
                                $movieData[$field] = $selectableFields[$field];
                            }
                        }
                    } else {
                        // Default: all fields
                        $movieData = array_merge($movieData, $selectableFields);
                    }

                    $movie = Movie::firstOrNew(['id' => $oldDvd->id]);
                    $movie->id = $oldDvd->id; // Explicitly set ID for new records
                    $movie->timestamps = false; // Disable auto-timestamps
                    
                    if ($rating > 0) {
                        $this->log("Speichere Bewertung für {$oldDvd->title}: {$rating}");
                    }
                    
                    $movie->forceFill($movieData)->save();
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Film ID {$oldDvd->id} ({$oldDvd->title}): " . $e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Filme migriert.");
        });
    }

    protected function migrateMovieActors()
    {
        if (!$this->tableExists('film_actor')) {
            $this->log('Tabelle "film_actor" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Film-Schauspieler Beziehungen...');
        $total = DB::connection($this->connection)->table('film_actor')->count();
        $count = 0;

        DB::table('film_actor')->delete();
        DB::connection($this->connection)->table('film_actor')->orderBy('film_id')->chunk(500, function ($oldLinks) use (&$count, $total) {
            foreach ($oldLinks as $link) {
                try {
                    DB::table('film_actor')->insert([
                        'film_id' => $link->film_id,
                        'actor_id' => $link->actor_id,
                        'role' => $link->role,
                        'is_main_role' => $link->is_main_role,
                        'sort_order' => $link->sort_order,
                        'created_at' => property_exists($link, 'created_at') ? $link->created_at : now(),
                    ]);
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Beziehung Film {$link->film_id} <-> Actor {$link->actor_id}: " . $e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Beziehungen migriert.");
        });
    }

    protected function migrateUserWatched()
    {
        if (!$this->tableExists('user_watched')) {
            $this->log('Tabelle "user_watched" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere "Gesehen" Status...');
        $total = DB::connection($this->connection)->table('user_watched')->count();
        $count = 0;

        UserWatched::truncate();
        DB::connection($this->connection)->table('user_watched')->orderBy('film_id')->chunk(500, function ($oldWatched) use (&$count, $total) {
            foreach ($oldWatched as $watched) {
                try {
                    UserWatched::create([
                        'user_id' => $watched->user_id,
                        'movie_id' => $watched->film_id,
                        'watched_at' => $watched->watched_at,
                        'created_at' => $watched->watched_at,
                        'updated_at' => $watched->watched_at,
                    ]);
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Watched-Status (User {$watched->user_id}, Film {$watched->film_id}): " . $e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Gesehen-Status migriert.");
        });
    }

    protected function migrateUserRatings()
    {
        if (!$this->tableExists('user_ratings')) {
            $this->log('Tabelle "user_ratings" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Bewertungen...');
        $total = DB::connection($this->connection)->table('user_ratings')->count();
        $count = 0;

        DB::connection($this->connection)->table('user_ratings')->orderBy('id')->chunk(100, function ($oldRatings) use (&$count, $total) {
            foreach ($oldRatings as $oldRating) {
                try {
                    $rating = UserRating::firstOrNew(['id' => $oldRating->id]);
                    $rating->timestamps = false;
                    $rating->forceFill([
                        'movie_id' => $oldRating->film_id,
                        'user_id' => $oldRating->user_id,
                        'rating' => $oldRating->rating,
                        'comment' => property_exists($oldRating, 'comment') ? $oldRating->comment : null,
                        'created_at' => $oldRating->created_at,
                        'updated_at' => $oldRating->updated_at,
                    ])->save();
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Bewertung ID {$oldRating->id}: " . $e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Bewertungen migriert.");
        });
    }

    protected function migrateUserWishlist()
    {
        if (!$this->tableExists('user_wishlist')) {
            $this->log('Tabelle "user_wishlist" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Wunschliste...');
        $total = DB::connection($this->connection)->table('user_wishlist')->count();
        $count = 0;

        DB::connection($this->connection)->table('user_wishlist')->orderBy('id')->chunk(200, function ($oldWishlist) use (&$count, $total) {
            foreach ($oldWishlist as $oldEntry) {
                try {
                    UserWishlist::updateOrCreate(
                        ['id' => $oldEntry->id],
                        [
                            'movie_id' => $oldEntry->film_id,
                            'user_id' => $oldEntry->user_id,
                            'added_at' => $oldEntry->added_at,
                        ]
                    );
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Wunschliste ID {$oldEntry->id}: " . $e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Wunschlisten-Einträge migriert.");
        });
    }

    protected function migrateSeasons()
    {
        if (!$this->tableExists('seasons')) {
            $this->log('Tabelle "seasons" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Staffeln...');
        $total = DB::connection($this->connection)->table('seasons')->count();
        $count = 0;

        DB::connection($this->connection)->table('seasons')->orderBy('id')->chunk(100, function ($oldSeasons) use (&$count, $total) {
            foreach ($oldSeasons as $oldSeason) {
                try {
                    $season = Season::firstOrNew(['id' => $oldSeason->id]);
                    $season->timestamps = false;
                    $season->forceFill([
                        'movie_id' => $oldSeason->series_id,
                        'season_number' => $oldSeason->season_number,
                        'title' => property_exists($oldSeason, 'name') ? $oldSeason->name : ($oldSeason->title ?? "Staffel {$oldSeason->season_number}"),
                        'overview' => property_exists($oldSeason, 'overview') ? $oldSeason->overview : null,
                        'created_at' => $oldSeason->created_at,
                        'updated_at' => $oldSeason->updated_at,
                    ])->save();
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Staffel ID {$oldSeason->id}: " . $e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Staffeln migriert.");
        });
    }

    protected function migrateEpisodes()
    {
        if (!$this->tableExists('episodes')) {
            $this->log('Tabelle "episodes" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Episoden...');
        $total = DB::connection($this->connection)->table('episodes')->count();
        $count = 0;

        DB::connection($this->connection)->table('episodes')->orderBy('id')->chunk(200, function ($oldEpisodes) use (&$count, $total) {
            foreach ($oldEpisodes as $oldEpisode) {
                try {
                    $episode = Episode::firstOrNew(['id' => $oldEpisode->id]);
                    $episode->timestamps = false;
                    $episode->forceFill([
                        'season_id' => $oldEpisode->season_id,
                        'episode_number' => $oldEpisode->episode_number,
                        'title' => $oldEpisode->title,
                        'overview' => $oldEpisode->overview,
                        'created_at' => $oldEpisode->created_at,
                        'updated_at' => $oldEpisode->updated_at,
                    ])->save();
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Episode ID {$oldEpisode->id}: " . $e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Episoden migriert.");
        });
    }

    protected function migrateSettings()
    {
        if (!$this->tableExists('settings')) {
            $this->log('Tabelle "settings" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Einstellungen...');
        $oldSettings = DB::connection($this->connection)->table('settings')->get();

        foreach ($oldSettings as $oldSetting) {
            try {
                Setting::updateOrCreate(
                    ['key' => $oldSetting->key],
                    [
                        'value' => $oldSetting->value,
                        'group' => property_exists($oldSetting, 'group') ? $oldSetting->group : 'general',
                    ]
                );
            } catch (\Exception $e) {
                $this->log("Fehler beim Migrieren von Einstellung {$oldSetting->key}: " . $e->getMessage());
            }
        }
        $this->log('Einstellungen migriert.');
    }

    protected function migrateCounter()
    {
        if (!$this->tableExists('counter')) {
            $this->log('Tabelle "counter" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Counter...');
        try {
            $oldCounter = DB::connection($this->connection)->table('counter')->first();

            if ($oldCounter) {
                // Migrate Total Visits
                Counter::updateOrCreate(
                    ['page' => 'all'],
                    [
                        'visits' => $oldCounter->visits,
                        'last_visit' => property_exists($oldCounter, 'last_visit_date') ? $oldCounter->last_visit_date : ($oldCounter->last_visit ?? null),
                        'created_at' => $oldCounter->created_at,
                        'updated_at' => $oldCounter->updated_at,
                    ]
                );

                // Migrate Daily Visits (if exists and has a date)
                if (property_exists($oldCounter, 'daily_visits') && property_exists($oldCounter, 'last_visit_date')) {
                    $date = $oldCounter->last_visit_date;
                    if ($date) {
                        Counter::updateOrCreate(
                            ['page' => "daily:$date"],
                            [
                                'visits' => $oldCounter->daily_visits,
                                'last_visit' => $date . ' 23:59:59', // Approximation for historical daily
                                'created_at' => $oldCounter->updated_at, // Use update date as base
                                'updated_at' => $oldCounter->updated_at,
                            ]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log("Fehler beim Migrieren des Counters: " . $e->getMessage());
        }
        $this->log('Counter migriert.');
    }

    protected function migrateLogs()
    {
        if (!$this->tableExists('activity_log')) {
            $this->log('Tabelle "activity_log" nicht gefunden, Überspringe...');
        } else {
            $this->log('Migriere Activity Logs...');
            $total = DB::connection($this->connection)->table('activity_log')->count();
            $count = 0;
            DB::connection($this->connection)->table('activity_log')->orderBy('id')->chunk(500, function ($oldLogs) use (&$count, $total) {
                foreach ($oldLogs as $log) {
                    try {
                        ActivityLog::updateOrCreate(
                            ['id' => $log->id],
                            [
                                'user_id' => $log->user_id,
                                'action' => $log->action,
                                'details' => $log->details,
                                'ip_address' => $log->ip_address,
                                'user_agent' => $log->user_agent,
                                'created_at' => $log->created_at,
                            ]
                        );
                    } catch (\Exception $e) {
                        // Fail silently for logs to not bloat output
                    }
                    $count++;
                }
                if ($count % 1000 == 0) $this->log("Fortschritt: {$count}/{$total} Activity-Logs migriert.");
            });
        }

        if (!$this->tableExists('audit_log')) {
            $this->log('Tabelle "audit_log" nicht gefunden, Überspringe...');
        } else {
            $this->log('Migriere Audit Logs...');
            $totalAudit = DB::connection($this->connection)->table('audit_log')->count();
            $countAudit = 0;
            DB::connection($this->connection)->table('audit_log')->orderBy('id')->chunk(500, function ($oldAudit) use (&$countAudit, $totalAudit) {
                foreach ($oldAudit as $log) {
                    try {
                        AuditLog::updateOrCreate(
                            ['id' => $log->id],
                            [
                                'user_id' => $log->user_id,
                                'action' => $log->action,
                                'ip_address' => $log->ip_address,
                                'user_agent' => $log->user_agent,
                                'created_at' => $log->created_at,
                            ]
                        );
                    } catch (\Exception $e) {
                        // Fail silently
                    }
                    $countAudit++;
                }
                if ($countAudit % 1000 == 0) $this->log("Fortschritt: {$countAudit}/{$totalAudit} Audit-Logs migriert.");
            });
        }
        $this->log('Logs migriert.');
    }

    protected function migrateBackupCodes()
    {
        if (!$this->tableExists('user_backup_codes')) {
            $this->log('Tabelle "user_backup_codes" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Backup-Codes...');
        $total = DB::connection($this->connection)->table('user_backup_codes')->count();
        $count = 0;
        DB::connection($this->connection)->table('user_backup_codes')->orderBy('id')->chunk(500, function ($oldCodes) use (&$count, $total) {
            foreach ($oldCodes as $code) {
                try {
                    UserBackupCode::updateOrCreate(
                        ['id' => $code->id],
                        [
                            'user_id' => $code->user_id,
                            'code' => $code->code,
                            'used' => (bool)$code->used_at,
                            'used_at' => $code->used_at,
                            'created_at' => $code->created_at,
                        ]
                    );
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Backup-Code ID {$code->id}: " . $e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Backup-Codes migriert.");
        });
    }
}
