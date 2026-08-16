<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\UserRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieRatingController extends Controller
{
    /**
     * Eigene Bewertung setzen — oder mit 0 wieder entfernen.
     *
     * Bisher galt min:1, es gab also keinen Weg zurueck auf "noch nicht
     * bewertet": ein Client konnte eine einmal abgegebene Bewertung nur noch
     * aendern. Die Weboberflaeche schickt weiterhin 1-5, die 0 ist der
     * ausdrueckliche Loeschweg fuer Clients wie den Desktop, die das Entfernen
     * anbieten.
     */
    public function store(Request $request, Movie $movie)
    {
        $request->validate(['rating' => 'required|integer|min:0|max:5']);

        $rating = (int) $request->rating;

        if ($rating === 0) {
            UserRating::where('user_id', Auth::id())
                ->where('movie_id', $movie->id)
                ->delete();
        } else {
            UserRating::updateOrCreate(
                ['user_id' => Auth::id(), 'movie_id' => $movie->id],
                ['rating' => $rating]
            );
        }

        // Wie bei Movie::setWatchedFor(): die Bewertung lebt in `user_ratings`,
        // ein Schreiben dort laesst `movies.updated_at` unberueht. Der
        // Delta-Export filtert aber genau darauf — eine geaenderte Bewertung
        // fiel deshalb aus jedem Delta-Abgleich heraus und erreichte Desktop-
        // und Android-App nie. Nur ein Voll-Abgleich brachte sie mit.
        $movie->touch();

        $avg   = UserRating::where('movie_id', $movie->id)->avg('rating');
        $count = UserRating::where('movie_id', $movie->id)->count();

        return response()->json([
            'rating' => $rating === 0 ? null : $rating,
            'avg'    => round($avg, 1),
            'count'  => $count,
        ]);
    }
}
