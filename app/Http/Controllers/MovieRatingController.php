<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\UserRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieRatingController extends Controller
{
    public function store(Request $request, Movie $movie)
    {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);

        UserRating::updateOrCreate(
            ['user_id' => Auth::id(), 'movie_id' => $movie->id],
            ['rating' => $request->rating]
        );

        $avg   = UserRating::where('movie_id', $movie->id)->avg('rating');
        $count = UserRating::where('movie_id', $movie->id)->count();

        return response()->json([
            'rating' => $request->rating,
            'avg'    => round($avg, 1),
            'count'  => $count,
        ]);
    }
}
