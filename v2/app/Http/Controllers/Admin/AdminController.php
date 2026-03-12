<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Actor;
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
            'decades' => Movie::where('is_deleted', false)
                ->where('year', '>', 0)
                ->selectRaw(
                    ($this->getDatabaseDriver() === 'sqlite' ? '(year / 10) * 10' : 'FLOOR(year / 10) * 10') . ' as decade, count(*) as count'
                )
                ->groupBy('decade')
                ->orderBy('decade', 'asc')
                ->get(),
            'topActors' => Actor::withCount('movies')
                ->orderBy('movies_count', 'desc')
                ->limit(5)
                ->get(),
            'latestMovies' => Movie::where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
    protected function getDatabaseDriver()
    {
        return \Illuminate\Support\Facades\DB::connection()->getDriverName();
    }
}
