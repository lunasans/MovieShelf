<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Setting;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        // Redirect to full-page movie details in streaming mode if a movie is selected
        if ($request->filled('movie')) {
            $currentLayout = auth()->check() 
                ? auth()->user()->layout 
                : \App\Models\Setting::get('default_guest_layout', 'classic');

            if ($currentLayout === 'streaming') {
                return redirect()->route('movies.show', ['movie' => $request->movie]);
            }
        }

        $query = Movie::query()->where('in_collection', true);

        $hasFilters = $request->hasAny(['q', 'type', 'genre', 'year_from', 'year_to', 'rating_min', 'runtime_max']);
        if (! $hasFilters) {
            $query->whereNull('boxset_parent');
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($w) use ($q) {
                $w->where('title', 'like', '%'.$q.'%')
                  ->orWhere('genre', 'like', '%'.$q.'%')
                  ->orWhere('actors_names', 'like', '%'.$q.'%')
                  ->orWhere('director', 'like', '%'.$q.'%');
            });
        }

        if ($request->filled('type')) {
            $query->where('collection_type', $request->type);
        }

        if ($request->filled('genre')) {
            $query->where('genre', 'like', '%' . $request->genre . '%');
        }

        if ($request->filled('year_from')) {
            $query->where('year', '>=', (int) $request->year_from);
        }

        if ($request->filled('year_to')) {
            $query->where('year', '<=', (int) $request->year_to);
        }

        if ($request->filled('rating_min')) {
            $query->whereNotNull('rating')->where('rating', '>=', (float) $request->rating_min);
        }

        if ($request->filled('runtime_max')) {
            $query->whereNotNull('runtime')->where('runtime', '<=', (int) $request->runtime_max);
        }

        $perPage = Setting::get('items_per_page', 20);
        $movies = $query->withCount('boxsetChildren')->orderBy('title')->paginate($perPage)->withQueryString();
        $collectionTypes = Movie::where('in_collection', true)->distinct()->whereNotNull('collection_type')->orderBy('collection_type')->pluck('collection_type');

        $genres = Movie::where('in_collection', true)->whereNotNull('genre')->pluck('genre')
            ->flatMap(fn($g) => array_map('trim', explode(',', $g)))
            ->filter()->unique()->sort()->values();

        $latestCount = (int) Setting::where('key', 'latest_films_count')->value('value') ?: 15;
        $latestMovies = Movie::where('in_collection', true)->whereNull('boxset_parent')->withCount('boxsetChildren')->orderBy('created_at', 'desc')->limit($latestCount)->get();

        $genreRows = [];

        $featuredMovies = Movie::where('in_collection', true)->whereNotNull('backdrop_url')->whereNull('boxset_parent')->inRandomOrder()->limit(5)->get();
        if ($featuredMovies->isEmpty()) {
            $featuredMovies = Movie::where('in_collection', true)->whereNull('boxset_parent')->latest()->limit(1)->get();
        }

        $defaultViewMode = Setting::get('default_view_mode', 'grid');
        $viewMode = $request->get('view', $defaultViewMode);

        if ($request->ajax()) {
            $currentLayout = auth()->check() 
                ? auth()->user()->layout 
                : Setting::get('default_guest_layout', 'classic');

            if ($currentLayout === 'streaming') {
                return view('tenant.movies.partials.streaming-movie-list-ajax', compact('movies'))->render();
            }
            return view('tenant.movies.partials.movie-list-ajax', compact('movies', 'viewMode'))->render();
        }

        return view('tenant.dashboard', compact(
            'movies',
            'collectionTypes',
            'genres',
            'latestMovies',
            'defaultViewMode',
            'genreRows',
            'featuredMovies'
        ));
    }

    public function show(Movie $movie)
    {
        $movie->load(['actors', 'boxsetChildren', 'parentBoxset', 'seasons.episodes']);
        $layoutMode = auth()->check() 
            ? auth()->user()->layout 
            : Setting::get('default_guest_layout', 'classic');

        return view('tenant.movies.show', compact('movie', 'layoutMode'));
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

        $layoutMode = auth()->check() 
            ? auth()->user()->layout 
            : \App\Models\Setting::get('default_guest_layout', 'classic');

        return view('tenant.movies.partials.details', compact('movie', 'similarMovies', 'layoutMode'));
    }

    public function random(Request $request)
    {
        $query = Movie::query()->where('in_collection', true)->whereNull('boxset_parent');

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->q.'%');
        }

        if ($request->filled('type')) {
            $query->where('collection_type', $request->type);
        }

        if ($request->filled('genre')) {
            $query->where('genre', 'like', '%' . $request->genre . '%');
        }

        if ($request->filled('year_from')) {
            $query->where('year', '>=', (int) $request->year_from);
        }

        if ($request->filled('year_to')) {
            $query->where('year', '<=', (int) $request->year_to);
        }

        if ($request->filled('rating_min')) {
            $query->whereNotNull('rating')->where('rating', '>=', (float) $request->rating_min);
        }

        if ($request->filled('runtime_max')) {
            $query->whereNotNull('runtime')->where('runtime', '<=', (int) $request->runtime_max);
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
