<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Services\TmdbService;
use App\Services\TmdbImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TmdbController extends Controller
{
    protected TmdbService $tmdb;
    protected TmdbImportService $importService;

    public function __construct(TmdbService $tmdb, TmdbImportService $importService)
    {
        $this->tmdb = $tmdb;
        $this->importService = $importService;
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        $type = $request->get('type', 'movie');

        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        try {
            $results = ($type === 'tv') ? $this->tmdb->searchTv($query) : $this->tmdb->searchMovie($query);
            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('API TmdbSearch Error: '.$e->getMessage());
            return response()->json(['error' => 'Fehler bei der Suche'], 500);
        }
    }

    public function details(Request $request)
    {
        $tmdbId = $request->get('tmdb_id');
        $type = $request->get('type', 'movie');

        if (! $tmdbId) {
            return response()->json(['error' => 'Keine TMDb ID angegeben'], 400);
        }

        try {
            $details = ($type === 'tv') ? $this->tmdb->getTvDetails($tmdbId) : $this->tmdb->getMovieDetails($tmdbId);
            return response()->json($details);
        } catch (\Exception $e) {
            Log::error('API TmdbDetails Error: '.$e->getMessage());
            return response()->json(['error' => 'Fehler beim Abrufen der Details'], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'tmdb_id' => 'required|integer',
            'type' => 'required|string|in:movie,tv',
            'seasons' => 'nullable|array',
        ]);

        $tmdbId = $request->get('tmdb_id');
        $type = $request->get('type');

        try {
            if ($type === 'tv') {
                $movie = $this->importService->importTv((int) $tmdbId, $request->get('seasons', []));
            } else {
                $movie = $this->importService->importMovie((int) $tmdbId);
            }

            return new MovieResource($movie);
        } catch (\Exception $e) {
            Log::error('API TmdbImport Error: '.$e->getMessage());
            return response()->json(['error' => 'Fehler beim Import: '.$e->getMessage()], 500);
        }
    }
}
