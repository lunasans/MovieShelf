<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Actor;
use App\Models\Counter;
use App\Models\Movie;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'totalMovies' => Movie::where('is_deleted', false)->count(),
            'totalActors' => Actor::count(),
            'totalRuntime' => Movie::where('is_deleted', false)->sum('runtime'),
            'collectionTypes' => Movie::where('is_deleted', false)
                ->selectRaw('collection_type, count(*) as count')
                ->groupBy('collection_type')
                ->orderBy('count', 'desc')
                ->get(),
            'genres' => Movie::where('is_deleted', false)
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
            'latestMovies' => Movie::where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'missingTmdbCount' => Movie::where('is_deleted', false)->whereNull('tmdb_id')->count(),
            'missingCoverCount' => Movie::where('is_deleted', false)->whereNull('cover_id')->count(),
            'missingTrailerCount' => Movie::where('is_deleted', false)->whereNotNull('tmdb_id')->where(function($q) {
                $q->whereNull('trailer_url')->orWhere('trailer_url', '');
            })->count(),
            'totalUsers' => User::count(),
            'visitsToday' => Counter::where('page', 'daily:'.now()->format('Y-m-d'))->value('visits') ?? 0,
            'visitsTotal' => Counter::where('page', 'all')->value('visits') ?? 0,
            'recentActivity' => ActivityLog::orderBy('created_at', 'desc')->limit(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    protected function getDatabaseDriver()
    {
        return \Illuminate\Support\Facades\DB::connection()->getDriverName();
    }
}
