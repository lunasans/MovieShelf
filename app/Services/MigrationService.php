<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
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
use App\Models\UserBackupCode;

class MigrationService
{
    protected $callback;
    protected $connection = 'mysql_v1';

    public function migrate($fresh = false, callable $callback = null)
    {
        $this->callback = $callback;

        // Check if we have an exported SQLite file
        if (file_exists(database_path('v1_dump.sqlite'))) {
            $this->connection = 'sqlite_v1';
            $this->log('Nutze exportierte SQLite-Datei als Datenquelle (v1_dump.sqlite).');
        } else {
            $this->log('Starte Migration: v1.5 (MySQL) -> v2.0 (SQLite)');
        }

        if ($fresh) {
            $this->log('Tabellen werden geleert...');
            $this->truncateTables();
        }

        $this->migrateUsers();
        $this->migrateActors();
        $this->migrateMovies();
        $this->migrateMovieActors();
        $this->migrateUserWatched();
        $this->migrateUserRatings();
        $this->migrateUserWishlist();
        $this->migrateSeasons();
        $this->migrateEpisodes();
        $this->migrateSettings();
        $this->migrateCounter();
        $this->migrateLogs();
        $this->migrateBackupCodes();

        $this->log('Migration erfolgreich abgeschlossen!');

        return true;
    }

    protected function log($message)
    {
        if ($this->callback) {
            call_user_func($this->callback, $message);
        }
    }

    protected function truncateTables()
    {
        DB::statement('PRAGMA foreign_keys = OFF;');
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
        DB::statement('PRAGMA foreign_keys = ON;');
    }

    protected function migrateUsers()
    {
        $this->log('Migriere Benutzer...');
        $oldUsers = DB::connection($this->connection)->table('users')->get();
        
        foreach ($oldUsers as $oldUser) {
            User::updateOrCreate(
                ['id' => $oldUser->id],
                [
                    'name' => explode('@', $oldUser->email)[0],
                    'email' => $oldUser->email,
                    'password' => $oldUser->password,
                    'two_factor_secret' => property_exists($oldUser, 'twofa_secret') ? $oldUser->twofa_secret : null,
                    'two_factor_confirmed_at' => (property_exists($oldUser, 'twofa_enabled') && $oldUser->twofa_enabled) ? ($oldUser->twofa_activated_at ?? $oldUser->created_at) : null,
                    'created_at' => $oldUser->created_at,
                    'updated_at' => $oldUser->updated_at,
                ]
            );
        }
        $this->log('Benutzer migriert: ' . count($oldUsers));
    }

    protected function migrateActors()
    {
        $this->log('Migriere Schauspieler...');
        $oldActors = DB::connection($this->connection)->table('actors')->get();

        foreach ($oldActors as $oldActor) {
            Actor::updateOrCreate(
                ['id' => $oldActor->id],
                [
                    'first_name' => $oldActor->first_name,
                    'last_name' => $oldActor->last_name,
                    'birth_year' => $oldActor->birth_year,
                    'bio' => $oldActor->bio,
                    'created_at' => $oldActor->created_at,
                    'updated_at' => $oldActor->updated_at,
                ]
            );
        }
        $this->log('Schauspieler migriert: ' . count($oldActors));
    }

    protected function migrateMovies()
    {
        $this->log('Migriere Filme (dvds -> movies)...');
        $oldDvds = DB::connection($this->connection)->table('dvds')->get();

        foreach ($oldDvds as $oldDvd) {
            Movie::updateOrCreate(
                ['id' => $oldDvd->id],
                [
                    'title' => $oldDvd->title,
                    'year' => $oldDvd->year,
                    'genre' => $oldDvd->genre,
                    'cover_id' => $oldDvd->cover_id,
                    'collection_type' => $oldDvd->collection_type,
                    'runtime' => $oldDvd->runtime,
                    'rating_age' => $oldDvd->rating_age,
                    'overview' => $oldDvd->overview,
                    'trailer_url' => $oldDvd->trailer_url,
                    'boxset_parent' => $oldDvd->boxset_parent,
                    'user_id' => $oldDvd->user_id,
                    'is_deleted' => $oldDvd->is_deleted || (property_exists($oldDvd, 'deleted') ? $oldDvd->deleted : false),
                    'view_count' => $oldDvd->view_count,
                    'created_at' => $oldDvd->created_at,
                    'updated_at' => $oldDvd->updated_at,
                ]
            );
        }
        $this->log('Filme migriert: ' . count($oldDvds));
    }

