<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    private const COUNT_RAW = 'count(*) as count';

    public function index()
    {
        // Serien getrennt ausweisen; die übrigen Film-Statistiken beziehen sich nur auf Filme
        $totalSeries = Movie::where('is_deleted', false)->where('in_collection', true)->seriesOnly()->count();

        $totalFilms = Movie::where('is_deleted', false)->where('in_collection', true)->moviesOnly()->whereDoesntHave('boxsetChildren')->count();
        $totalRuntime = Movie::where('is_deleted', false)->where('in_collection', true)->moviesOnly()->whereDoesntHave('boxsetChildren')->sum('runtime');
        $avgRuntime = $totalFilms > 0 ? round($totalRuntime / $totalFilms) : 0;
        $hours = round($totalRuntime / 60);
        $days = round($hours / 24);

        // Watched Stats
        $watchedFilms = 0;
        if (auth()->check()) {
            $watchedFilms = auth()->user()->watchedMovies()->moviesOnly()->count();
        }
        $watchedPercentage = $totalFilms > 0 ? round(($watchedFilms * 100) / $totalFilms, 1) : 0;

        // Persönliche Statistiken (nur eingeloggt): gesehene Zeit, Watch-Historie, Serienfortschritt
        $watchedHours = 0;
        $watchHistory = collect();
        $seriesProgress = collect();
        $episodesWatched = 0;
        $episodesTotal = 0;

        if (auth()->check()) {
            $user = auth()->user();

            // Gesehene Laufzeit: Filme + Episoden (Minuten -> Stunden)
            $movieMinutes = (int) $user->watchedMovies()->moviesOnly()->sum('runtime');
            $episodeMinutes = (int) $user->watchedEpisodes()->sum('runtime');
            $watchedHours = (int) round(($movieMinutes + $episodeMinutes) / 60);

            // Watch-Historie: Gesehen-Markierungen der letzten 12 Monate (Filme + Episoden).
            // Gruppierung in PHP, damit es treiberneutral bleibt (SQLite/MySQL).
            $since = now()->subMonths(11)->startOfMonth();
            $perMonth = DB::table('movie_user_watched')
                ->where('user_id', $user->id)
                ->where('created_at', '>=', $since)
                ->pluck('created_at')
                ->concat(
                    DB::table('episode_user_watched')
                        ->where('user_id', $user->id)
                        ->where('watched_at', '>=', $since)
                        ->pluck('watched_at')
                )
                ->filter()
                ->map(fn ($date) => substr((string) $date, 0, 7)) // "YYYY-MM"
                ->countBy();

            $watchHistory = collect(range(11, 0))->mapWithKeys(function ($i) use ($perMonth) {
                $month = now()->subMonths($i);

                return [$month->translatedFormat('M y') => $perMonth->get($month->format('Y-m'), 0)];
            });

            // Serienfortschritt: gesehene Episoden je Serie
            $watchedLookup = $user->watchedEpisodes()->pluck('episodes.id')->flip();
            $episodesWatched = $watchedLookup->count();

            $seriesProgress = Movie::where('is_deleted', false)->where('in_collection', true)->seriesOnly()
                ->with('seasons.episodes:id,season_id')
                ->orderBy('title')
                ->get()
                ->map(function ($series) use ($watchedLookup, &$episodesTotal) {
                    $episodes = $series->seasons->flatMap->episodes;
                    $total = $episodes->count();
                    $episodesTotal += $total;
                    $watched = $episodes->filter(fn ($episode) => $watchedLookup->has($episode->id))->count();

                    return (object) [
                        'id' => $series->id,
                        'title' => $series->title,
                        'total' => $total,
                        'watched' => $watched,
                        'percentage' => $total > 0 ? round(($watched * 100) / $total, 1) : 0,
                    ];
                })
                ->filter(fn ($series) => $series->total > 0)
                ->values();
        }

        // Year Stats
        $yearStats = Movie::where('is_deleted', false)->where('in_collection', true)->moviesOnly()->whereDoesntHave('boxsetChildren')
            ->where('year', '>', 0)
            ->select(
                DB::raw('ROUND(AVG(year)) as avg_year'),
                DB::raw('MIN(year) as oldest_year'),
                DB::raw('MAX(year) as newest_year')
            )->first();

        // Collection Types: Filme UND Serien ausweisen (Serie = collection_type),
        // Prozentbasis ist die Gesamtsammlung — identisch zur Desktop-App.
        $collectionTotal = $totalFilms + $totalSeries;

        $collectionsWithFilms = Movie::where('is_deleted', false)->where('in_collection', true)->whereDoesntHave('boxsetChildren')
            ->whereNotNull('collection_type')
            ->select('id', 'title', 'year', 'collection_type')
            ->orderBy('title')
            ->get()
            ->groupBy('collection_type');

        $collections = Movie::where('is_deleted', false)->where('in_collection', true)->whereDoesntHave('boxsetChildren')
            ->whereNotNull('collection_type')
            ->select('collection_type', DB::raw(self::COUNT_RAW))
            ->groupBy('collection_type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) use ($collectionTotal, $collectionsWithFilms) {
                $item->percentage = $collectionTotal > 0 ? round(($item->count * 100) / $collectionTotal, 1) : 0;
                $item->films = $collectionsWithFilms->get($item->collection_type, collect());

                return $item;
            });

        // Ratings (FSK)
        $ratings = Movie::where('is_deleted', false)->where('in_collection', true)->moviesOnly()->whereDoesntHave('boxsetChildren')
            ->whereNotNull('rating_age')
            ->select('rating_age', DB::raw(self::COUNT_RAW))
            ->groupBy('rating_age')
            ->orderBy('rating_age', 'asc')
            ->get();

        // Top Genres (Split by comma)
        $allGenreStrings = Movie::where('is_deleted', false)->where('in_collection', true)->moviesOnly()->whereDoesntHave('boxsetChildren')
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
        $yearDistribution = Movie::where('is_deleted', false)->where('in_collection', true)->moviesOnly()->whereDoesntHave('boxsetChildren')
            ->where('year', '>=', 1970)
            ->where('year', '<=', date('Y'))
            ->select('year', DB::raw(self::COUNT_RAW))
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get()
            ->pluck('count', 'year');

        // Decades
        $decades = Movie::where('is_deleted', false)->where('in_collection', true)->moviesOnly()->whereDoesntHave('boxsetChildren')
            ->where('year', '>', 0)
            ->select(
                DB::raw('(CAST(year / 10 AS UNSIGNED) * 10) as decade'),
                DB::raw(self::COUNT_RAW),
                DB::raw('round(avg(runtime)) as avg_runtime')
            )
            ->groupBy('decade')
            ->orderBy('decade', 'asc')
            ->get();

        $viewData = compact(
            'totalFilms', 'totalRuntime', 'avgRuntime', 'hours', 'days',
            'yearStats', 'collections', 'ratings', 'genres',
            'yearDistribution', 'decades', 'watchedFilms', 'watchedPercentage', 'totalSeries',
            'watchedHours', 'watchHistory', 'seriesProgress', 'episodesWatched', 'episodesTotal'
        );

        if (request()->ajax()) {
            return view('tenant.movies.partials.stats', $viewData);
        }

        return view('tenant.statistics', $viewData);
    }
}
