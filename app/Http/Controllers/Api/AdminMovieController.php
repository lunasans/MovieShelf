<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminMovieController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'year'            => 'required|integer',
            'collection_type' => 'required|string',
            'tag'             => 'nullable|string|max:50',
            'genre'           => 'nullable|string',
            'runtime'         => 'nullable|integer',
            'rating'          => 'nullable|numeric|min:0|max:100',
            'rating_age'      => 'nullable|integer',
            'overview'        => 'nullable|string',
            'director'        => 'nullable|string|max:255',
            'trailer_url'     => 'nullable|url',
            'tmdb_id'         => 'nullable|integer',
            'cover_id'        => 'nullable|string',
            'backdrop_id'     => 'nullable|string',
            'in_collection'   => 'nullable|boolean',
            'edition'         => 'nullable|string|max:120',
            'region_code'     => 'nullable|string|max:20',
            'disc_location'   => 'nullable|string|max:120',
            'purchase_date'   => 'nullable|date',
            'purchase_price'  => 'nullable|numeric|min:0|max:999999',
            'condition'       => 'nullable|in:new,like_new,good,acceptable,damaged',
        ]);

        $validated['user_id'] = $request->user()->id;

        $this->handleImageDownloads($validated);

        $movie = Movie::create($validated);

        return new MovieResource($movie->load('actors'));
    }

    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'year'            => 'required|integer',
            'collection_type' => 'required|string',
            'tag'             => 'nullable|string|max:50',
            'genre'           => 'nullable|string',
            'runtime'         => 'nullable|integer',
            'rating'          => 'nullable|numeric|min:0|max:100',
            'rating_age'      => 'nullable|integer',
            'overview'        => 'nullable|string',
            'director'        => 'nullable|string|max:255',
            'trailer_url'     => 'nullable|url',
            'tmdb_id'         => 'nullable|integer',
            'cover_id'        => 'nullable|string',
            'backdrop_id'     => 'nullable|string',
            'in_collection'   => 'nullable|boolean',
            'edition'         => 'nullable|string|max:120',
            'region_code'     => 'nullable|string|max:20',
            'disc_location'   => 'nullable|string|max:120',
            'purchase_date'   => 'nullable|date',
            'purchase_price'  => 'nullable|numeric|min:0|max:999999',
            'condition'       => 'nullable|in:new,like_new,good,acceptable,damaged',
        ]);

        $this->handleImageDownloads($validated);

        $movie->update($validated);

        return new MovieResource($movie->load('actors'));
    }

    public function destroy(Movie $movie)
    {
        $movie->update(['is_deleted' => true, 'deleted_at' => now()]);

        return response()->json(['message' => 'Film wurde gelöscht.']);
    }

    /**
     * Trailer für einen Film via TMDb (Fallback YouTube-Suche) holen und speichern.
     */
    public function fetchTrailer(
        Movie $movie,
        \App\Services\TmdbService $tmdb,
        \App\Services\YouTubeSearchService $youtube
    ) {
        $trailerUrl = null;

        try {
            if ($movie->tmdb_id) {
                $details = $tmdb->getMovieDetails((int) $movie->tmdb_id);
                $trailerUrl = $this->pickTrailer($details['videos']['results'] ?? []);
            }
        } catch (\Throwable $e) {
            Log::warning("fetchTrailer TMDb failed for movie {$movie->id}: " . $e->getMessage());
        }

        if (! $trailerUrl) {
            $trailerUrl = $youtube->searchTrailer($movie->title, $movie->year);
        }

        if ($trailerUrl) {
            $movie->update(['trailer_url' => $trailerUrl]);
        }

        return response()->json([
            'trailer_url' => $trailerUrl,
            'found'       => (bool) $trailerUrl,
        ]);
    }

    private function pickTrailer(array $videos): ?string
    {
        $vids = collect($videos)->where('site', 'YouTube');
        $t = $vids->where('type', 'Trailer')->where('iso_639_1', 'de')->first()
            ?? $vids->where('type', 'Trailer')->where('iso_639_1', 'en')->first()
            ?? $vids->where('type', 'Trailer')->first()
            ?? $vids->where('type', 'Teaser')->first();

        if (! $t || empty($t['key'])) {
            return null;
        }
        return 'https://www.youtube.com/watch?v=' . $t['key'];
    }

    public function uploadCover(Request $request, Movie $movie)
    {
        $request->validate([
            'cover' => 'required|image|max:4096',
        ]);

        $file = $request->file('cover');
        // Str::random statt time(): zwei Uploads in derselben Sekunde ueberschrieben
        // einander sonst, und der zweite Film zeigte das Cover des ersten.
        $filename = 'covers/custom_' . Str::random(20) . '.' . $file->guessExtension();
        $file->storeAs('', $filename, 'public');

        $movie->update(['cover_id' => $filename]);

        return response()->json([
            'message'   => 'Cover hochgeladen.',
            'cover_url' => $movie->fresh()->cover_url,
        ]);
    }

    public function uploadBackdrop(Request $request, Movie $movie)
    {
        $request->validate([
            'backdrop' => 'required|image|max:10240',
        ]);

        $file = $request->file('backdrop');
        $filename = 'backdrops/custom_' . Str::random(20) . '.' . $file->guessExtension();
        $file->storeAs('', $filename, 'public');

        $movie->update(['backdrop_id' => $filename]);

        return response()->json([
            'message'      => 'Backdrop hochgeladen.',
            'backdrop_url' => $movie->fresh()->backdrop_url,
        ]);
    }

    public function export(Request $request)
    {
        $since = $request->query('since');

        // Wasserzeichen VOR der Query erfassen: so liegt `exported_at` garantiert
        // vor (oder gleich) dem Datenstand. Der Client nutzt es als nächstes `since`
        // (>=), wodurch keine im Mikro-Fenster zwischen Query und Antwort geänderten
        // Datensätze verloren gehen (höchstens harmlose Re-Syncs).
        $exportedAt = now()->toIso8601String();

        $userId = $request->user()->id;
        // Bewertung und Folgen-Gesehen-Stand haengen am Nutzer, nicht am Film.
        // Beide Relationen werden gleich hier auf ihn gefiltert geladen — sonst
        // stuende je Film bzw. je Folge eine eigene Abfrage an, und eine Serie
        // hat schnell hundert Folgen.
        $query = Movie::with([
                'actors',
                'seasons.episodes',
                'seasons.episodes.watchedByUsers' => fn($q) => $q->where('users.id', $userId),
                'userRatings'      => fn($q) => $q->where('user_id', $userId),
                'watchedByUsers'   => fn($q) => $q->where('users.id', $userId),
            ])
            ->withCount('boxsetChildren')
            ->orderBy('title');

        if ($since) {
            // Delta: alle seit `since` geänderten Einträge – auch gelöschte
            $sinceDate = \Carbon\Carbon::parse($since)->utc();
            $query->where('updated_at', '>=', $sinceDate);
        }
        // Vollsync (kein `since`): bewusst KEIN Filter auf is_deleted/in_collection.
        // Clients müssen zwischen "gelöscht" (is_deleted) und "nicht in Sammlung"
        // (in_collection) unterscheiden können - beide Felder liefert die
        // MovieResource mit. Würden wir hier schon rausfiltern, sähe eine bloß
        // nicht-gesammelte Zeile für den Client identisch aus wie eine gelöschte
        // ("fehlt im Export") und würde fälschlich hart lokal entfernt.

        $movies = $query->get();

        return response()->json([
            'exported_at' => $exportedAt,
            'is_delta'    => (bool) $since,
            'since'       => $since,
            'count'       => $movies->where('is_deleted', false)->count(),
            'movies'      => MovieResource::collection($movies),
        ]);
    }

    protected function handleImageDownloads(array &$validated): void
    {
        if (! empty($validated['cover_id']) && str_starts_with($validated['cover_id'], '/')) {
            $filename = $this->downloadTmdbImage($validated['cover_id'], 'w500', 'covers');
            if ($filename) {
                $validated['cover_id'] = $filename;
            }
        }

        if (! empty($validated['backdrop_id']) && str_starts_with($validated['backdrop_id'], '/')) {
            $filename = $this->downloadTmdbImage($validated['backdrop_id'], 'w1280', 'backdrops');
            if ($filename) {
                $validated['backdrop_id'] = $filename;
            }
        }
    }

    protected function downloadTmdbImage(string $path, string $size, string $folder): ?string
    {
        try {
            $response = Http::get("https://image.tmdb.org/t/p/{$size}" . $path);
            if ($response->successful()) {
                $prefix = $folder === 'backdrops' ? 'tmdb_backdrop_' : 'tmdb_';
                $filename = $prefix . ltrim($path, '/');
                Storage::disk('public')->put($folder . '/' . $filename, $response->body());
                return $folder . '/' . $filename;
            }
        } catch (\Exception $e) {
            Log::error("Failed to download TMDb image ({$folder}): " . $e->getMessage());
        }
        return null;
    }
}