    protected function migrateMovieActors()
    {
        $this->log('Migriere Film-Schauspieler Beziehungen...');
        $oldLinks = DB::connection($this->connection)->table('film_actor')->get();

        DB::table('film_actor')->delete();
        foreach ($oldLinks as $link) {
            DB::table('film_actor')->insert([
                'film_id' => $link->film_id,
                'actor_id' => $link->actor_id,
                'role' => $link->role,
                'is_main_role' => $link->is_main_role,
                'sort_order' => $link->sort_order,
                'created_at' => property_exists($link, 'created_at') ? $link->created_at : now(),
            ]);
        }
        $this->log('Beziehungen migriert: ' . count($oldLinks));
    }

    protected function migrateUserWatched()
    {
        $this->log('Migriere "Gesehen" Status...');
        $oldWatched = DB::connection($this->connection)->table('user_watched')->get();

        UserWatched::truncate();
        foreach ($oldWatched as $watched) {
            UserWatched::create([
                'user_id' => $watched->user_id,
                'movie_id' => $watched->film_id,
                'watched_at' => $watched->watched_at,
                'created_at' => $watched->watched_at,
                'updated_at' => $watched->watched_at,
            ]);
        }
        $this->log('Einträge migriert: ' . count($oldWatched));
    }

    protected function migrateUserRatings()
    {
        $this->log('Migriere Bewertungen...');
        $oldRatings = DB::connection($this->connection)->table('user_ratings')->get();

        foreach ($oldRatings as $oldRating) {
            UserRating::updateOrCreate(
                ['id' => $oldRating->id],
                [
                    'movie_id' => $oldRating->film_id,
                    'user_id' => $oldRating->user_id,
                    'rating' => $oldRating->rating,
                    'comment' => property_exists($oldRating, 'comment') ? $oldRating->comment : null,
                    'created_at' => $oldRating->created_at,
                    'updated_at' => $oldRating->updated_at,
                ]
            );
        }
        $this->log('Bewertungen migriert: ' . count($oldRatings));
    }

    protected function migrateUserWishlist()
    {
        $this->log('Migriere Wunschliste...');
        $oldWishlist = DB::connection($this->connection)->table('user_wishlist')->get();

        foreach ($oldWishlist as $oldEntry) {
            UserWishlist::updateOrCreate(
                ['id' => $oldEntry->id],
                [
                    'movie_id' => $oldEntry->film_id,
                    'user_id' => $oldEntry->user_id,
                    'added_at' => $oldEntry->added_at,
                ]
            );
        }
        $this->log('Wunschliste migriert: ' . count($oldWishlist));
    }

    protected function migrateSeasons()
    {
        $this->log('Migriere Staffeln...');
        $oldSeasons = DB::connection($this->connection)->table('seasons')->get();

        foreach ($oldSeasons as $oldSeason) {
            Season::updateOrCreate(
                ['id' => $oldSeason->id],
                [
                    'movie_id' => $oldSeason->series_id,
                    'season_number' => $oldSeason->season_number,
                    'title' => property_exists($oldSeason, 'name') ? $oldSeason->name : ($oldSeason->title ?? "Staffel {$oldSeason->season_number}"),
                    'overview' => property_exists($oldSeason, 'overview') ? $oldSeason->overview : null,
                    'created_at' => $oldSeason->created_at,
                    'updated_at' => $oldSeason->updated_at,
                ]
            );
        }
        $this->log('Staffeln migriert: ' . count($oldSeasons));
    }

    protected function migrateEpisodes()
    {
        $this->log('Migriere Episoden...');
        $oldEpisodes = DB::connection($this->connection)->table('episodes')->get();

        foreach ($oldEpisodes as $oldEpisode) {
            Episode::updateOrCreate(
                ['id' => $oldEpisode->id],
                [
                    'season_id' => $oldEpisode->season_id,
                    'episode_number' => $oldEpisode->episode_number,
                    'title' => $oldEpisode->title,
                    'overview' => $oldEpisode->overview,
                    'created_at' => $oldEpisode->created_at,
                    'updated_at' => $oldEpisode->updated_at,
                ]
            );
        }
        $this->log('Episoden migriert: ' . count($oldEpisodes));
    }

