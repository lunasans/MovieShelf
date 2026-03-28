<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $movies = Movie::where('is_deleted', false)
            ->with(['actors'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return MovieResource::collection($movies);
    }

    public function show(Movie $movie)
    {
        $movie->load(['actors', 'boxsetChildren', 'watchedByUsers']);
        
        return new MovieResource($movie);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $movies = Movie::where('is_deleted', false)
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('director', 'like', "%{$query}%");
            })
            ->with(['actors'])
            ->paginate(20);

        return MovieResource::collection($movies);
    }

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
