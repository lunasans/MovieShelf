<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\MovieList;
use App\Services\TmdbImportService;
use Illuminate\Http\Request;

class ListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $lists = MovieList::where('user_id', auth()->id())
            ->withCount('movies')
            ->orderBy('name')
            ->get();

        return view('tenant.lists.index', compact('lists'));
    }

    public function show(MovieList $list)
    {
        $this->authorizeList($list);
        $list->load('movies');

        return view('tenant.lists.show', compact('list'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $list = MovieList::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
        ]);

        return redirect()->route('lists.show', $list)->with('success', "Liste \"{$list->name}\" erstellt.");
    }

    public function update(Request $request, MovieList $list)
    {
        $this->authorizeList($list);
        $request->validate(['name' => 'required|string|max:255']);

        $list->update(['name' => $request->name]);

        return back()->with('success', 'Liste umbenannt.');
    }

    public function destroy(MovieList $list)
    {
        $this->authorizeList($list);

        $this->cleanupListOnlyMovies($list);
        $list->delete();

        return redirect()->route('lists.index')->with('success', 'Liste gelöscht.');
    }

    public function addMovie(Request $request, MovieList $list)
    {
        $this->authorizeList($list);
        $request->validate(['movie_id' => 'required|integer|exists:movies,id']);

        $list->movies()->syncWithoutDetaching([$request->movie_id => ['added_at' => now()]]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Film zur Liste hinzugefügt.');
    }

    public function removeMovie(Request $request, MovieList $list, Movie $movie)
    {
        $this->authorizeList($list);

        $list->movies()->detach($movie->id);

        // Remove movie entirely if it's list-only and has no remaining lists
        if (! $movie->in_collection) {
            $remaining = $movie->lists()->count();
            if ($remaining === 0) {
                $movie->delete();
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Film von Liste entfernt.');
    }

    public function importFromTmdb(Request $request, MovieList $list, TmdbImportService $importService)
    {
        $this->authorizeList($list);
        $request->validate(['tmdb_id' => 'required|integer']);

        $tmdbId = (int) $request->tmdb_id;

        // Check if already exists in DB
        $movie = Movie::where('tmdb_id', $tmdbId)->first();

        if (! $movie) {
            $movie = $importService->importMovie($tmdbId, inCollection: false);
        }

        $list->movies()->syncWithoutDetaching([$movie->id => ['added_at' => now()]]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'movie_id' => $movie->id]);
        }

        return back()->with('success', "\"{$movie->title}\" zur Liste hinzugefügt.");
    }

    private function authorizeList(MovieList $list): void
    {
        abort_unless($list->user_id === auth()->id(), 403);
    }

    private function cleanupListOnlyMovies(MovieList $list): void
    {
        $listOnlyMovies = $list->movies()->where('in_collection', false)->get();

        foreach ($listOnlyMovies as $movie) {
            $otherLists = $movie->lists()->where('lists.id', '!=', $list->id)->count();
            if ($otherLists === 0) {
                $movie->delete();
            }
        }
    }
}
