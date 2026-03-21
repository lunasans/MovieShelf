<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Actor;
use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TmdbImportController extends Controller
{
    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function index()
    {
        return view('admin.tmdb.index');
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        $type = $request->get('type', 'movie');

        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        $results = ($type === 'tv') ? $this->tmdb->searchTv($query) : $this->tmdb->searchMovie($query);

        return response()->json($results);
    }

    public function details(Request $request)
    {
        $tmdbId = $request->get('tmdb_id');
        $type = $request->get('type', 'movie');

        if (!$tmdbId) {
            return response()->json(['error' => 'Keine ID angegeben'], 400);
        }

        $details = ($type === 'tv') ? $this->tmdb->getTvDetails($tmdbId) : $this->tmdb->getMovieDetails($tmdbId);

        return response()->json($details);
    }

    public function import(Request $request)
    {
        $tmdbId = $request->get('tmdb_id');
        $mediaType = $request->get('media_type', 'movie');

        if (!$tmdbId) {
            return back()->with('error', 'Keine TMDb ID angegeben.');
        }

        if ($mediaType === 'tv') {
            return $this->importTv($tmdbId, $request);
        }

        return $this->importMovie($tmdbId);
    }

    protected function importMovie(int $tmdbId)
    {
        $details = $this->tmdb->getMovieDetails($tmdbId);
        if (isset($details['error'])) {
            return back()->with('error', $details['error']);
        }

        try {
            DB::beginTransaction();

            $movie = Movie::create([
                'title' => $details['title'],
                'year' => isset($details['release_date']) ? (int) substr($details['release_date'], 0, 4) : null,
                'rating' => $details['vote_average'] ?? null,
                'genre' => implode(', ', array_column($details['genres'], 'name')),
                'runtime' => $details['runtime'] ?? null,
                'overview' => $details['overview'] ?? null,
                'director' => $this->extractDirector($details),
                'trailer_url' => $this->extractTrailer($details),
                'collection_type' => 'Blu-ray',
                'rating_age' => $this->extractRating($details),
                'user_id' => auth()->id(),
                'tmdb_id' => $tmdbId,
                'tmdb_type' => 'movie',
                'tmdb_json' => $details,
            ]);

            $this->handleImages($movie, $details);
            $this->handleActors($movie, $details);

            DB::commit();
            $this->logActivity($movie, 'MOVIE_IMPORT', ['media_type' => 'movie', 'tmdb_id' => $tmdbId]);

            return redirect()->route('admin.movies.index')->with('success', "Film '{$movie->title}' wurde erfolgreich importiert.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Fehler beim Import: '.$e->getMessage());
        }
    }

    protected function extractRating(array $details): ?int
    {
        return $this->getGermanRating($details)
            ?? $this->getGermanTvRating($details)
            ?? $this->getUsFallbackRating($details);
    }

    private function getGermanRating(array $details): ?int
    {
        if (! isset($details['release_dates']['results'])) {
            return null;
        }

        foreach ($details['release_dates']['results'] as $result) {
            if ($result['iso_3166_1'] === 'DE') {
                foreach ($result['release_dates'] as $release) {
                    if (! empty($release['certification'])) {
                        return (int) preg_replace('/[^0-9]/', '', $release['certification']);
                    }
                }
            }
        }

        return null;
    }

    private function getGermanTvRating(array $details): ?int
    {
        if (! isset($details['content_ratings']['results'])) {
            return null;
        }

        foreach ($details['content_ratings']['results'] as $result) {
            if ($result['iso_3166_1'] === 'DE' && ! empty($result['rating'])) {
                return (int) preg_replace('/[^0-9]/', '', $result['rating']);
            }
        }

        return null;
    }

    private function getUsFallbackRating(array $details): ?int
    {
        if (! isset($details['content_ratings']['results'])) {
            return null;
        }

        $usRating = collect($details['content_ratings']['results'])->firstWhere('iso_3166_1', 'US');
        if ($usRating && ! empty($usRating['rating'])) {
            if (is_numeric($usRating['rating'])) {
                return (int) $usRating['rating'];
            }
            $map = ['TV-Y' => 0, 'TV-Y7' => 6, 'TV-G' => 0, 'TV-PG' => 6, 'TV-14' => 12, 'TV-MA' => 16];

            return $map[$usRating['rating']] ?? null;
        }

        return null;
    }

    protected function extractTrailer(array $details): ?string
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

    protected function extractDirector(array $details): ?string
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

    protected function importTv(int $tmdbId, Request $request)
    {
        $details = $this->tmdb->getTvDetails($tmdbId);
        if (isset($details['error'])) {
            return back()->with('error', $details['error']);
        }

        try {
            DB::beginTransaction();

            $movie = Movie::create([
                'title' => $details['name'],
                'year' => isset($details['first_air_date']) ? (int) substr($details['first_air_date'], 0, 4) : null,
                'rating' => $details['vote_average'] ?? null,
                'genre' => implode(', ', array_column($details['genres'], 'name')),
                'runtime' => $details['episode_run_time'][0] ?? null,
                'overview' => $details['overview'] ?? null,
                'director' => $this->extractCreator($details),
                'trailer_url' => $this->extractTrailer($details),
                'collection_type' => 'Serie',
                'rating_age' => $this->extractRating($details),
                'user_id' => auth()->id(),
                'tmdb_id' => $tmdbId,
                'tmdb_type' => 'tv',
                'tmdb_json' => $details,
            ]);

            $this->handleImages($movie, $details);
            $this->handleActors($movie, $details);
            $this->importSeasons($movie, $details, $request->get('seasons', []));

            DB::commit();
            $this->logActivity($movie, 'SERIES_IMPORT', ['media_type' => 'tv', 'tmdb_id' => $tmdbId]);

            return redirect()->route('admin.movies.index')->with('success', "Serie '{$movie->title}' wurde erfolgreich importiert.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Fehler beim Serien-Import: '.$e->getMessage());
        }
    }

    private function importSeasons(Movie $movie, array $details, array $requestedSeasons)
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
            $season = \App\Models\Season::create([
                'movie_id' => $movie->id,
                'season_number' => $tmdbSeason['season_number'],
                'title' => $tmdbSeason['name'],
                'overview' => $tmdbSeason['overview'] ?? null,
            ]);

            $this->importEpisodes($season, $seasonDetails);
        }
    }

    private function importEpisodes($season, $seasonDetails)
    {
        if (! isset($seasonDetails['episodes'])) {
            return;
        }

        foreach ($seasonDetails['episodes'] as $tmdbEpisode) {
            \App\Models\Episode::create([
                'season_id' => $season->id,
                'episode_number' => $tmdbEpisode['episode_number'],
                'title' => $tmdbEpisode['name'],
                'overview' => $tmdbEpisode['overview'] ?? null,
            ]);
        }
    }

    protected function handleImages($movie, $details)
    {
        if (! empty($details['poster_path'])) {
            $posterUrl = 'https://image.tmdb.org/t/p/w500'.$details['poster_path'];
            $imageContent = Http::get($posterUrl)->body();
            $filename = 'covers/'.Str::random(20).'.jpg';
            Storage::disk('public')->put($filename, $imageContent);
            $movie->update(['cover_id' => $filename]);
        }

        if (! empty($details['backdrop_path'])) {
            $backdropUrl = 'https://image.tmdb.org/t/p/original'.$details['backdrop_path'];
            $imageContent = Http::get($backdropUrl)->body();
            $filename = 'backdrops/'.Str::random(20).'.jpg';
            Storage::disk('public')->put($filename, $imageContent);
            $movie->update(['backdrop_id' => $filename]);
        }
    }

    protected function handleActors($movie, $details)
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

    private function findOrCreateActor(array $person): Actor
    {
        $nameParts = explode(' ', $person['name'], 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $actor = Actor::where('tmdb_id', $person['id'])->first();

        if (! $actor) {
            $actor = Actor::where('first_name', $firstName)
                          ->where('last_name', $lastName)
                          ->first();
        }

        if ($actor) {
            if (! $actor->tmdb_id) {
                $actor->update(['tmdb_id' => $person['id']]);
            }

            return $actor;
        }

        return Actor::create([
            'tmdb_id' => $person['id'],
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
    }

    private function updateActorProfile(Actor $actor, array $person)
    {
        if (! empty($person['profile_path']) && empty($actor->profile_path)) {
            try {
                $profileUrl = 'https://image.tmdb.org/t/p/w185'.$person['profile_path'];
                $imageContent = Http::get($profileUrl)->body();
                $filename = 'actors/'.Str::random(20).'.jpg';
                Storage::disk('public')->put($filename, $imageContent);
                $actor->update(['profile_path' => $filename]);
            } catch (\Exception $e) {
                // Ignore image download errors
            }
        }
    }

    protected function extractCreator(array $details): ?string
    {
        if (isset($details['created_by']) && ! empty($details['created_by'])) {
            return $details['created_by'][0]['name'];
        }

        return null;
    }

    public function getMoviesForUpdate()
    {
        $movies = Movie::whereNotNull('tmdb_id')
            ->select('id', 'title', 'tmdb_id', 'tmdb_type')
            ->get();

        return response()->json($movies);
    }

    public function bulkUpdate(Request $request)
    {
        $movieId = $request->get('movie_id');
        $movie = Movie::findOrFail($movieId);

        if (! $movie->tmdb_id) {
            return response()->json(['error' => 'Keine TMDb ID für diesen Film vorhanden.'], 400);
        }

        try {
            DB::beginTransaction();
            $isTv = ($movie->tmdb_type === 'tv');
            $details = $isTv ? $this->tmdb->getTvDetails($movie->tmdb_id) : $this->tmdb->getMovieDetails($movie->tmdb_id);

            if (isset($details['error'])) {
                throw new \RuntimeException($details['error']);
            }

            $movie->update($this->getUpdateData($movie, $details, $isTv));
            $this->handleActors($movie, $details);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getUpdateData(Movie $movie, array $details, bool $isTv): array
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

    protected function logActivity(Movie $movie, string $action, array $extraDetails = [])
    {
        $details = array_merge([
            'movie_id' => $movie->id,
            'title' => $movie->title,
        ], $extraDetails);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => json_encode($details),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getUnlinkedMovies()
    {
        $movies = Movie::whereNull('tmdb_id')
            ->select('id', 'title', 'year')
            ->get();

        return response()->json($movies);
    }

    public function autoLinkMovie(Request $request)
    {
        $movieId = $request->get('movie_id');
        $movie = Movie::findOrFail($movieId);

        try {
            $cleanTitle = $this->cleanTitle($movie->title);
            $year = $movie->year;

            // Try movie with year
            $search = $this->tmdb->searchMovie($cleanTitle, $year);
            if ($this->hasMatch($search)) {
                return $this->linkMovie($movie, $search['results'][0], 'movie');
            }

            // Try movie without year
            if ($year) {
                $search = $this->tmdb->searchMovie($cleanTitle);
                if ($this->hasMatch($search)) {
                    return $this->linkMovie($movie, $search['results'][0], 'movie');
                }
            }

            // Try TV with year
            $searchTv = $this->tmdb->searchTv($cleanTitle, $year);
            if ($this->hasMatch($searchTv)) {
                return $this->linkMovie($movie, $searchTv['results'][0], 'tv');
            }

            // Try TV without year
            $searchTv = $this->tmdb->searchTv($cleanTitle);
            if ($this->hasMatch($searchTv)) {
                return $this->linkMovie($movie, $searchTv['results'][0], 'tv');
            }

            return response()->json(['success' => false, 'message' => 'Kein Treffer bei TMDb gefunden.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cleanTitle(string $title): string
    {
        $title = preg_replace('/\s*\(.*?\)\s*/', ' ', $title);
        $title = preg_replace('/\s*\[.*?\]\s*/', ' ', $title);
        $title = preg_replace('/\b(DVD|Blu-ray|BluRay|4K|UHD|Remastered|Steelbook)\b/i', '', $title);

        return trim($title);
    }

    protected function hasMatch(array $search): bool
    {
        return isset($search['results']) && count($search['results']) > 0;
    }

    protected function linkMovie(Movie $movie, array $tmdbResult, string $type)
    {
        $movie->update([
            'tmdb_id' => $tmdbResult['id'],
            'tmdb_type' => $type,
        ]);

        $title = $tmdbResult['title'] ?? $tmdbResult['name'];
        $date = $tmdbResult['release_date'] ?? $tmdbResult['first_air_date'] ?? '';
        $year = substr($date, 0, 4);

        return response()->json([
            'success' => true,
            'match' => "{$title} ({$year})".($type === 'tv' ? ' [Serie]' : ''),
        ]);
    }
}