<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::query();

        if (!$request->filled('q') && !$request->filled('type')) {
            $query->whereNull('boxset_parent');
        }

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

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
            ->orderBy('created_at', 'desc')
            ->limit($latestCount)
            ->get();

        $defaultViewMode = \App\Models\Setting::get('default_view_mode', 'grid');

        if ($request->ajax()) {
            return view('movies.partials.movie-list-ajax', compact('movies'))->render();
        }

        return view('dashboard', compact('movies', 'collectionTypes', 'latestMovies', 'defaultViewMode'));
    }

    public function show(Movie $movie)
    {
        $movie->load(['actors', 'boxsetChildren', 'parentBoxset', 'seasons.episodes']);
        return view('movies.show', compact('movie'));
    }

    public function details(Movie $movie)
    {
        $movie->load(['actors', 'boxsetChildren', 'parentBoxset', 'seasons.episodes']);
        
        // Fetch up to 5 similar movies based on genre
        $similarMovies = collect();
        if ($movie->genre) {
            $firstGenre = trim(explode(',', $movie->genre)[0]);
            $similarMovies = Movie::where('id', '!=', $movie->id)
                ->whereNull('boxset_parent')
                ->where('genre', 'like', '%' . $firstGenre . '%')
                ->inRandomOrder()
                ->limit(5)
                ->get();
        }
        
        return view('movies.partials.details', compact('movie', 'similarMovies'));
    }
}
