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
        ]);

        $this->handleImageDownloads($validated);

        $movie->update($validated);

        return new MovieResource($movie->load('actors'));
    }

    public function destroy(Movie $movie)
    {
        $movie->update(['is_deleted' => true]);

        return response()->json(['message' => 'Film wurde gelöscht.']);
    }

    public function uploadCover(Request $request, Movie $movie)
    {
        $request->validate([
            'cover' => 'required|image|max:4096',
        ]);

        $file = $request->file('cover');
        $filename = 'covers/custom_' . time() . '.' . $file->guessExtension();
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
        $filename = 'backdrops/custom_' . time() . '.' . $file->guessExtension();
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

        $userId = $request->user()->id;
        $query = Movie::with(['actors', 'watchedByUsers' => fn($q) => $q->where('users.id', $userId)])
            ->withCount('boxsetChildren')
            ->orderBy('title');

        if ($since) {
            // Delta: alle seit `since` geänderten Einträge – auch gelöschte
            $sinceDate = \Carbon\Carbon::parse($since)->utc();
            $query->where('updated_at', '>=', $sinceDate)
                  ->where('in_collection', true);
        } else {
            // Vollsync: nur nicht-gelöschte und in der Sammlung vorhandene
            $query->where('is_deleted', false)
                  ->where('in_collection', true);
        }

        $movies = $query->get();

        return response()->json([
            'exported_at' => now()->toIso8601String(),
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
