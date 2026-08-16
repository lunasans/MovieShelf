<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
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

    /**
     * Eine einzelne Staffel samt Episoden ausliefern.
     *
     * Für Clients, die den Import selbst vornehmen: die Desktop-App legt
     * Filme und Serien lokal an und schiebt sie erst beim Abgleich hoch,
     * braucht die Episoden also im Klartext. Ohne diesen Endpunkt bliebe für
     * Serien-Importe ein eigener TMDb-Schlüssel im Client nötig, obwohl der
     * Schlüssel der Shelf längst hinterlegt ist.
     */
    public function season(Request $request)
    {
        $data = $request->validate([
            'tmdb_id' => 'required|integer',
            'season'  => 'required|integer|min:0',
        ]);

        try {
            return response()->json($this->tmdb->getSeasonDetails($data['tmdb_id'], $data['season']));
        } catch (\Exception $e) {
            Log::error('API TmdbSeason Error: '.$e->getMessage());

            return response()->json(['error' => 'Fehler beim Abrufen der Staffel'], 500);
        }
    }

    /**
     * Staffeln für eine bestehende Serie nachladen (Desktop-/Android-App).
     * Vorhandene Staffelnummern überspringt der Service.
     */
    public function importSeasons(Request $request)
    {
        $request->validate([
            'movie_id'  => 'required|integer',
            'seasons'   => 'required|array|min:1',
            'seasons.*' => 'integer|min:1',
        ]);

        $movie = Movie::findOrFail($request->get('movie_id'));

        if ($movie->collection_type !== 'Serie' || ! $movie->tmdb_id) {
            return response()->json(['error' => 'Dieser Eintrag ist keine mit TMDb verknüpfte Serie.'], 422);
        }

        try {
            $imported = $this->importService->importSeasonsForExisting($movie, $request->get('seasons'));

            return response()->json(['success' => true, 'imported' => $imported]);
        } catch (\Exception $e) {
            Log::error('API TmdbImportSeasons Error: '.$e->getMessage());

            return response()->json(['error' => 'Fehler beim Import: '.$e->getMessage()], 500);
        }
    }

    /**
     * Staffeln einer bestehenden Serie entfernen (Desktop-/Android-App).
     * Gegenstück zu importSeasons; Episoden cascaden auf DB-Ebene.
     */
    public function removeSeasons(Request $request)
    {
        $request->validate([
            'movie_id'  => 'required|integer',
            'seasons'   => 'required|array|min:1',
            'seasons.*' => 'integer|min:1',
        ]);

        $movie = Movie::findOrFail($request->get('movie_id'));

        if ($movie->collection_type !== 'Serie') {
            return response()->json(['error' => 'Dieser Eintrag ist keine Serie.'], 422);
        }

        try {
            $removed = $this->importService->removeSeasonsForExisting($movie, $request->get('seasons'));

            return response()->json(['success' => true, 'removed' => $removed]);
        } catch (\Exception $e) {
            Log::error('API TmdbRemoveSeasons Error: '.$e->getMessage());

            return response()->json(['error' => 'Fehler beim Entfernen: '.$e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'tmdb_id'      => 'required|integer',
            'type'         => 'required|string|in:movie,tv',
            'seasons'      => 'nullable|array',
            'in_collection' => 'nullable|boolean',
        ]);

        $tmdbId      = $request->get('tmdb_id');
        $type        = $request->get('type');
        // Nach dem Datenmodell-Split ist /tmdb/import ausschließlich Sammlungs-Import.
        // Nicht-besessene Filme laufen über POST /external-movies (ExternalMovie).
        $inCollection = true;

        try {
            if ($type === 'tv') {
                $movie = $this->importService->importTv((int) $tmdbId, $request->get('seasons', []), $inCollection);
            } else {
                $movie = $this->importService->importMovie((int) $tmdbId, $inCollection);
            }

            $wasUpdated = !($movie->wasRecentlyCreated);

            return (new MovieResource($movie))->additional([
                'meta' => [
                    'is_updated' => $wasUpdated,
                    'message' => $wasUpdated 
                        ? "Film/Serie '{$movie->title}' existierte bereits und wurde aktualisiert." 
                        : "Import erfolgreich: '{$movie->title}'"
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('API TmdbImport Error: '.$e->getMessage());
            return response()->json(['error' => 'Fehler beim Import: '.$e->getMessage()], 500);
        }
    }
}