    protected function migrateSettings()
    {
        $this->log('Migriere Einstellungen...');
        $oldSettings = DB::connection($this->connection)->table('settings')->get();

        foreach ($oldSettings as $oldSetting) {
            Setting::updateOrCreate(
                ['key' => $oldSetting->key],
                [
                    'value' => $oldSetting->value,
                    'group' => property_exists($oldSetting, 'group') ? $oldSetting->group : 'general',
                ]
            );
        }
        $this->log('Einstellungen migriert.');
    }

    protected function migrateCounter()
    {
        $this->log('Migriere Counter...');
        $oldCounter = DB::connection($this->connection)->table('counter')->first();

        if ($oldCounter) {
            Counter::updateOrCreate(
                ['id' => $oldCounter->id],
                [
                    'visits' => $oldCounter->visits,
                    'last_visit_date' => $oldCounter->last_visit_date,
                    'daily_visits' => $oldCounter->daily_visits,
                    'created_at' => $oldCounter->created_at,
                    'updated_at' => $oldCounter->updated_at,
                ]
            );
        }
        $this->log('Counter migriert.');
    }

    protected function migrateLogs()
    {
        $this->log('Migriere Logs...');
        $oldLogs = DB::connection($this->connection)->table('activity_log')->get();
        foreach ($oldLogs as $log) {
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
        }

        $oldAudit = DB::connection($this->connection)->table('audit_log')->get();
        foreach ($oldAudit as $log) {
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
        }
        $this->log('Logs migriert.');
    }

    protected function migrateBackupCodes()
    {
        $this->log('Migriere Backup-Codes...');
        $oldCodes = DB::connection($this->connection)->table('user_backup_codes')->get();
        foreach ($oldCodes as $code) {
            UserBackupCode::updateOrCreate(
                ['id' => $code->id],
                [
                    'user_id' => $code->user_id,
                    'code' => $code->code,
                    'used_at' => $code->used_at,
                    'created_at' => $code->created_at,
                ]
            );
        }
        $this->log('Backup-Codes migriert.');
    }
}

    protected function migrateSettings()
    {
        $this->log('Migriere Einstellungen...');
        $oldSettings = DB::connection($this->connection)->table('settings')->get();

        foreach ($oldSettings as $oldSetting) {
            Setting::updateOrCreate(
                ['key' => $oldSetting->key],
                [
                    'value' => $oldSetting->value,
                    'group' => property_exists($oldSetting, 'group') ? $oldSetting->group : 'general',
                ]
            );
        }
        $this->log('Einstellungen migriert.');
    }

    protected function migrateCounter()
    {
        $this->log('Migriere Counter...');
        $oldCounter = DB::connection($this->connection)->table('counter')->first();

        if ($oldCounter) {
            Counter::updateOrCreate(
                ['id' => $oldCounter->id],
                [
                    'visits' => $oldCounter->visits,
                    'last_visit_date' => $oldCounter->last_visit_date,
                    'daily_visits' => $oldCounter->daily_visits,
                    'created_at' => $oldCounter->created_at,
                    'updated_at' => $oldCounter->updated_at,
                ]
            );
        }
        $this->log('Counter migriert.');
    }

    protected function migrateLogs()
    {
        $this->log('Migriere Logs...');
        $oldLogs = DB::connection($this->connection)->table('activity_log')->get();
        foreach ($oldLogs as $log) {
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
        }

        $oldAudit = DB::connection($this->connection)->table('audit_log')->get();
        foreach ($oldAudit as $log) {
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
        }
        $this->log('Logs migriert.');
    }

    protected function migrateBackupCodes()
    {
        $this->log('Migriere Backup-Codes...');
        $oldCodes = DB::connection($this->connection)->table('user_backup_codes')->get();
        foreach ($oldCodes as $code) {
            UserBackupCode::updateOrCreate(
                ['id' => $code->id],
                [
                    'user_id' => $code->user_id,
                    'code' => $code->code,
                    'used_at' => $code->used_at,
                    'created_at' => $code->created_at,
                ]
            );
        }
        $this->log('Backup-Codes migriert.');
    }
}
