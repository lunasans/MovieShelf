<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\UserWishlist;
use Illuminate\Support\Facades\Auth;

class MovieWishlistController extends Controller
{
    public function toggle(Movie $movie)
    {
        $user = Auth::user();

        $exists = UserWishlist::where('user_id', $user->id)
            ->where('movie_id', $movie->id)
            ->exists();

        if ($exists) {
            UserWishlist::where('user_id', $user->id)
                ->where('movie_id', $movie->id)
                ->delete();
            $wishlisted = false;
        } else {
            UserWishlist::create([
                'user_id'  => $user->id,
                'movie_id' => $movie->id,
                'added_at' => now(),
            ]);
            $wishlisted = true;
        }

        return response()->json(['wishlisted' => $wishlisted]);
    }
}
