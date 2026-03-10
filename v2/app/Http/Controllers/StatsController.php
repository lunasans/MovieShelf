<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Actor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        $totalFilms = Movie::where('is_deleted', false)->count();
        $totalRuntime = Movie::where('is_deleted', false)->sum('runtime');
        $avgRuntime = $totalFilms > 0 ? round($totalRuntime / $totalFilms) : 0;
        $hours = round($totalRuntime / 60);
        $days = round($hours / 24);

        // Year Stats
        $yearStats = Movie::where('is_deleted', false)
            ->where('year', '>', 0)
            ->select(
                DB::raw('ROUND(AVG(year)) as avg_year'),
                DB::raw('MIN(year) as oldest_year'),
                DB::raw('MAX(year) as newest_year')
            )->first();

        // Collection Types
        $collections = Movie::where('is_deleted', false)
            ->whereNotNull('collection_type')
            ->select('collection_type', DB::raw('count(*) as count'))
            ->groupBy('collection_type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) use ($totalFilms) {
                $item->percentage = $totalFilms > 0 ? round(($item->count * 100) / $totalFilms, 1) : 0;
                return $item;
            });

        // Ratings (FSK)
        $ratings = Movie::where('is_deleted', false)
            ->whereNotNull('rating_age')
            ->select('rating_age', DB::raw('count(*) as count'))
            ->groupBy('rating_age')
            ->orderBy('rating_age', 'asc')
            ->get();

        // Top Genres
        $genres = Movie::where('is_deleted', false)
            ->whereNotNull('genre')
            ->where('genre', '!=', '')
            ->select('genre', DB::raw('count(*) as count'))
            ->groupBy('genre')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Year Distribution (Timeline)
        $yearDistribution = Movie::where('is_deleted', false)
            ->where('year', '>=', 1970)
            ->where('year', '<=', date('Y'))
            ->select('year', DB::raw('count(*) as count'))
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get()
            ->pluck('count', 'year');

        // Decades
        $decades = Movie::where('is_deleted', false)
            ->where('year', '>', 0)
            ->select(
                DB::raw("(CAST(year / 10 AS UNSIGNED) * 10) as decade"),
                DB::raw("count(*) as count"),
                DB::raw("round(avg(runtime)) as avg_runtime")
            )
            ->groupBy('decade')
            ->orderBy('decade', 'asc')
            ->get();

        if (request()->ajax()) {
            return view('movies.partials.stats', compact(
                'totalFilms', 'totalRuntime', 'avgRuntime', 'hours', 'days', 
                'yearStats', 'collections', 'ratings', 'genres', 
                'yearDistribution', 'decades'
            ));
        }

        return view('statistics', compact(
            'totalFilms', 'totalRuntime', 'avgRuntime', 'hours', 'days', 
            'yearStats', 'collections', 'ratings', 'genres', 
            'yearDistribution', 'decades'
        ));
    }
}
