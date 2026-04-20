<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\MovieList;
use Illuminate\Http\Request;

class ListController extends Controller
{
    public function index(Request $request)
    {
        $lists = MovieList::where('user_id', $request->user()->id)
            ->with('movies:id')
            ->get()
            ->map(fn ($list) => [
                'id'               => $list->id,
                'name'             => $list->name,
                'movie_remote_ids' => $list->movies->pluck('id')->values(),
                'updated_at'       => $list->updated_at,
            ]);

        return response()->json(['lists' => $lists]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'movie_remote_ids' => 'array',
            'movie_remote_ids.*' => 'integer',
        ]);

        $list = MovieList::create([
            'user_id' => $request->user()->id,
            'name'    => $request->name,
        ]);

        $this->syncMovies($list, $request->input('movie_remote_ids', []), $request->user()->id);

        return response()->json(['id' => $list->id, 'name' => $list->name], 201);
    }

    public function update(Request $request, MovieList $list)
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $request->validate([
            'name'               => 'required|string|max:255',
            'movie_remote_ids'   => 'array',
            'movie_remote_ids.*' => 'integer',
        ]);

        $list->update(['name' => $request->name]);
        $this->syncMovies($list, $request->input('movie_remote_ids', []), $request->user()->id);

        return response()->json(['id' => $list->id, 'name' => $list->name]);
    }

    public function destroy(Request $request, MovieList $list)
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $this->cleanupListOnlyMovies($list);
        $list->delete();

        return response()->json(['success' => true]);
    }

    private function syncMovies(MovieList $list, array $remoteIds, int $userId): void
    {
        $movieIds = Movie::whereIn('id', $remoteIds)->pluck('id')->toArray();

        // Ensure list-only movies that get removed are cleaned up
        $removedMovieIds = $list->movies()
            ->whereNotIn('movies.id', $movieIds)
            ->where('in_collection', false)
            ->pluck('movies.id')
            ->toArray();

        $list->movies()->sync(
            collect($movieIds)->mapWithKeys(fn ($id) => [$id => ['added_at' => now()]])->toArray()
        );

        foreach ($removedMovieIds as $movieId) {
            $remaining = \DB::table('list_movies')->where('movie_id', $movieId)->count();
            if ($remaining === 0) {
                Movie::where('id', $movieId)->where('in_collection', false)->delete();
            }
        }
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
