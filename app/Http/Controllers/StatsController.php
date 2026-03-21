<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    private const COUNT_RAW = 'count(*) as count';

    public function index()
    {
        $totalFilms = Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')->count();
        $totalRuntime = Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')->sum('runtime');
        $avgRuntime = $totalFilms > 0 ? round($totalRuntime / $totalFilms) : 0;
        $hours = round($totalRuntime / 60);
        $days = round($hours / 24);

        // Watched Stats
        $watchedFilms = 0;
        if (auth()->check()) {
            $watchedFilms = auth()->user()->watchedMovies()->count();
        }
        $watchedPercentage = $totalFilms > 0 ? round(($watchedFilms * 100) / $totalFilms, 1) : 0;

        // Year Stats
        $yearStats = Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')
            ->where('year', '>', 0)
            ->select(
                DB::raw('ROUND(AVG(year)) as avg_year'),
                DB::raw('MIN(year) as oldest_year'),
                DB::raw('MAX(year) as newest_year')
            )->first();

        // Collection Types
        $collections = Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')
            ->whereNotNull('collection_type')
            ->select('collection_type', DB::raw(self::COUNT_RAW))
            ->groupBy('collection_type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) use ($totalFilms) {
                $item->percentage = $totalFilms > 0 ? round(($item->count * 100) / $totalFilms, 1) : 0;

                return $item;
            });

        // Ratings (FSK)
        $ratings = Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')
            ->whereNotNull('rating_age')
            ->select('rating_age', DB::raw(self::COUNT_RAW))
            ->groupBy('rating_age')
            ->orderBy('rating_age', 'asc')
            ->get();

        // Top Genres (Split by comma)
        $allGenreStrings = Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')
            ->whereNotNull('genre')
            ->where('genre', '!=', '')
            ->pluck('genre');

        $genreCounts = [];
        foreach ($allGenreStrings as $string) {
            $parts = array_map('trim', explode(',', $string));
            foreach ($parts as $genre) {
                if ($genre) {
                    $genreCounts[$genre] = ($genreCounts[$genre] ?? 0) + 1;
                }
            }
        }
        arsort($genreCounts);
        $genres = collect(array_slice($genreCounts, 0, 10))
            ->map(fn ($count, $name) => (object) ['genre' => $name, 'count' => $count]);

        // Year Distribution (Timeline)
        $yearDistribution = Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')
            ->where('year', '>=', 1970)
            ->where('year', '<=', date('Y'))
            ->select('year', DB::raw(self::COUNT_RAW))
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get()
            ->pluck('count', 'year');

        // Decades
        $decades = Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')
            ->where('year', '>', 0)
            ->select(
                DB::raw('(CAST(year / 10 AS UNSIGNED) * 10) as decade'),
                DB::raw(self::COUNT_RAW),
                DB::raw('round(avg(runtime)) as avg_runtime')
            )
            ->groupBy('decade')
            ->orderBy('decade', 'asc')
            ->get();

        if (request()->ajax()) {
            return view('movies.partials.stats', compact(
                'totalFilms', 'totalRuntime', 'avgRuntime', 'hours', 'days',
                'yearStats', 'collections', 'ratings', 'genres',
                'yearDistribution', 'decades', 'watchedFilms', 'watchedPercentage'
            ));
        }

        return view('statistics', compact(
            'totalFilms', 'totalRuntime', 'avgRuntime', 'hours', 'days',
            'yearStats', 'collections', 'ratings', 'genres',
            'yearDistribution', 'decades', 'watchedFilms', 'watchedPercentage'
        ));
    }
}
