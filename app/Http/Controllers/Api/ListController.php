<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExternalMovieResource;
use App\Http\Resources\MovieResource;
use App\Models\ExternalMovie;
use App\Models\Movie;
use App\Models\MovieList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListController extends Controller
{
    public function index(Request $request)
    {
        $lists = MovieList::where('user_id', $request->user()->id)
            ->with(['movies:id', 'externalMovies:id'])
            ->get()
            ->map(fn ($list) => [
                'id'         => $list->id,
                'name'       => $list->name,
                'updated_at' => $list->updated_at,
                'items'      => $list->movies->map(fn ($m) => ['type' => 'movie', 'id' => $m->id])
                    ->concat($list->externalMovies->map(fn ($e) => ['type' => 'external', 'id' => $e->id]))
                    ->values(),
            ]);

        return response()->json(['lists' => $lists]);
    }

    /**
     * Vollständige Items einer Liste (Sammlungs- + externe Filme), je mit `item_type`.
     */
    public function show(Request $request, MovieList $list)
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $list->load([
            'movies' => fn ($q) => $q->where('is_deleted', false)->with('actors')->withCount('boxsetChildren'),
            'externalMovies',
        ]);

        $items = $list->movies->map(fn ($m) => (new MovieResource($m))->resolve($request))
            ->concat($list->externalMovies->map(fn ($e) => (new ExternalMovieResource($e))->resolve($request)))
            ->values();

        return response()->json([
            'id'    => $list->id,
            'name'  => $list->name,
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'items'        => 'array',
            'items.*.type' => 'required|in:movie,external',
            'items.*.id'   => 'required|integer',
        ]);

        $list = MovieList::create([
            'user_id' => $request->user()->id,
            'name'    => $request->name,
        ]);

        $this->syncItems($list, $request->input('items', []));

        return response()->json(['id' => $list->id, 'name' => $list->name], 201);
    }

    public function update(Request $request, MovieList $list)
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $request->validate([
            'name'         => 'required|string|max:255',
            'items'        => 'array',
            'items.*.type' => 'required|in:movie,external',
            'items.*.id'   => 'required|integer',
        ]);

        $list->update(['name' => $request->name]);
        $this->syncItems($list, $request->input('items', []));

        return response()->json(['id' => $list->id, 'name' => $list->name]);
    }

    public function destroy(Request $request, MovieList $list)
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $this->cleanupExternalOnly($list);
        $list->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Setzt die Mitgliedschaft exakt auf die übergebenen Items (Clients senden bereits die UNION).
     * Externe Filme, die dadurch aus allen Listen fliegen, werden aufgeräumt.
     */
    private function syncItems(MovieList $list, array $items): void
    {
        $movieIds    = collect($items)->where('type', 'movie')->pluck('id')->all();
        $externalIds = collect($items)->where('type', 'external')->pluck('id')->all();

        $validMovieIds    = Movie::whereIn('id', $movieIds)->pluck('id')->all();
        $validExternalIds = ExternalMovie::whereIn('id', $externalIds)->pluck('id')->all();

        $beforeExternal = $list->externalMovies()->pluck('external_movies.id')->all();

        $list->movies()->sync(
            collect($validMovieIds)->mapWithKeys(fn ($id) => [$id => ['added_at' => now()]])->all()
        );
        $list->externalMovies()->sync(
            collect($validExternalIds)->mapWithKeys(fn ($id) => [$id => ['added_at' => now()]])->all()
        );

        foreach (array_diff($beforeExternal, $validExternalIds) as $exId) {
            $remaining = DB::table('list_items')
                ->where('item_type', 'external')->where('item_id', $exId)->count();
            if ($remaining === 0) {
                ExternalMovie::where('id', $exId)->delete();
            }
        }
    }

    private function cleanupExternalOnly(MovieList $list): void
    {
        foreach ($list->externalMovies()->get() as $ex) {
            $other = DB::table('list_items')
                ->where('item_type', 'external')->where('item_id', $ex->id)
                ->where('list_id', '!=', $list->id)->count();
            if ($other === 0) {
                $ex->delete();
            }
        }
    }
}
