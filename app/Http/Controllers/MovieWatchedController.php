<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieWatchedController extends Controller
{
    /**
     * Toggle the watched status of a movie for the authenticated user.
     */
    public function toggle(Movie $movie)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $isWatched = $user->watchedMovies()->where('movie_id', $movie->id)->exists();

        if ($isWatched) {
            $user->watchedMovies()->detach($movie->id);
            $watched = false;
        } else {
            $user->watchedMovies()->attach($movie->id);
            $watched = true;
        }

        return response()->json([
            'watched' => $watched,
            'count' => $movie->watchedByUsers()->count()
        ]);
    }
}
