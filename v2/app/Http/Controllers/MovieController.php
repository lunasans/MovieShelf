<?php

namespace App\Http\Controllers;
 
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::query()->whereNull('boxset_parent');

        // Search
        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        // Collection Type Filter
        if ($request->filled('type')) {
            $query->where('collection_type', $request->type);
        }

        $movies = $query->orderBy('title')->paginate(20)->withQueryString();
        
        $collectionTypes = Movie::distinct()
            ->whereNotNull('collection_type')
            ->orderBy('collection_type')
            ->pluck('collection_type');

        $latestCount = (int)\App\Models\Setting::where('key', 'latest_films_count')->value('value') ?: 15;
        $latestMovies = Movie::whereNull('boxset_parent')
            ->orderBy('id', 'desc')
            ->limit($latestCount)
            ->get();

        return view('dashboard', compact('movies', 'collectionTypes', 'latestMovies'));
    }

    public function details(Movie $movie)
    {
        $movie->load(['actors', 'boxsetChildren', 'parentBoxset']);
        return view('movies.partials.details', compact('movie'));
    }
}
