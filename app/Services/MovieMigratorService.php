<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Season;
use App\Models\Episode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MovieMigratorService
{
    protected $callback;
    protected $connection;
    protected array $movieFields;

    public function __construct($callback, $connection, array $movieFields)
    {
        $this->callback = $callback;
        $this->connection = $connection;
        $this->movieFields = $movieFields;
    }

    protected function log($message)
    {
        if ($this->callback) {
            call_user_func($this->callback, $message);
        }
    }

    protected function tableExists($tableName)
    {
        return Schema::connection($this->connection)->hasTable($tableName);
    }

    public function migrateMovies($v1CacheDir)
    {
        if (! $this->tableExists('dvds')) {
            $this->log('Tabelle "dvds" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Filme (dvds -> movies)...');
        $total = DB::connection($this->connection)->table('dvds')->count();
        $count = 0;

        $tmdbMapping = $this->loadTmdbMapping();

        DB::connection($this->connection)->table('dvds')->orderBy('id')->chunk(100, function ($oldDvds) use (&$count, $total, $tmdbMapping, $v1CacheDir) {
            foreach ($oldDvds as $oldDvd) {
                try {
                    $tmdbId = $tmdbMapping[$oldDvd->id] ?? null;
                    if (! $tmdbId && str_starts_with((string) $oldDvd->cover_id, 'tmdb_')) {
                        $tmdbId = str_replace('tmdb_', '', $oldDvd->cover_id);
                    }

                    $tmdbData = $this->getTmdbData($oldDvd, $v1CacheDir);
                    $movieData = $this->prepareMovieData($oldDvd, $tmdbId, $tmdbData);

                    $movie = Movie::firstOrNew(['id' => $oldDvd->id]);
                    $movie->id = $oldDvd->id;
                    $movie->timestamps = false;
                    $movie->forceFill($movieData)->save();
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Film ID {$oldDvd->id} ({$oldDvd->title}): ".$e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Filme migriert.");
        });
    }

    public function migrateMovieActors()
    {
        if (! $this->tableExists('film_actor')) {
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
                    $this->log("Fehler beim Migrieren von Beziehung Film {$link->film_id} <-> Actor {$link->actor_id}: ".$e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Beziehungen migriert.");
        });
    }

    public function migrateSeasons()
    {
        if (! $this->tableExists('seasons')) {
            $this->log('Tabelle "seasons" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Staffeln...');
        $total = DB::connection($this->connection)->table('seasons')->count();
        $count = 0;

        DB::connection($this->connection)->table('seasons')->orderBy('id')->chunk(100, function ($oldSeasons) use (&$count, $total) {
            foreach ($oldSeasons as $oldSeason) {
                try {
                    $seasonData = $this->prepareSeasonData($oldSeason);
                    $season = Season::firstOrNew(['id' => $oldSeason->id]);
                    $season->timestamps = false;
                    $season->forceFill($seasonData)->save();
                } catch (\Exception $e) {
                    $this->log("Fehler beim Migrieren von Staffel ID {$oldSeason->id}: ".$e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Staffeln migriert.");
        });
    }

    public function migrateEpisodes()
    {
        if (! $this->tableExists('episodes')) {
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
                    $this->log("Fehler beim Migrieren von Episode ID {$oldEpisode->id}: ".$e->getMessage());
                }
                $count++;
            }
            $this->log("Fortschritt: {$count}/{$total} Episoden migriert.");
        });
    }

    protected function loadTmdbMapping(): array
    {
        $tmdbMapping = [];
        if ($this->tableExists('activity_log')) {
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
            $this->log(count($tmdbMapping).' TMDB-Mappings geladen.');
        }
        return $tmdbMapping;
    }

    protected function getTmdbData($oldDvd, ?string $v1CacheDir): ?array
    {
        if (! $v1CacheDir || ! is_dir($v1CacheDir)) {
            return null;
        }

        $cacheKey = md5($oldDvd->title.($oldDvd->year ?? ''));
        $cacheFile = $v1CacheDir.'/'.$cacheKey.'.json';

        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        return null;
    }

    protected function prepareMovieData($oldDvd, $tmdbId, ?array $tmdbData): array
    {
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
            'is_deleted' => $this->resolveIsDeleted($oldDvd),
            'view_count' => $oldDvd->view_count,
            'created_at' => $oldDvd->created_at,
            'updated_at' => $oldDvd->updated_at,
            'tmdb_id' => $tmdbId,
            'tmdb_type' => 'movie',
            'tmdb_json' => $tmdbData,
        ];

        $movieData = ['title' => $oldDvd->title, 'user_id' => $oldDvd->user_id];

        if (! empty($this->movieFields)) {
            foreach ($this->movieFields as $field) {
                if (array_key_exists($field, $selectableFields)) {
                    $movieData[$field] = $selectableFields[$field];
                }
            }
        } else {
            $movieData = array_merge($movieData, $selectableFields);
        }

        return $movieData;
    }

    protected function prepareSeasonData($oldSeason): array
    {
        $title = property_exists($oldSeason, 'name') ? $oldSeason->name : ($oldSeason->title ?? "Staffel {$oldSeason->season_number}");

        return [
            'movie_id' => $oldSeason->series_id,
            'season_number' => $oldSeason->season_number,
            'title' => $title,
            'overview' => property_exists($oldSeason, 'overview') ? $oldSeason->overview : null,
            'created_at' => $oldSeason->created_at,
            'updated_at' => $oldSeason->updated_at,
        ];
    }

    protected function resolveIsDeleted($oldDvd): bool
    {
        if (isset($oldDvd->is_deleted) && $oldDvd->is_deleted) {
            return true;
        }

        return property_exists($oldDvd, 'deleted') && $oldDvd->deleted;
    }
}
