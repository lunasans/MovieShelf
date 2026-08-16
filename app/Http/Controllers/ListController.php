<?php

namespace App\Http\Controllers;

use App\Models\ExternalMovie;
use App\Models\Movie;
use App\Models\MovieList;
use App\Services\TmdbImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListController extends Controller
{
    public function index()
    {
        $lists = MovieList::where('user_id', auth()->id())
            ->withCount(['movies', 'externalMovies'])
            ->orderBy('name')
            ->get();

        return view('tenant.lists.index', compact('lists'));
    }

    public function show(MovieList $list)
    {
        $this->authorizeList($list);

        $items = $list->allItems();

        return view('tenant.lists.show', compact('list', 'items'));
    }

    /** Öffentliche Read-only-Ansicht einer geteilten Liste (kein Login nötig). */
    public function shared(string $token)
    {
        $list = MovieList::where('share_token', $token)->firstOrFail();

        $items = $list->allItems();

        return view('tenant.lists.shared', compact('list', 'items'));
    }

    /** Teilen ein-/ausschalten: Token erzeugen bzw. entfernen. */
    public function toggleShare(MovieList $list)
    {
        $this->authorizeList($list);

        $list->update([
            'share_token' => $list->isShared() ? null : \Illuminate\Support\Str::random(40),
        ]);

        return back()->with('success', $list->isShared()
            ? 'Liste ist jetzt öffentlich über den Link erreichbar.'
            : 'Teilen beendet – der Link ist nicht mehr gültig.');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $list = MovieList::create([
            'user_id' => auth()->id(),
            'name'    => $request->name,
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

        $this->cleanupExternalOnly($list);
        $list->delete();

        return redirect()->route('lists.index')->with('success', 'Liste gelöscht.');
    }

    /** Item (Sammlungs- ODER externer Film) zur Liste hinzufügen. */
    public function addItem(Request $request, MovieList $list)
    {
        $this->authorizeList($list);
        $request->validate([
            'item_type' => 'required|in:movie,external',
            'item_id'   => 'required|integer',
        ]);

        $relation = $request->item_type === 'movie' ? $list->movies() : $list->externalMovies();
        $relation->syncWithoutDetaching([$request->item_id => ['added_at' => now()]]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Zur Liste hinzugefügt.');
    }

    /** Item aus der Liste entfernen. */
    public function removeItem(Request $request, MovieList $list)
    {
        $this->authorizeList($list);
        $request->validate([
            'item_type' => 'required|in:movie,external',
            'item_id'   => 'required|integer',
        ]);

        if ($request->item_type === 'movie') {
            $list->movies()->detach($request->item_id);
        } else {
            $list->externalMovies()->detach($request->item_id);
            // Externen Film löschen, wenn er in keiner Liste mehr ist.
            $remaining = DB::table('list_items')
                ->where('item_type', 'external')->where('item_id', $request->item_id)->count();
            if ($remaining === 0) {
                ExternalMovie::where('id', $request->item_id)->delete();
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Von Liste entfernt.');
    }

    /** TMDb-Film als externen Film anlegen und zur Liste hinzufügen. */
    public function importFromTmdb(Request $request, MovieList $list, TmdbImportService $importService)
    {
        $this->authorizeList($list);
        $request->validate([
            'tmdb_id' => 'required|integer',
            'type'    => 'nullable|in:movie,tv',
        ]);

        $tmdbId = (int) $request->tmdb_id;

        try {
            $external = ExternalMovie::where('tmdb_id', $tmdbId)
                ->where('user_id', auth()->id())
                ->first();

            if (! $external) {
                $external = $importService->createExternalFromTmdb($tmdbId, $request->get('type', 'movie'), auth()->id());
            }

            $list->externalMovies()->syncWithoutDetaching([$external->id => ['added_at' => now()]]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('importFromTmdb fehlgeschlagen: '.$e->getMessage());

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Import fehlgeschlagen: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Import fehlgeschlagen: '.$e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'external_id' => $external->id]);
        }

        return back()->with('success', "\"{$external->title}\" zur Liste hinzugefügt.");
    }

    private function authorizeList(MovieList $list): void
    {
        abort_unless($list->user_id === auth()->id(), 403);
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
