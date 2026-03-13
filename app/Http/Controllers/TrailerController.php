<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class TrailerController extends Controller
{
    /**
     * Display a gallery of movie trailers.
     */
    public function index(Request $request)
    {
        $query = $request->get('q');

        $movies = Movie::query()
            ->whereNotNull('trailer_url')
            ->where('trailer_url', '!=', '')
            ->where('is_deleted', false)
            ->when($query, function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%");
            })
            ->orderBy('year', 'desc')
            ->orderBy('title', 'asc')
            ->paginate(24);

        if ($request->ajax()) {
            return view('trailers.partials.movie-card-list', compact('movies'))->render();
        }

        return view('trailers.index', compact('movies'));
    }
}
