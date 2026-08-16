<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Actor;
use App\Models\ExternalMovie;
use App\Models\Movie;
use App\Models\Season;
use App\Models\Episode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Exceptions\TmdbImportException;

class TmdbImportService
{
    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    /* Import a movie from TMDb. */
    public function importMovie(int $tmdbId, bool $inCollection = true)
    {
        $details = $this->tmdb->getMovieDetails($tmdbId);
        if (isset($details['error'])) {
            throw new TmdbImportException($details['error']);
        }

        try {
            return DB::transaction(function () use ($details, $tmdbId, $inCollection) {
                $movie = Movie::where('tmdb_id', $tmdbId)
                    ->where(function($q) {
                        $q->where('tmdb_type', 'movie')->orWhereNull('tmdb_type');
                    })
                    ->first();

                $data = [
                    'title' => $details['title'],
                    'year' => isset($details['release_date']) ? (int) substr($details['release_date'], 0, 4) : null,
                    'rating' => $details['vote_average'] ?? null,
                    'genre' => implode(', ', array_column($details['genres'], 'name')),
                    'runtime' => $details['runtime'] ?? null,
                    'overview' => $details['overview'] ?? null,
                    'director' => $this->extractDirector($details),
                    'trailer_url' => $this->extractTrailer($details),
                    // Kanonisches Schema: Film | Serie (das Medium steht im tag-Feld)
                    'collection_type' => 'Film',
                    'rating_age' => $this->extractRating($details),
                    'tmdb_id' => $tmdbId,
                    'tmdb_type' => 'movie',
                    'tmdb_json' => $details,
                    'in_collection' => $inCollection,
                ];

                if ($movie) {
                    $movie->update($data);
                } else {
                    // Kein 'Blu-ray' mehr als collection_type: das Feld traegt seit
                    // der Vereinheitlichung nur noch Film|Serie, das Medium steht
                    // im tag-Feld. Sonst legt jeder Import wieder Werte an, die
                    // normalize_collection_type gerade weggeraeumt hat.
                    $data['user_id'] = auth()->id();
                    $movie = Movie::create($data);
                }

                $this->handleImages($movie, $details);
                $this->handleActors($movie, $details);

                return $movie;
            });
        } catch (\Exception $e) {
            Log::error('TmdbMovieImport Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Import a TV show from TMDb.
     */
    public function importTv(int $tmdbId, array $requestedSeasons = [], bool $inCollection = true)
    {
        $details = $this->tmdb->getTvDetails($tmdbId);
        if (isset($details['error'])) {
            throw new TmdbImportException($details['error']);
        }

        try {
            return DB::transaction(function () use ($details, $tmdbId, $requestedSeasons, $inCollection) {
                $movie = Movie::where('tmdb_id', $tmdbId)->where('tmdb_type', 'tv')->first();

                $data = [
                    'title' => $details['name'],
                    'year' => isset($details['first_air_date']) ? (int) substr($details['first_air_date'], 0, 4) : null,
                    'rating' => $details['vote_average'] ?? null,
                    'genre' => implode(', ', array_column($details['genres'], 'name')),
                    'runtime' => $details['episode_run_time'][0] ?? null,
                    'overview' => $details['overview'] ?? null,
                    'director' => $this->extractCreator($details),
                    'trailer_url' => $this->extractTrailer($details),
                    'rating_age' => $this->extractRating($details),
                    'tmdb_id' => $tmdbId,
                    'tmdb_type' => 'tv',
                    'tmdb_json' => $details,
                    'in_collection' => $inCollection,
                ];

                if ($movie) {
                    $movie->update($data);
                } else {
                    $data['collection_type'] = 'Serie';
                    $data['user_id'] = auth()->id();
                    $movie = Movie::create($data);
                }

                $this->handleImages($movie, $details);
                $this->handleActors($movie, $details);
                $this->importSeasons($movie, $details, $requestedSeasons);

                return $movie;
            });
        } catch (\Exception $e) {
            Log::error('TmdbTvImport Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Staffeln für eine BESTEHENDE Serie nachladen. Importiert nur die
     * angefragten Staffelnummern und überspringt bereits vorhandene —
     * man besitzt ja nicht von jeder Serie alle Staffeln.
     *
     * @return int Anzahl neu importierter Staffeln
     */
    public function importSeasonsForExisting(Movie $movie, array $requestedSeasons): int
    {
        if (! $movie->tmdb_id) {
            throw new TmdbImportException('Keine TMDb ID für diese Serie vorhanden.');
        }

        $details = $this->tmdb->getTvDetails($movie->tmdb_id);
        if (isset($details['error'])) {
            throw new TmdbImportException($details['error']);
        }

        $existing = $movie->seasons()->pluck('season_number')->all();
        $toImport = array_values(array_diff(array_map('intval', $requestedSeasons), $existing));

        if (empty($toImport)) {
            return 0;
        }

        try {
            return DB::transaction(function () use ($movie, $details, $toImport) {
                $this->importSeasons($movie, $details, $toImport);
                // Sync: Clients ziehen Staffeln nur mit dem Serien-Datensatz –
                // ohne Touch sähe der Delta-Sync die Änderung nie.
                $movie->touch();

                return count($toImport);
            });
        } catch (\Exception $e) {
            Log::error('TmdbSeasonBackfill Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Staffeln einer bestehenden Serie entfernen (Gegenstück zum Nachladen).
     * Episoden hängen per FK-Cascade an der Staffel und werden mitgelöscht.
     *
     * @return int Anzahl entfernter Staffeln
     */
    public function removeSeasonsForExisting(Movie $movie, array $requestedSeasons): int
    {
        $numbers = array_map('intval', $requestedSeasons);

        return DB::transaction(function () use ($movie, $numbers) {
            $seasons = $movie->seasons()->whereIn('season_number', $numbers)->get();

            foreach ($seasons as $season) {
                $season->delete();
            }

            if ($seasons->isNotEmpty()) {
                $movie->touch();
            }

            return $seasons->count();
        });
    }

    /**
     * Bulk update a movie from TMDb.
     */
    public function bulkUpdate(Movie $movie)
    {
        if (! $movie->tmdb_id) {
            throw new TmdbImportException('Keine TMDb ID für diesen Film vorhanden.');
        }

        try {
            return DB::transaction(function () use ($movie) {
                $isTv = ($movie->tmdb_type === 'tv');
                $details = $isTv ? $this->tmdb->getTvDetails($movie->tmdb_id) : $this->tmdb->getMovieDetails($movie->tmdb_id);

                if (isset($details['error'])) {
                    throw new TmdbImportException($details['error']);
                }

                $movie->update($this->getUpdateData($movie, $details, $isTv));
                $this->handleActors($movie, $details);

                return $movie;
            });
        } catch (\Exception $e) {
            Log::error('TmdbBulkUpdate Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle image downloading for movies/series.
     */
    /**
     * Externen Film (nicht in der Sammlung) aus TMDb anlegen – für Listen-Items.
     * Lädt Cover/Backdrop auf den Master-Server (kein TMDb-Hotlink beim Client).
     */
    public function createExternalFromTmdb(int $tmdbId, string $type, ?int $userId): ExternalMovie
    {
        $isTv = $type === 'tv';
        $details = $isTv ? $this->tmdb->getTvDetails($tmdbId) : $this->tmdb->getMovieDetails($tmdbId);
        if (isset($details['error'])) {
            throw new TmdbImportException($details['error']);
        }

        return DB::transaction(function () use ($details, $tmdbId, $type, $isTv, $userId) {
            $external = ExternalMovie::create([
                'user_id'         => $userId,
                'title'           => $isTv ? ($details['name'] ?? '') : ($details['title'] ?? ''),
                'year'            => $isTv
                    ? (isset($details['first_air_date']) ? (int) substr($details['first_air_date'], 0, 4) : null)
                    : (isset($details['release_date']) ? (int) substr($details['release_date'], 0, 4) : null),
                'rating'          => $details['vote_average'] ?? null,
                'genre'           => implode(', ', array_column($details['genres'] ?? [], 'name')),
                'runtime'         => $isTv ? ($details['episode_run_time'][0] ?? null) : ($details['runtime'] ?? null),
                'overview'        => $details['overview'] ?? null,
                'director'        => $isTv ? $this->extractCreator($details) : $this->extractDirector($details),
                'trailer_url'     => $this->extractTrailer($details),
                'collection_type' => $isTv ? 'Serie' : 'Film',
                'rating_age'      => $this->extractRating($details),
                'tmdb_id'         => $tmdbId,
                'tmdb_type'       => $type,
            ]);

            $this->handleImages($external, $details);

            return $external;
        });
    }

    protected function handleImages($movie, array $details)
    {
        if (! empty($details['poster_path'])) {
            try {
                $posterUrl = 'https://image.tmdb.org/t/p/w500'.$details['poster_path'];
                $imageContent = Http::withOptions([
                    'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
                ])->get($posterUrl)->body();
                $filename = 'covers/'.Str::random(20).'.jpg';
                Storage::disk('public')->put($filename, $imageContent);
                $movie->update(['cover_id' => $filename]);
            } catch (\Exception $e) {
                Log::warning('Tmdb poster download failed: '.$e->getMessage());
            }
        }

        if (! empty($details['backdrop_path'])) {
            try {
                $backdropUrl = 'https://image.tmdb.org/t/p/original'.$details['backdrop_path'];
                $imageContent = Http::withOptions([
                    'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
                ])->get($backdropUrl)->body();
                $filename = 'backdrops/'.Str::random(20).'.jpg';
                Storage::disk('public')->put($filename, $imageContent);
                $movie->update(['backdrop_id' => $filename]);
            } catch (\Exception $e) {
                Log::warning('Tmdb backdrop download failed: '.$e->getMessage());
            }
        }
    }

    /**
     * Handle actor synchronization.
     */
    protected function handleActors(Movie $movie, array $details)
    {
        if (! isset($details['credits']['cast'])) {
            return;
        }

        $cast = array_slice($details['credits']['cast'], 0, 10);
        foreach ($cast as $person) {
            $actor = $this->findOrCreateActor($person);
            $this->updateActorProfile($actor, $person);

            $movie->actors()->syncWithoutDetaching([
                $actor->id => [
                    'role' => $person['character'],
                    'is_main_role' => $person['order'] < 3,
                    'sort_order' => $person['order'],
                ],
            ]);
        }
    }

    protected function findOrCreateActor(array $person): Actor
    {
        $resolved = $this->resolveActorName($person);
        $nameParts = explode(' ', $resolved['name'], 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $actor = Actor::where('tmdb_id', $person['id'])->first();

        if (! $actor) {
            $actor = Actor::where('first_name', $firstName)
                ->where('last_name', $lastName)
                ->first();
        }

        if ($actor) {
            $updates = [];
            if (! $actor->tmdb_id) {
                $updates['tmdb_id'] = $person['id'];
            }
            // Frühere Importe haben CJK-Namen gespeichert – auf den romanisierten Namen reparieren.
            if (! self::hasLatinCharacters($actor->full_name) && self::hasLatinCharacters($resolved['name'])) {
                $updates['first_name'] = $firstName;
                $updates['last_name'] = $lastName;
                $updates['original_name'] = $actor->original_name ?: trim($actor->full_name);
            }
            if (empty($actor->original_name) && $resolved['original_name']) {
                $updates['original_name'] = $resolved['original_name'];
            }
            if ($updates) {
                $actor->update($updates);
            }

            return $actor;
        }

        return Actor::create([
            'tmdb_id' => $person['id'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'original_name' => $resolved['original_name'],
        ]);
    }

    /**
     * TMDb liefert ohne Übersetzung für die eingestellte Sprache den Namen in
     * Originalschrift (z. B. Kanji/Hanzi). Dann den romanisierten Namen aus den
     * englischen Personendaten nachladen und die Originalschrift separat behalten.
     */
    protected function resolveActorName(array $person): array
    {
        $name = trim($person['name'] ?? '');
        $originalName = trim($person['original_name'] ?? '') ?: null;

        if (! self::hasLatinCharacters($name) && ! empty($person['id'])) {
            $originalName = $originalName ?: $name;
            $english = $this->tmdb->getPersonDetails((int) $person['id'], 'en-US');
            if (empty($english['error']) && self::hasLatinCharacters($english['name'] ?? '')) {
                $name = trim($english['name']);
            }
        }

        if ($originalName === $name) {
            $originalName = null;
        }

        return ['name' => $name, 'original_name' => $originalName];
    }

    public static function hasLatinCharacters(string $value): bool
    {
        return (bool) preg_match('/\p{Latin}/u', $value);
    }

    protected function updateActorProfile(Actor $actor, array $person)
    {
        if (! empty($person['profile_path']) && empty($actor->profile_path)) {
            try {
                $profileUrl = 'https://image.tmdb.org/t/p/w185'.$person['profile_path'];
                $imageContent = Http::withOptions([
                    'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
                ])->get($profileUrl)->body();
                $filename = 'actors/'.Str::random(20).'.jpg';
                Storage::disk('public')->put($filename, $imageContent);
                $actor->update(['profile_path' => $filename]);
            } catch (\Exception $e) {
                // Ignore image download errors
            }
        }
    }

    protected function importSeasons(Movie $movie, array $details, array $requestedSeasons)
    {
        if (! isset($details['seasons'])) {
            return;
        }

        $hasFilter = ! empty($requestedSeasons);

        foreach ($details['seasons'] as $tmdbSeason) {
            if ($tmdbSeason['season_number'] === 0) {
                continue;
            }
            if ($hasFilter && ! in_array($tmdbSeason['season_number'], $requestedSeasons)) {
                continue;
            }

            $seasonDetails = $this->tmdb->getSeasonDetails($movie->tmdb_id, $tmdbSeason['season_number']);
            $season = Season::create([
                'movie_id' => $movie->id,
                'season_number' => $tmdbSeason['season_number'],
                'title' => $tmdbSeason['name'],
                'overview' => $tmdbSeason['overview'] ?? null,
            ]);

            $this->importEpisodes($season, $seasonDetails);
        }
    }

    protected function importEpisodes($season, $seasonDetails)
    {
        if (! isset($seasonDetails['episodes'])) {
            return;
        }

        foreach ($seasonDetails['episodes'] as $tmdbEpisode) {
            Episode::create([
                'season_id' => $season->id,
                'episode_number' => $tmdbEpisode['episode_number'],
                'title' => $tmdbEpisode['name'],
                'overview' => $tmdbEpisode['overview'] ?? null,
                'runtime' => $tmdbEpisode['runtime'] ?? null,
                'air_date' => ! empty($tmdbEpisode['air_date']) ? $tmdbEpisode['air_date'] : null,
            ]);
        }
    }

    public function extractRating(array $details): ?int
    {
        return $this->getGermanRating($details)
            ?? $this->getGermanTvRating($details)
            ?? $this->getUsFallbackRating($details);
    }

    protected function getGermanRating(array $details): ?int
    {
        if (! isset($details['release_dates']['results'])) {
            return null;
        }

        foreach ($details['release_dates']['results'] as $result) {
            if ($result['iso_3166_1'] === 'DE') {
                foreach ($result['release_dates'] as $release) {
                    if (! empty($release['certification'])) {
                        return (int) preg_replace('/\D/', '', $release['certification']);
                    }
                }
            }
        }

        return null;
    }

    protected function getGermanTvRating(array $details): ?int
    {
        if (! isset($details['content_ratings']['results'])) {
            return null;
        }

        foreach ($details['content_ratings']['results'] as $result) {
            if ($result['iso_3166_1'] === 'DE' && ! empty($result['rating'])) {
                return (int) preg_replace('/\D/', '', $result['rating']);
            }
        }

        return null;
    }

    protected function getUsFallbackRating(array $details): ?int
    {
        $rating = null;

        if (isset($details['content_ratings']['results'])) {
            $usRating = collect($details['content_ratings']['results'])->firstWhere('iso_3166_1', 'US');

            if ($usRating && ! empty($usRating['rating'])) {
                if (is_numeric($usRating['rating'])) {
                    $rating = (int) $usRating['rating'];
                } else {
                    $map = ['TV-Y' => 0, 'TV-Y7' => 6, 'TV-G' => 0, 'TV-PG' => 6, 'TV-14' => 12, 'TV-MA' => 16];
                    $rating = $map[$usRating['rating']] ?? null;
                }
            }
        }

        return $rating;
    }

    public function extractTrailer(array $details): ?string
    {
        if (isset($details['videos']['results'])) {
            foreach ($details['videos']['results'] as $video) {
                if ($video['site'] === 'YouTube' && ($video['type'] === 'Trailer' || $video['type'] === 'Teaser')) {
                    return 'https://www.youtube.com/watch?v='.$video['key'];
                }
            }
        }

        return null;
    }

    public function extractDirector(array $details): ?string
    {
        if (isset($details['credits']['crew'])) {
            foreach ($details['credits']['crew'] as $person) {
                if ($person['job'] === 'Director') {
                    return $person['name'];
                }
            }
        }

        return null;
    }

    public function extractCreator(array $details): ?string
    {
        if (isset($details['created_by']) && ! empty($details['created_by'])) {
            return $details['created_by'][0]['name'];
        }

        return null;
    }

    protected function getUpdateData(Movie $movie, array $details, bool $isTv): array
    {
        $updateData = [
            'title' => $details['name'] ?? $details['title'],
            'year' => isset($details['release_date']) || isset($details['first_air_date'])
                ? (int) substr($details['release_date'] ?? $details['first_air_date'], 0, 4)
                : $movie->year,
            'rating' => $details['vote_average'] ?? $movie->rating,
            'genre' => implode(', ', array_column($details['genres'], 'name')),
            'runtime' => $details['runtime'] ?? ($details['episode_run_time'][0] ?? $movie->runtime),
            'overview' => $details['overview'] ?? $movie->overview,
            'director' => $isTv ? $this->extractCreator($details) : $this->extractDirector($details),
            'rating_age' => $this->extractRating($details) ?? $movie->rating_age,
            'tmdb_json' => $details,
        ];

        if (! $isTv && isset($details['videos'])) {
            $updateData['trailer_url'] = $this->extractTrailer($details) ?? $movie->trailer_url;
        }

        return $updateData;
    }

    public function cleanTitle(string $title): string
    {
        $title = preg_replace('/\s*\(.*?\)\s*/', ' ', $title);
        $title = preg_replace('/\s*\[.*?\]\s*/', ' ', $title);
        $title = preg_replace('/\b(DVD|Blu-ray|BluRay|4K|UHD|Remastered|Steelbook)\b/i', '', $title);

        return trim($title);
    }
}
