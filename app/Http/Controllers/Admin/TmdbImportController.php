<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Actor;
use App\Models\Movie;
use App\Services\TmdbService;
use App\Services\TmdbImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TmdbImportController extends Controller
{
    protected TmdbService $tmdb;

    protected TmdbImportService $importService;

    public function __construct(TmdbService $tmdb, TmdbImportService $importService)
    {
        $this->tmdb = $tmdb;
        $this->importService = $importService;
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

        if (! $tmdbId) {
            return response()->json(['error' => 'Keine ID angegeben'], 400);
        }

        $details = ($type === 'tv') ? $this->tmdb->getTvDetails($tmdbId) : $this->tmdb->getMovieDetails($tmdbId);

        return response()->json($details);
    }

    public function import(Request $request)
    {
        $tmdbId = $request->get('tmdb_id');
        $mediaType = $request->get('media_type', 'movie');

        if (! $tmdbId) {
            return back()->with('error', 'Keine TMDb ID angegeben.');
        }

        try {
            if ($mediaType === 'tv') {
                $movie = $this->importService->importTv((int) $tmdbId, $request->get('seasons', []));
                $wasUpdated = !($movie->wasRecentlyCreated);
                $this->logActivity($movie, $wasUpdated ? 'SERIES_UPDATE' : 'SERIES_IMPORT', ['media_type' => 'tv', 'tmdb_id' => $tmdbId]);
            } else {
                $movie = $this->importService->importMovie((int) $tmdbId);
                $wasUpdated = !($movie->wasRecentlyCreated);
                $this->logActivity($movie, $wasUpdated ? 'MOVIE_UPDATE' : 'MOVIE_IMPORT', ['media_type' => 'movie', 'tmdb_id' => $tmdbId]);
            }

            $msg = $wasUpdated 
                ? "Film/Serie '{$movie->title}' existierte bereits und wurde aktualisiert." 
                : "Import erfolgreich: '{$movie->title}'";

            return redirect()->route('admin.movies.index')->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'Fehler beim Import: '.$e->getMessage());
        }
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

        try {
            $this->importService->bulkUpdate($movie);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
            $cleanTitle = $this->importService->cleanTitle($movie->title);
            $year = $movie->year;

            $searchMovie = $this->tmdb->searchMovie($cleanTitle, $year);
            if ($this->hasMatch($searchMovie)) {
                $response = $this->linkAndRedirect($movie, $searchMovie['results'][0], 'movie');
            } else {
                $searchTv = $this->tmdb->searchTv($cleanTitle, $year);
                if ($this->hasMatch($searchTv)) {
                    $response = $this->linkAndRedirect($movie, $searchTv['results'][0], 'tv');
                } else {
                    $response = response()->json(['success' => false, 'message' => 'Kein Treffer bei TMDb gefunden.']);
                }
            }

            return $response;
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    protected function hasMatch(array $search): bool
    {
        return isset($search['results']) && count($search['results']) > 0;
    }

    protected function linkAndRedirect(Movie $movie, array $tmdbResult, string $type)
    {
        $movie->update([
            'tmdb_id' => $tmdbResult['id'],
            'tmdb_type' => $type,
        ]);

        $title = $tmdbResult['title'] ?? $tmdbResult['name'];
        $year = substr($tmdbResult['release_date'] ?? $tmdbResult['first_air_date'] ?? '', 0, 4);

        return response()->json([
            'success' => true,
            'match' => "{$title} ({$year})".($type === 'tv' ? ' [Serie]' : ''),
        ]);
    }

    protected function logActivity(Movie $movie, string $action, array $extraDetails = [])
    {
        $details = array_merge(['movie_id' => $movie->id, 'title' => $movie->title], $extraDetails);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => json_encode($details),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
