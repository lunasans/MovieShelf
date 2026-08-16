<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExternalMovieResource;
use App\Models\ExternalMovie;
use App\Services\TmdbImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExternalMovieController extends Controller
{
    /**
     * Externen Film (nicht in der Sammlung) anlegen – per TMDb-ID oder direktem Payload.
     * Wird von Clients genutzt, um Listen-Items zu erzeugen, die nicht zur Sammlung gehören.
     */
    public function store(Request $request, TmdbImportService $tmdb)
    {
        $validated = $request->validate([
            'tmdb_id'         => 'nullable|integer',
            'type'            => 'nullable|in:movie,tv',
            'title'           => 'required_without:tmdb_id|string|max:255',
            'year'            => 'nullable|integer',
            'genre'           => 'nullable|string',
            'director'        => 'nullable|string|max:255',
            'runtime'         => 'nullable|integer',
            'rating'          => 'nullable|numeric',
            'rating_age'      => 'nullable|integer',
            'overview'        => 'nullable|string',
            'collection_type' => 'nullable|string|max:100',
            'trailer_url'     => 'nullable|url',
            'cover_id'        => 'nullable|string',
            'backdrop_id'     => 'nullable|string',
        ]);

        $userId = $request->user()->id;

        try {
            if (! empty($validated['tmdb_id'])) {
                // Bereits vorhandenen externen Film desselben Users wiederverwenden (keine Duplikate).
                $existing = ExternalMovie::where('tmdb_id', $validated['tmdb_id'])
                    ->where('user_id', $userId)
                    ->first();
                if ($existing) {
                    return new ExternalMovieResource($existing);
                }

                $external = $tmdb->createExternalFromTmdb(
                    (int) $validated['tmdb_id'],
                    $validated['type'] ?? 'movie',
                    $userId
                );
            } else {
                $validated['user_id'] = $userId;
                unset($validated['type']);
                $external = ExternalMovie::create($validated);
            }
        } catch (\Throwable $e) {
            Log::error('ExternalMovie anlegen fehlgeschlagen: '.$e->getMessage());

            return response()->json(['message' => 'Anlegen fehlgeschlagen: '.$e->getMessage()], 500);
        }

        return new ExternalMovieResource($external);
    }
}
