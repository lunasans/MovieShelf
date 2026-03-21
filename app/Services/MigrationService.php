<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Actor;
use App\Models\AuditLog;
use App\Models\Counter;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Season;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserBackupCode;
use App\Models\UserRating;
use App\Models\UserWatched;
use App\Models\UserWishlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationService
{
    protected $callback;

    protected $connection = 'mysql_v1';

    protected array $modules = [];

    protected array $movieFields = [];

    protected $v1Path = null;

    public function migrate($fresh = false, array $modules = [], array $movieFields = [], $v1Path = null, ?callable $callback = null)
    {
        $this->callback = $callback;
        $this->modules = $modules;
        $this->movieFields = $movieFields;
        $this->v1Path = $v1Path;

        $defaultModules = ['users', 'actors', 'movies', 'movie_actors', 'watched', 'ratings', 'wishlist', 'seasons', 'episodes', 'settings', 'counter', 'logs', 'backup_codes'];
        $activeModules = ! empty($this->modules) ? $this->modules : $defaultModules;

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

            $movieMigrator = new MovieMigratorService($this->callback, $this->connection, $this->movieFields);
            $systemMigrator = new SystemMigratorService($this->callback, $this->connection);
            $v1CacheDir = $this->resolveTmdbCacheDir();

            $moduleActions = [
                'users' => fn () => $this->migrateUsers(),
                'actors' => fn () => $this->migrateActors(),
                'movies' => fn () => $movieMigrator->migrateMovies($v1CacheDir),
                'movie_actors' => fn () => $movieMigrator->migrateMovieActors(),
                'watched' => fn () => $this->migrateUserWatched(),
                'ratings' => fn () => $this->migrateUserRatings(),
                'wishlist' => fn () => $this->migrateUserWishlist(),
                'seasons' => fn () => $movieMigrator->migrateSeasons(),
                'episodes' => fn () => $movieMigrator->migrateEpisodes(),
                'settings' => fn () => $systemMigrator->migrateSettings(),
                'counter' => fn () => $systemMigrator->migrateCounter(),
                'logs' => fn () => $systemMigrator->migrateLogs(),
                'backup_codes' => fn () => $this->migrateBackupCodes(),
            ];

            foreach ($activeModules as $module) {
                if (isset($moduleActions[$module])) {
                    $moduleActions[$module]();
                }
            }

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
        if (! $this->tableExists('users')) {
            $this->log('Tabelle "users" nicht gefunden, Überspringe...');

            return;
        }

        $this->log('Migriere Benutzer...');
        $total = DB::connection($this->connection)->table('users')->count();
        $count = 0;

        DB::connection($this->connection)->table('users')->orderBy('id')->chunk(100, function ($oldUsers) use (&$count, $total) {
            foreach ($oldUsers as $oldUser) {
                try {
                    $userData = $this->prepareUserData($oldUser);
                    $user = User::firstOrNew(['id' => $oldUser->id]);
                    $user->timestamps = false;
                    $user->forceFill($userData)->save();
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Benutzer ID {$oldUser->id}: ".$e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Benutzer migriert.");
        });
    }

    protected function prepareUserData($oldUser): array
    {
        $twoFactorConfirmedAt = null;
        $hasTwoFactor = property_exists($oldUser, 'twofa_enabled') && $oldUser->twofa_enabled;

        if ($hasTwoFactor) {
            $twoFactorConfirmedAt = $oldUser->twofa_activated_at ?? $oldUser->created_at;
        }

        return [
            'name' => explode('@', $oldUser->email)[0],
            'email' => $oldUser->email,
            'password' => $oldUser->password,
            'two_factor_secret' => property_exists($oldUser, 'twofa_secret') ? $oldUser->twofa_secret : null,
            'two_factor_confirmed_at' => $twoFactorConfirmedAt,
            'created_at' => $oldUser->created_at,
            'updated_at' => $oldUser->updated_at,
        ];
    }

    protected function migrateActors()
    {
        if (! $this->tableExists('actors')) {
            $this->log('Tabelle "actors" nicht gefunden, Überspringe...');

            return;
        }

        $this->log('Migriere Schauspieler...');
        $total = DB::connection($this->connection)->table('actors')->count();
        $count = 0;

        DB::connection($this->connection)->table('actors')->orderBy('id')->chunk(100, function ($oldActors) use (&$count, $total) {
            foreach ($oldActors as $oldActor) {
                try {
                    $actorData = $this->prepareActorData($oldActor);
                    $actor = Actor::firstOrNew(['id' => $oldActor->id]);
                    $actor->timestamps = false;
                    $actor->forceFill($actorData)->save();
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Schauspieler ID {$oldActor->id}: ".$e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Schauspieler migriert.");
        });
    }

    protected function prepareActorData($oldActor): array
    {
        return [
            'first_name' => $oldActor->first_name,
            'last_name' => $oldActor->last_name,
            'birth_year' => $oldActor->birth_year,
            'bio' => $oldActor->bio,
            'created_at' => $oldActor->created_at,
            'updated_at' => $oldActor->updated_at,
        ];
    }

    protected function migrateUserWatched()
    {
        if (! $this->tableExists('user_watched')) {
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
                    $this->log("Fehler beim Migrieren von Watched-Status (User {$watched->user_id}, Film {$watched->film_id}): ".$e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Gesehen-Status migriert.");
        });
    }

    protected function migrateUserRatings()
    {
        if (! $this->tableExists('user_ratings')) {
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
                    $this->log("Fehler beim Migrieren von Bewertung ID {$oldRating->id}: ".$e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Bewertungen migriert.");
        });
    }

    protected function migrateUserWishlist()
    {
        if (! $this->tableExists('user_wishlist')) {
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
                    $this->log("Fehler beim Migrieren von Wunschliste ID {$oldEntry->id}: ".$e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Wunschlisten-Einträge migriert.");
        });
    }

    protected function migrateBackupCodes()
    {
        if (! $this->tableExists('user_backup_codes')) {
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
                            'used' => (bool) $code->used_at,
                            'used_at' => $code->used_at,
                            'created_at' => $code->created_at,
                        ]
                    );
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Backup-Code ID {$code->id}: ".$e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Backup-Codes migriert.");
        });
    }

    protected function resolveTmdbCacheDir(): ?string
    {
        $v1CacheDir = $this->v1Path ? rtrim($this->v1Path, '/').'/cache/tmdb' : null;

        if (! $v1CacheDir || ! is_dir($v1CacheDir)) {
            $v1CacheDir = database_path('v1_cache');
        }

        if (! is_dir($v1CacheDir)) {
            $v1CacheDir = base_path('../dvdprofiler.liste/cache/tmdb');
        }

        if (! is_dir($v1CacheDir)) {
            $v1CacheDir = dirname(base_path()).'/dvdprofiler.liste/cache/tmdb';
        }

        if (is_dir($v1CacheDir)) {
            $this->log('TMDB-Cache gefunden: '.realpath($v1CacheDir));

            return $v1CacheDir;
        }

        $this->log('WARNUNG: TMDB-Cache Verzeichnis nicht gefunden! ('.($v1CacheDir ?: 'kein Pfad angegeben').')');

        return null;
    }
}
