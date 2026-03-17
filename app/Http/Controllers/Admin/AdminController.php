<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Actor;
use App\Models\Counter;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

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
                ->selectRaw('genre, count(*) as count')
                ->groupBy('genre')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
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
            'totalUsers' => User::count(),
            'visitsToday' => Counter::where('page', 'daily:' . now()->format('Y-m-d'))->value('visits') ?? 0,
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
