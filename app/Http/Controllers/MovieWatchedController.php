<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Support\Facades\Auth;

class MovieWatchedController extends Controller
{
    /**
     * Toggle the watched status of a movie for the authenticated user.
     *
     * Bei einem Boxset gehört die Markierung an die enthaltenen Filme: sein
     * eigener Stand wird aus ihnen abgeleitet (Movie::isWatchedBy), ihn zu
     * setzen bliebe deshalb wirkungslos.
     */
    public function toggle(Movie $movie)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $watched = $movie->setWatchedFor($user, ! $movie->isWatchedBy($user));

        return response()->json([
            'watched' => $watched,
            'count' => $movie->watchedByUsers()->count(),
        ]);
    }
}
