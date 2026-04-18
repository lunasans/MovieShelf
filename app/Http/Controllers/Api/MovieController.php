<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use Illuminate\Http\Request;

use OpenApi\Attributes as OA;

class MovieController extends Controller
{
    #[OA\Get(
        path: '/movies',
        summary: 'Filmliste abrufen (paginiert)',
        tags: ['Movies'],
        security: [['apiAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Anzahl der Filme pro Seite', required: false, schema: new OA\Schema(type: 'integer', default: 20)),
            new OA\Parameter(name: 'page', in: 'query', description: 'Seitenzahl', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'tag', in: 'query', description: 'Nach Tag filtern', required: false, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste der Filme',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object')
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', \App\Models\Setting::get('items_per_page', 20));
        $tag = $request->get('tag');

        $query = Movie::where('is_deleted', false);

        if ($tag) {
            $query->where('tag', 'like', "%{$tag}%");
        }

        $movies = $query->with(['actors'])
            ->withCount('boxsetChildren')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return MovieResource::collection($movies);
    }

    #[OA\Get(
        path: '/movies/{movie}',
        summary: 'Film-Details abrufen',
        tags: ['Movies'],
        security: [['apiAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'movie', in: 'path', description: 'Film ID', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detaillierte Filminformationen',
                content: new OA\JsonContent()
            ),
            new OA\Response(response: 404, description: 'Film nicht gefunden')
        ]
    )]
    public function show(Movie $movie)
    {
        $movie->load(['actors', 'boxsetChildren', 'watchedByUsers']);
        
        return new MovieResource($movie);
    }

    #[OA\Get(
        path: '/search',
        summary: 'Filme suchen',
        tags: ['Movies'],
        security: [['apiAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', description: 'Suchbegriff (Titel oder Regisseur)', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'tag', in: 'query', description: 'Zusätzlich nach Tag filtern', required: false, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Suchergebnisse',
                content: new OA\JsonContent()
            )
        ]
    )]
    public function search(Request $request)
    {
        $queryStr = $request->get('q');
        $tag = $request->get('tag');
        
        if (empty($queryStr) && empty($tag)) {
            return response()->json(['data' => []]);
        }

        $perPage = \App\Models\Setting::get('items_per_page', 20);
        $moviesQuery = Movie::where('is_deleted', false);

        if ($queryStr) {
            $moviesQuery->where(function($q) use ($queryStr) {
                $q->where('title', 'like', "%{$queryStr}%")
                  ->orWhere('director', 'like', "%{$queryStr}%")
                  ->orWhere('tag', 'like', "%{$queryStr}%");
            });
        }

        if ($tag) {
            $moviesQuery->where('tag', 'like', "%{$tag}%");
        }

        $movies = $moviesQuery->with(['actors'])
            ->withCount('boxsetChildren')
            ->paginate($perPage);

        return MovieResource::collection($movies);
    }

    #[OA\Post(
        path: '/movies/{movie}/watched',
        summary: 'Gesehen-Status umschalten',
        tags: ['Movies'],
        security: [['apiAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'movie', in: 'path', description: 'Film ID', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status erfolgreich geändert',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'is_watched', type: 'boolean')
                    ]
                )
            )
        ]
    )]
    public function toggleWatched(Request $request, Movie $movie)
    {
        $user = $request->user();
        
        if ($user->watchedMovies()->where('movie_id', $movie->id)->exists()) {
            $user->watchedMovies()->detach($movie->id);
            $watched = false;
        } else {
            $user->watchedMovies()->attach($movie->id);
            $watched = true;
        }

        return response()->json([
            'message' => $watched ? 'Movie marked as watched' : 'Movie marked as unwatched',
            'is_watched' => $watched
        ]);
    }
}
