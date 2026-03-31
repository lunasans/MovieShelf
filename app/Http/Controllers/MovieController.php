<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Setting;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::query();

        if (! $request->filled('q') && ! $request->filled('type')) {
            $query->whereNull('boxset_parent');
        }

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->q.'%');
        }

        if ($request->filled('type')) {
            $query->where('collection_type', $request->type);
        }

        $perPage = Setting::get('items_per_page', 20);
        $movies = $query->withCount('boxsetChildren')->orderBy('title')->paginate($perPage)->withQueryString();
        $collectionTypes = Movie::distinct()->whereNotNull('collection_type')->orderBy('collection_type')->pluck('collection_type');

        $latestCount = (int) Setting::where('key', 'latest_films_count')->value('value') ?: 15;
        $latestMovies = Movie::whereNull('boxset_parent')->withCount('boxsetChildren')->orderBy('created_at', 'desc')->limit($latestCount)->get();


        $genreRows = []; // Genre rows removed in favor of a unified grid

        $featuredMovies = Movie::whereNotNull('backdrop_url')->whereNull('boxset_parent')->inRandomOrder()->limit(5)->get();
        if ($featuredMovies->isEmpty()) {
            $featuredMovies = Movie::whereNull('boxset_parent')->latest()->limit(1)->get();
        }

        $defaultViewMode = Setting::get('default_view_mode', 'grid');
        $viewMode = $request->get('view', $defaultViewMode);

        if ($request->ajax()) {
            if (auth()->user()->layout === 'streaming') {
                return view('movies.partials.streaming-movie-list-ajax', compact('movies'))->render();
            }
            return view('movies.partials.movie-list-ajax', compact('movies', 'viewMode'))->render();
        }

        return view('dashboard', compact(
            'movies',
            'collectionTypes',
            'latestMovies',
            'defaultViewMode',
            'genreRows',
            'featuredMovies'
        ));
    }

    public function show(Movie $movie)
    {
        $movie->load(['actors', 'boxsetChildren', 'parentBoxset', 'seasons.episodes']);
        $layoutMode = auth()->check() ? optional(auth()->user())->layout : 'classic';

        return view('movies.show', compact('movie', 'layoutMode'));
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
                ->where('genre', 'like', '%'.$firstGenre.'%')
                ->inRandomOrder()
                ->limit(5)
                ->get();
        }

        return view('movies.partials.details', compact('movie', 'similarMovies'));
    }

    public function random(Request $request)
    {
        $query = Movie::query()->whereNull('boxset_parent');

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->q.'%');
        }

        if ($request->filled('type')) {
            $query->where('collection_type', $request->type);
        }

        $movie = $query->inRandomOrder()->first();

        if (! $movie) {
            return response()->json(['error' => 'No movies found'], 404);
        }

        return response()->json([
            'id' => $movie->id,
            'backdrop_url' => $movie->backdrop_url,
        ]);
    }

    public function boxset(Movie $movie)
    {
        $movie->load('boxsetChildren');

        return response()->json([
            'parent_title' => $movie->title,
            'children' => $movie->boxsetChildren->map(function ($child) {
                return [
                    'id' => $child->id,
                    'title' => $child->title,
                    'year' => $child->year,
                    'cover_url' => $child->cover_url,
                    'details_url' => route('movies.show', $child->id),
                ];
            }),
        ]);
    }
}
