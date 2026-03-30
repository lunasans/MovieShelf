<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class StatsController extends Controller
{
    private const COUNT_RAW = 'count(*) as count';

    #[OA\Get(
        path: '/stats',
        summary: 'Sammlungs-Statistiken abrufen',
        tags: ['Statistics'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detaillierte Statistiken der Film-Sammlung',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'total_films', type: 'integer'),
                        new OA\Property(property: 'total_runtime_minutes', type: 'integer'),
                        new OA\Property(property: 'total_runtime_hours', type: 'integer'),
                        new OA\Property(property: 'total_runtime_days', type: 'integer'),
                        new OA\Property(property: 'avg_runtime', type: 'integer'),
                        new OA\Property(
                            property: 'watched',
                            properties: [
                                new OA\Property(property: 'count', type: 'integer'),
                                new OA\Property(property: 'percentage', type: 'number', format: 'float')
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'years',
                            properties: [
                                new OA\Property(property: 'avg_year', type: 'integer'),
                                new OA\Property(property: 'oldest_year', type: 'integer'),
                                new OA\Property(property: 'newest_year', type: 'integer')
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'collections',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'collection_type', type: 'string'),
                                    new OA\Property(property: 'count', type: 'integer'),
                                    new OA\Property(property: 'percentage', type: 'number', format: 'float')
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'ratings',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'rating_age', type: 'integer'),
                                    new OA\Property(property: 'count', type: 'integer')
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'genres',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'genre', type: 'string'),
                                    new OA\Property(property: 'count', type: 'integer')
                                ]
                            )
                        ),
                        new OA\Property(property: 'year_distribution', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'integer')),
                        new OA\Property(
                            property: 'decades',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'decade', type: 'integer'),
                                    new OA\Property(property: 'count', type: 'integer'),
                                    new OA\Property(property: 'avg_runtime', type: 'integer')
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Nicht authentifiziert')
        ]
    )]
    public function index(Request $request)
    {
        $totalFilms = Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')->count();
        $totalRuntime = Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')->sum('runtime');
        $avgRuntime = $totalFilms > 0 ? round($totalRuntime / $totalFilms) : 0;
        $hours = round($totalRuntime / 60);
        $days = round($hours / 24);

        // Watched Stats
        $watchedFilms = $request->user()->watchedMovies()->count();
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
            ->map(fn ($count, $name) => ['genre' => $name, 'count' => $count])
            ->values();

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

        return response()->json([
            'total_films' => $totalFilms,
            'total_runtime_minutes' => (int) $totalRuntime,
            'total_runtime_hours' => (int) $hours,
            'total_runtime_days' => (int) $days,
            'avg_runtime' => (int) $avgRuntime,
            'watched' => [
                'count' => $watchedFilms,
                'percentage' => $watchedPercentage,
            ],
            'years' => [
                'avg_year' => $yearStats->avg_year ? (int) $yearStats->avg_year : null,
                'oldest_year' => $yearStats->oldest_year ? (int) $yearStats->oldest_year : null,
                'newest_year' => $yearStats->newest_year ? (int) $yearStats->newest_year : null,
            ],
            'collections' => $collections,
            'ratings' => $ratings,
            'genres' => $genres,
            'year_distribution' => $yearDistribution,
            'decades' => $decades,
        ]);
    }
}
