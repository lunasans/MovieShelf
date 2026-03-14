<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TmdbService;
use App\Models\Movie;
use App\Models\Actor;
use App\Models\ActivityLog;
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

        if ($type === 'tv') {
            $results = $this->tmdb->searchTv($query);
        } else {
            $results = $this->tmdb->searchMovie($query);
        }
        
        return response()->json($results);
    }

    public function details(Request $request)
    {
        $tmdbId = $request->get('tmdb_id');
        $type = $request->get('type', 'movie');

        if (!$tmdbId) {
            return response()->json(['error' => 'Keine ID angegeben'], 400);
        }

        if ($type === 'tv') {
            $details = $this->tmdb->getTvDetails($tmdbId);
        } else {
            $details = $this->tmdb->getMovieDetails($tmdbId);
        }

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

        $details = $this->tmdb->getMovieDetails($tmdbId);
        if (isset($details['error'])) {
            return back()->with('error', $details['error']);
        }

        try {
            DB::beginTransaction();

            $movie = Movie::create([
                'title' => $details['title'],
                'year' => isset($details['release_date']) ? (int)substr($details['release_date'], 0, 4) : null,
                'rating' => $details['vote_average'] ?? null,
                'genre' => implode(', ', array_column($details['genres'], 'name')),
                'runtime' => $details['runtime'] ?? null,
                'overview' => $details['overview'] ?? null,
                'director' => $this->extractDirector($details),
                'trailer_url' => $this->extractTrailer($details),
                'collection_type' => 'Blu-ray', // Default
                'rating_age' => $this->extractRating($details),
                'user_id' => auth()->id(),
                'tmdb_id' => $tmdbId,
                'tmdb_type' => 'movie',
                'tmdb_json' => $details,
            ]);

            // Handle Images (Poster & Backdrop)
            if (!empty($details['poster_path'])) {
                $posterUrl = "https://image.tmdb.org/t/p/w500" . $details['poster_path'];
                $imageContent = Http::get($posterUrl)->body();
                $filename = 'covers/' . Str::random(20) . '.jpg';
                Storage::disk('public')->put($filename, $imageContent);
                $movie->update(['cover_id' => $filename]);
            }

            if (!empty($details['backdrop_path'])) {
                $backdropUrl = "https://image.tmdb.org/t/p/original" . $details['backdrop_path'];
                $imageContent = Http::get($backdropUrl)->body();
                $filename = 'backdrops/' . Str::random(20) . '.jpg';
                Storage::disk('public')->put($filename, $imageContent);
                $movie->update(['backdrop_id' => $filename]);
            }

            // Handle Actors (Top 10)
            if (isset($details['credits']['cast'])) {
                $cast = array_slice($details['credits']['cast'], 0, 10);
                foreach ($cast as $person) {
                    $nameParts = explode(' ', $person['name'], 2);
                    $firstName = $nameParts[0];
                    $lastName = $nameParts[1] ?? '';

                $actor = Actor::where('tmdb_id', $person['id'])->first();

                // Fallback: Check by name if no tmdb_id match (for legacy v1.5 imports)
                if (!$actor) {
                    $actor = \App\Models\Actor::where('first_name', $firstName)
                                  ->where('last_name', $lastName)
                                  ->first();
                }

                if ($actor) {
                    // Update existing actor
                    if (!$actor->tmdb_id) {
                        $actor->update(['tmdb_id' => $person['id']]);
                    }
                } else {
                    // Create entirely new actor
                    $actor = \App\Models\Actor::create([
                        'tmdb_id' => $person['id'],
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ]);
                }

                    // Handle Profile Image
                    if (!empty($person['profile_path']) && empty($actor->profile_path)) {
                        try {
                            $profileUrl = "https://image.tmdb.org/t/p/w185" . $person['profile_path'];
                            $imageContent = Http::get($profileUrl)->body();
                            $filename = 'actors/' . Str::random(20) . '.jpg';
                            Storage::disk('public')->put($filename, $imageContent);
                            $actor->update(['profile_path' => $filename]);
                        } catch (\Exception $e) {
                            Log::error("Could not download actor profile: " . $e->getMessage());
                        }
                    }

                    $movie->actors()->syncWithoutDetaching([
                        $actor->id => [
                            'role' => $person['character'],
                            'is_main_role' => $person['order'] < 3,
                            'sort_order' => $person['order']
                        ]
                    ]);
                }
            }

            DB::commit();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'MOVIE_IMPORT',
                'details' => json_encode([
                    'movie_id' => $movie->id,
                    'title' => $movie->title,
                    'media_type' => 'movie',
                    'tmdb_id' => $tmdbId,
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.movies.index')->with('success', "Filme '{$movie->title}' wurde erfolgreich importiert.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Fehler beim Import: ' . $e->getMessage());
        }
    }

    protected function extractRating(array $details): ?int
    {
        // 1. Check for German rating (DE)
        if (isset($details['release_dates']['results'])) {
            foreach ($details['release_dates']['results'] as $result) {
                if ($result['iso_3166_1'] === 'DE') {
                    foreach ($result['release_dates'] as $release) {
                        if (!empty($release['certification'])) {
                            return (int)preg_replace('/[^0-9]/', '', $release['certification']);
                        }
                    }
                }
            }
        }

        // 2. Check for German TV rating (DE)
        if (isset($details['content_ratings']['results'])) {
            foreach ($details['content_ratings']['results'] as $result) {
                if ($result['iso_3166_1'] === 'DE') {
                    if (!empty($result['rating'])) {
                        return (int)preg_replace('/[^0-9]/', '', $result['rating']);
                    }
                }
            }
        }

        // 3. Fallback: Check for US rating as a generic indicator for series if DE is missing
        if (isset($details['content_ratings']['results'])) {
            $usRating = collect($details['content_ratings']['results'])->firstWhere('iso_3166_1', 'US');
            if ($usRating && !empty($usRating['rating'])) {
                // Map common US TV ratings to FSK approximations if possible, but keeping it simple for now
                if (is_numeric($usRating['rating'])) return (int)$usRating['rating'];
                
                $map = ['TV-Y' => 0, 'TV-Y7' => 6, 'TV-G' => 0, 'TV-PG' => 6, 'TV-14' => 12, 'TV-MA' => 16];
                return $map[$usRating['rating']] ?? null;
            }
        }

        return null;
    }

    protected function extractTrailer(array $details): ?string
    {
        if (isset($details['videos']['results'])) {
            foreach ($details['videos']['results'] as $video) {
                if ($video['site'] === 'YouTube' && ($video['type'] === 'Trailer' || $video['type'] === 'Teaser')) {
                    return "https://www.youtube.com/watch?v=" . $video['key'];
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
                'year' => isset($details['first_air_date']) ? (int)substr($details['first_air_date'], 0, 4) : null,
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

            // Images
            $this->handleImages($movie, $details);

            // Actors
            $this->handleActors($movie, $details);

            // Seasons & Episodes
            if (isset($details['seasons'])) {
                $requestedSeasons = $request->get('seasons', []); // Array von season_numbers
                $hasFilter = !empty($requestedSeasons);

                foreach ($details['seasons'] as $tmdbSeason) {
                    if ($tmdbSeason['season_number'] === 0) continue; // Skip specials by default
                    
                    // Filter anwenden, falls vorhanden
                    if ($hasFilter && !in_array($tmdbSeason['season_number'], $requestedSeasons)) {
                        continue;
                    }

                    $seasonDetails = $this->tmdb->getSeasonDetails($tmdbId, $tmdbSeason['season_number']);
                    
                    $season = \App\Models\Season::create([
                        'movie_id' => $movie->id,
                        'season_number' => $tmdbSeason['season_number'],
                        'title' => $tmdbSeason['name'],
                        'overview' => $tmdbSeason['overview'] ?? null,
                    ]);

                    if (isset($seasonDetails['episodes'])) {
                        foreach ($seasonDetails['episodes'] as $tmdbEpisode) {
                            \App\Models\Episode::create([
                                'season_id' => $season->id,
                                'episode_number' => $tmdbEpisode['episode_number'],
                                'title' => $tmdbEpisode['name'],
                                'overview' => $tmdbEpisode['overview'] ?? null,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'SERIES_IMPORT',
                'details' => json_encode([
                    'movie_id' => $movie->id,
                    'title' => $movie->title,
                    'media_type' => 'tv',
                    'tmdb_id' => $tmdbId,
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.movies.index')->with('success', "Serie '{$movie->title}' wurde erfolgreich importiert.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Fehler beim Serien-Import: ' . $e->getMessage());
        }
    }

    protected function handleImages($movie, $details)
    {
        if (!empty($details['poster_path'])) {
            $posterUrl = "https://image.tmdb.org/t/p/w500" . $details['poster_path'];
            $imageContent = Http::get($posterUrl)->body();
            $filename = 'covers/' . Str::random(20) . '.jpg';
            Storage::disk('public')->put($filename, $imageContent);
            $movie->update(['cover_id' => $filename]);
        }

        if (!empty($details['backdrop_path'])) {
            $backdropUrl = "https://image.tmdb.org/t/p/original" . $details['backdrop_path'];
            $imageContent = Http::get($backdropUrl)->body();
            $filename = 'backdrops/' . Str::random(20) . '.jpg';
            Storage::disk('public')->put($filename, $imageContent);
            $movie->update(['backdrop_id' => $filename]);
        }
    }

    protected function handleActors($movie, $details)
    {
        if (isset($details['credits']['cast'])) {
            $cast = array_slice($details['credits']['cast'], 0, 10);
            foreach ($cast as $person) {
                $nameParts = explode(' ', $person['name'], 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? '';

                $actor = \App\Models\Actor::where('tmdb_id', $person['id'])->first();

                if (!$actor) {
                    $actor = \App\Models\Actor::where('first_name', $firstName)
                                  ->where('last_name', $lastName)
                                  ->first();
                }

                if ($actor) {
                    if (!$actor->tmdb_id) {
                        $actor->update(['tmdb_id' => $person['id']]);
                    }
                } else {
                    $actor = \App\Models\Actor::create([
                        'tmdb_id' => $person['id'],
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ]);
                }

                if (!empty($person['profile_path']) && empty($actor->profile_path)) {
                    try {
                        $profileUrl = "https://image.tmdb.org/t/p/w185" . $person['profile_path'];
                        $imageContent = Http::get($profileUrl)->body();
                        $filename = 'actors/' . Str::random(20) . '.jpg';
                        Storage::disk('public')->put($filename, $imageContent);
                        $actor->update(['profile_path' => $filename]);
                    } catch (\Exception $e) {}
                }

                $movie->actors()->syncWithoutDetaching([
                    $actor->id => [
                        'role' => $person['character'],
                        'is_main_role' => $person['order'] < 3,
                        'sort_order' => $person['order']
                    ]
                ]);
            }
        }
    }

    protected function extractCreator(array $details): ?string
    {
        if (isset($details['created_by']) && !empty($details['created_by'])) {
            return $details['created_by'][0]['name'];
        }
        return null;
    }

    /**
     * Get a list of movies that have a TMDb ID and can be updated.
     */
    public function getMoviesForUpdate()
    {
        $movies = Movie::whereNotNull('tmdb_id')
            ->select('id', 'title', 'tmdb_id', 'tmdb_type')
            ->get();
            
        return response()->json($movies);
    }

    /**
     * Update a single movie's metadata from TMDb.
     */
    public function bulkUpdate(Request $request)
    {
        $movieId = $request->get('movie_id');
        $movie = Movie::findOrFail($movieId);

        if (!$movie->tmdb_id) {
            return response()->json(['error' => 'Keine TMDb ID für diesen Film vorhanden.'], 400);
        }

        try {
            DB::beginTransaction();

            if ($movie->tmdb_type === 'tv') {
                $details = $this->tmdb->getTvDetails($movie->tmdb_id);
                if (isset($details['error'])) throw new \Exception($details['error']);

                $movie->update([
                    'title' => $details['name'],
                    'year' => isset($details['first_air_date']) ? (int)substr($details['first_air_date'], 0, 4) : $movie->year,
                    'rating' => $details['vote_average'] ?? $movie->rating,
                    'genre' => implode(', ', array_column($details['genres'], 'name')),
                    'runtime' => $details['episode_run_time'][0] ?? $movie->runtime,
                    'overview' => $details['overview'] ?? $movie->overview,
                    'director' => $this->extractCreator($details),
                    'rating_age' => $this->extractRating($details) ?? $movie->rating_age,
                    'tmdb_json' => $details,
                ]);

                $this->handleActors($movie, $details);
            } else {
                $details = $this->tmdb->getMovieDetails($movie->tmdb_id);
                if (isset($details['error'])) throw new \Exception($details['error']);

                $movie->update([
                    'title' => $details['title'],
                    'year' => isset($details['release_date']) ? (int)substr($details['release_date'], 0, 4) : $movie->year,
                    'rating' => $details['vote_average'] ?? $movie->rating,
                    'genre' => implode(', ', array_column($details['genres'], 'name')),
                    'runtime' => $details['runtime'] ?? $movie->runtime,
                    'overview' => $details['overview'] ?? $movie->overview,
                    'director' => $this->extractDirector($details),
                    'trailer_url' => $this->extractTrailer($details) ?? $movie->trailer_url,
                    'rating_age' => $this->extractRating($details) ?? $movie->rating_age,
                    'tmdb_json' => $details,
                ]);

                // Handle Actors (Top 10)
                if (isset($details['credits']['cast'])) {
                    $cast = array_slice($details['credits']['cast'], 0, 10);
                    $actorIds = [];
                    foreach ($cast as $person) {
                        $nameParts = explode(' ', $person['name'], 2);
                        $firstName = $nameParts[0];
                        $lastName = $nameParts[1] ?? '';

                        $actor = \App\Models\Actor::where('tmdb_id', $person['id'])->first();

                        if (!$actor) {
                            $actor = \App\Models\Actor::where('first_name', $firstName)
                                          ->where('last_name', $lastName)
                                          ->first();
                        }

                        if ($actor) {
                            if (!$actor->tmdb_id) {
                                $actor->update(['tmdb_id' => $person['id']]);
                            }
                        } else {
                            $actor = \App\Models\Actor::create([
                                'tmdb_id' => $person['id'],
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                            ]);
                        }

                        // Handle Profile Image
                        if (!empty($person['profile_path']) && empty($actor->profile_path)) {
                            try {
                                $profileUrl = "https://image.tmdb.org/t/p/w185" . $person['profile_path'];
                                $imageContent = Http::get($profileUrl)->body();
                                $filename = 'actors/' . Str::random(20) . '.jpg';
                                Storage::disk('public')->put($filename, $imageContent);
                                $actor->update(['profile_path' => $filename]);
                            } catch (\Exception $e) {}
                        }
                        
                        $actorIds[$actor->id] = [
                            'role' => $person['character'],
                            'is_main_role' => $person['order'] < 3,
                            'sort_order' => $person['order']
                        ];
                    }
                    $movie->actors()->sync($actorIds);
                }
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Get a list of movies that don't have a TMDb ID.
     */
    public function getUnlinkedMovies()
    {
        $movies = Movie::whereNull('tmdb_id')
            ->select('id', 'title', 'year')
            ->get();
            
        return response()->json($movies);
    }

    /**
     * Attempt to automatically link a movie to TMDb.
     */
    public function autoLinkMovie(Request $request)
    {
        $movieId = $request->get('movie_id');
        $movie = Movie::findOrFail($movieId);

        try {
            // Standardize Title for search
            $cleanTitle = $this->cleanTitle($movie->title);
            $year = $movie->year;

            // 1. Search as Movie with Year
            $search = $this->tmdb->searchMovie($cleanTitle, $year);
            if ($this->hasMatch($search)) {
                return $this->linkMovie($movie, $search['results'][0], 'movie');
            }

            // 2. Search as Movie WITHOUT Year (sometimes year in DB is slightly off)
            if ($year) {
                $search = $this->tmdb->searchMovie($cleanTitle);
                if ($this->hasMatch($search)) {
                    return $this->linkMovie($movie, $search['results'][0], 'movie');
                }
            }

            // 3. Search as TV Show with Year
            $searchTv = $this->tmdb->searchTv($cleanTitle, $year);
            if ($this->hasMatch($searchTv)) {
                return $this->linkMovie($movie, $searchTv['results'][0], 'tv');
            }

            // 4. Search as TV Show WITHOUT Year
            $searchTv = $this->tmdb->searchTv($cleanTitle);
            if ($this->hasMatch($searchTv)) {
                return $this->linkMovie($movie, $searchTv['results'][0], 'tv');
            }

            return response()->json(['success' => false, 'message' => 'Kein Treffer bei TMDb gefunden.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    protected function cleanTitle(string $title): string
    {
        // Remove common suffixes and prefixes that break TMDb search
        $title = preg_replace('/\s*\(.*?\)\s*/', ' ', $title); // Remove anything in parentheses
        $title = preg_replace('/\s*\[.*?\]\s*/', ' ', $title); // Remove anything in brackets
        $title = preg_replace('/\b(DVD|Blu-ray|BluRay|4K|UHD|Remastered|Steelbook)\b/i', '', $title);
        $title = trim($title);
        return $title;
    }

    protected function hasMatch(array $search): bool
    {
        return isset($search['results']) && count($search['results']) > 0;
    }

    protected function linkMovie(Movie $movie, array $tmdbResult, string $type)
    {
        $movie->update([
            'tmdb_id' => $tmdbResult['id'],
            'tmdb_type' => $type
        ]);

        $title = $tmdbResult['title'] ?? $tmdbResult['name'];
        $date = $tmdbResult['release_date'] ?? $tmdbResult['first_air_date'] ?? '';
        $year = substr($date, 0, 4);

        return response()->json([
            'success' => true, 
            'match' => "{$title} ({$year})" . ($type === 'tv' ? ' [Serie]' : '')
        ]);
    }
}
