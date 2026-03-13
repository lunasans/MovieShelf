<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Movie::query();

        if ($request->has('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        if ($request->filter === 'missing_tmdb') {
            $query->whereNull('tmdb_id');
        }

        if ($request->filter === 'missing_cover') {
            $query->whereNull('cover_id');
        }

        $movies = $query->orderBy('title')->paginate(20);

        return view('admin.movies.index', compact('movies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // To be implemented
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // To be implemented
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Movie $movie)
    {
        return view('admin.movies.edit', compact('movie'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'year' => 'required|integer',
            'collection_type' => 'required|string',
            'genre' => 'nullable|string',
            'runtime' => 'nullable|integer',
            'rating' => 'nullable|numeric|min:0|max:100',
            'rating_age' => 'nullable|integer',
            'created_at' => 'nullable|date',
            'trailer_url' => 'nullable|url',
            'overview' => 'nullable|string',
        ]);

        $movie->update($validated);

        return redirect()->route('admin.movies.index')->with('success', 'Film "' . $movie->title . '" wurde erfolgreich aktualisiert.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movie $movie)
    {
        $movie->delete();
        return redirect()->route('admin.movies.index')->with('success', 'Film gelöscht.');
    }
}
