<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Actor;
use App\Models\Counter;
use App\Models\Movie;
use App\Models\User;
use App\Support\DashboardWidgets;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Einheitliche Zählbasis wie auf der Statistik-Seite: Sammlung =
        // is_deleted=0 AND in_collection=1; Laufzeit/Genres nur Filme ohne
        // Boxset-Eltern; Serie = collection_type 'Serie'.
        $collection = fn () => Movie::where('is_deleted', false)->where('in_collection', true);

        $stats = [
            'totalMovies' => $collection()->count(),
            'totalActors' => Actor::count(),
            'totalRuntime' => $collection()->moviesOnly()->whereDoesntHave('boxsetChildren')->sum('runtime'),
            'collectionTypes' => $collection()
                ->whereDoesntHave('boxsetChildren')
                ->selectRaw('collection_type, count(*) as count')
                ->groupBy('collection_type')
                ->orderBy('count', 'desc')
                ->get(),
            'genres' => $collection()->moviesOnly()->whereDoesntHave('boxsetChildren')
                ->whereNotNull('genre')
                ->where('genre', '!=', '')
                ->pluck('genre')
                ->flatMap(fn($g) => array_map('trim', explode(',', $g)))
                ->filter()
                ->countBy()
                ->sortDesc()
                ->take(10)
                ->map(fn($count, $genre) => (object)['genre' => $genre, 'count' => $count])
                ->values(),
            'topActors' => Actor::withCount('movies')
                ->orderBy('movies_count', 'desc')
                ->limit(5)
                ->get(),
            'latestMovies' => $collection()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'missingTmdbCount' => $collection()->whereNull('tmdb_id')->count(),
            'missingCoverCount' => $collection()->whereNull('cover_id')->count(),
            'missingTrailerCount' => $collection()->whereNotNull('tmdb_id')->where(function($q) {
                $q->whereNull('trailer_url')->orWhere('trailer_url', '');
            })->count(),
            'totalUsers' => User::count(),
            'visitsToday' => Counter::where('page', 'daily:'.now()->format('Y-m-d'))->value('visits') ?? 0,
            'visitsTotal' => Counter::where('page', 'all')->value('visits') ?? 0,
            'recentActivity' => ActivityLog::orderBy('created_at', 'desc')->limit(5)->get(),
        ];

        // Frei platzierbare Kacheln: Anordnung kommt aus den Einstellungen
        $widgets = DashboardWidgets::layout();

        return view('admin.dashboard', compact('stats', 'widgets'));
    }

    /** Anordnung der Dashboard-Kacheln speichern (Drag & Drop im Anpassen-Modus). */
    public function saveLayout(Request $request)
    {
        $data = $request->validate([
            'layout'             => 'required|array',
            'layout.*.x'         => 'required|integer|min:0',
            'layout.*.y'         => 'required|integer|min:0',
            'layout.*.w'         => 'required|integer|min:1|max:' . DashboardWidgets::COLUMNS,
            'layout.*.h'         => 'required|integer|min:1|max:50',
            'layout.*.visible'   => 'required|boolean',
        ]);

        DashboardWidgets::save($data['layout']);

        return response()->json(['success' => true]);
    }

    public function resetLayout()
    {
        DashboardWidgets::reset();

        return response()->json(['success' => true]);
    }

    protected function getDatabaseDriver()
    {
        return \Illuminate\Support\Facades\DB::connection()->getDriverName();
    }
}
