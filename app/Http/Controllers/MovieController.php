<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Setting;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        // Redirect to full-page movie details in streaming mode if a movie is selected
        if ($request->filled('movie')) {
            $currentLayout = auth()->check() 
                ? auth()->user()->layout 
                : \App\Models\Setting::get('default_guest_layout', 'classic');

            if ($currentLayout === 'streaming') {
                return redirect()->route('movies.show', ['movie' => $request->movie]);
            }
        }

        $query = Movie::query()->where('is_deleted', false)->where('in_collection', true);

        $hasFilters = $request->hasAny(['q', 'type', 'genre', 'year_from', 'year_to', 'rating_min', 'runtime_max']);
        if (! $hasFilters) {
            $query->whereNull('boxset_parent');
        }

        // Medien-Trennung: Serien (collection_type = 'Serie', kanonischer
        // Diskriminator wie in Stats/Admin) haben eine eigene Ansicht (?media=tv)
        // und erscheinen nicht in der Filmliste. Die Suche bleibt bewusst global.
        $media = $request->get('media');
        if ($media === 'tv') {
            $query->seriesOnly();
        } elseif (! $request->filled('q')) {
            $query->moviesOnly();
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($w) use ($q) {
                $w->where('title', 'like', '%'.$q.'%')
                  ->orWhere('genre', 'like', '%'.$q.'%')
                  ->orWhere('actors_names', 'like', '%'.$q.'%')
                  ->orWhere('director', 'like', '%'.$q.'%');
            });
        }

        if ($request->filled('type')) {
            $query->where('collection_type', $request->type);
        }

        if ($request->filled('genre')) {
            $query->where('genre', 'like', '%' . $request->genre . '%');
        }

        if ($request->filled('year_from')) {
            $query->where('year', '>=', (int) $request->year_from);
        }

        if ($request->filled('year_to')) {
            $query->where('year', '<=', (int) $request->year_to);
        }

        if ($request->filled('rating_min')) {
            $query->whereNotNull('rating')->where('rating', '>=', (float) $request->rating_min);
        }

        if ($request->filled('runtime_max')) {
            $query->whereNotNull('runtime')->where('runtime', '<=', (int) $request->runtime_max);
        }

        $perPage = Setting::get('items_per_page', 20);
        $movies = $query->withCount('boxsetChildren')->orderBy('title')->paginate($perPage)->withQueryString();
        $collectionTypes = Movie::where('is_deleted', false)->where('in_collection', true)->distinct()->whereNotNull('collection_type')->orderBy('collection_type')->pluck('collection_type');

        $genres = Movie::where('is_deleted', false)->where('in_collection', true)->whereNotNull('genre')->pluck('genre')
            ->flatMap(fn($g) => array_map('trim', explode(',', $g)))
            ->filter()->unique()->sort()->values();

        $latestCount = (int) Setting::where('key', 'latest_films_count')->value('value') ?: 15;
        $latestMovies = Movie::where('is_deleted', false)->where('in_collection', true)->whereNull('boxset_parent')->withCount('boxsetChildren')->orderBy('created_at', 'desc')->limit($latestCount)->get();

        // Serien-Sektion (nur auf dem ungefilterten Dashboard, nicht in der Serien-Ansicht selbst)
        $series = collect();
        $seriesTotal = Movie::where('is_deleted', false)->where('in_collection', true)->seriesOnly()->count();
        if ($media !== 'tv' && ! $hasFilters && $seriesTotal > 0) {
            $series = Movie::where('is_deleted', false)
                ->where('in_collection', true)
                ->seriesOnly()
                ->whereNull('boxset_parent')
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
        }

        // „Weiterschauen“: angefangene Serien mit nächster ungesehener Episode (persönlich)
        $continueWatching = collect();
        if (auth()->check() && ! $hasFilters && $seriesTotal > 0) {
            $watchedAt = auth()->user()->watchedEpisodes()
                ->pluck('episode_user_watched.watched_at', 'episodes.id');

            if ($watchedAt->isNotEmpty()) {
                $continueWatching = Movie::where('is_deleted', false)
                    ->where('in_collection', true)
                    ->seriesOnly()
                    ->with([
                        'seasons' => fn ($q) => $q->orderBy('season_number'),
                        'seasons.episodes' => fn ($q) => $q->orderBy('episode_number'),
                    ])
                    ->get()
                    ->map(function ($serie) use ($watchedAt) {
                        $episodes = $serie->seasons->flatMap(
                            fn ($season) => $season->episodes->each(fn ($e) => $e->season_number = $season->season_number)
                        );
                        $total = $episodes->count();
                        $watched = $episodes->filter(fn ($e) => $watchedAt->has($e->id));

                        // Nur angefangene, noch nicht abgeschlossene Serien
                        if ($total === 0 || $watched->isEmpty() || $watched->count() >= $total) {
                            return null;
                        }

                        $serie->next_episode = $episodes->first(fn ($e) => ! $watchedAt->has($e->id));
                        $serie->progress_watched = $watched->count();
                        $serie->progress_total = $total;
                        $serie->progress_percent = (int) round(($watched->count() * 100) / $total);
                        $serie->last_watched_at = $watched->map(fn ($e) => $watchedAt->get($e->id))->filter()->max();

                        return $serie;
                    })
                    ->filter()
                    ->sortByDesc('last_watched_at')
                    ->take(12)
                    ->values();
            }
        }

        $genreRows = [];

        $featuredMovies = Movie::where('in_collection', true)->where('is_deleted', false)->whereNotNull('backdrop_url')->whereNull('boxset_parent')->inRandomOrder()->limit(5)->get();
        if ($featuredMovies->isEmpty()) {
            $featuredMovies = Movie::where('in_collection', true)->where('is_deleted', false)->whereNull('boxset_parent')->latest()->limit(1)->get();
        }

        $defaultViewMode = Setting::get('default_view_mode', 'grid');
        $viewMode = $request->get('view', $defaultViewMode);

        if ($request->ajax()) {
            $currentLayout = auth()->check() 
                ? auth()->user()->layout 
                : Setting::get('default_guest_layout', 'classic');

            if ($currentLayout === 'streaming') {
                return view('tenant.movies.partials.streaming-movie-list-ajax', compact('movies'))->render();
            }
            return view('tenant.movies.partials.movie-list-ajax', compact('movies', 'viewMode'))->render();
        }

        return view('tenant.dashboard', compact(
            'movies',
            'collectionTypes',
            'genres',
            'latestMovies',
            'defaultViewMode',
            'genreRows',
            'featuredMovies',
            'series',
            'seriesTotal',
            'continueWatching'
        ));
    }

    public function show(Movie $movie)
    {
        // Geloeschte Filme sind nur noch fuer den Delta-Sync vorhanden und
        // duerfen nicht mehr aufrufbar sein — auch nicht ueber einen
        // direkt eingegebenen Link.
        abort_if($movie->is_deleted, 404);

        $movie->load(['actors', 'boxsetChildren', 'parentBoxset', 'seasons.episodes']);
        $similarMovies = $this->similarMovies($movie);
        $layoutMode = auth()->check()
            ? auth()->user()->layout
            : Setting::get('default_guest_layout', 'classic');

        return view('tenant.movies.show', compact('movie', 'layoutMode', 'similarMovies'));
    }

    public function details(Movie $movie)
    {
        // Geloeschte Filme sind nur noch fuer den Delta-Sync vorhanden und
        // duerfen nicht mehr aufrufbar sein — auch nicht ueber einen
        // direkt eingegebenen Link.
        abort_if($movie->is_deleted, 404);

        $movie->load(['actors', 'boxsetChildren', 'parentBoxset', 'seasons.episodes']);

        $similarMovies = $this->similarMovies($movie);

        $layoutMode = auth()->check()
            ? auth()->user()->layout
            : \App\Models\Setting::get('default_guest_layout', 'classic');

        return view('tenant.movies.partials.details', compact('movie', 'similarMovies', 'layoutMode'));
    }

    /**
     * Ähnliche Titel aus der eigenen Sammlung: bewertet nach Genre-Überschneidung,
     * gemeinsamen Schauspielern, gleichem Regisseur und gleichem Medientyp (Film/Serie).
     * Rein lokal, kein API-Call.
     */
    private function similarMovies(Movie $movie, int $limit = 6)
    {
        $genres = collect(explode(',', (string) $movie->genre))->map(fn ($g) => trim($g))->filter()->values();
        $actorIds = $movie->actors->pluck('id')->flip();

        if ($genres->isEmpty() && $actorIds->isEmpty()) {
            return collect();
        }

        $candidates = Movie::where('in_collection', true)
            ->where('is_deleted', false)
            ->where('id', '!=', $movie->id)
            ->whereNull('boxset_parent')
            ->where(function ($q) use ($genres, $movie) {
                foreach ($genres as $genre) {
                    $q->orWhere('genre', 'like', '%'.$genre.'%');
                }
                if ($movie->director) {
                    $q->orWhere('director', $movie->director);
                }
            })
            ->with(['actors' => fn ($q) => $q->select('actors.id')])
            ->get();

        return $candidates
            ->map(function ($candidate) use ($genres, $actorIds, $movie) {
                $candidateGenres = collect(explode(',', (string) $candidate->genre))->map(fn ($g) => trim($g))->filter();

                $score = $genres->intersect($candidateGenres)->count() * 2;
                $score += $candidate->actors->pluck('id')->filter(fn ($id) => $actorIds->has($id))->count() * 3;
                if ($movie->director && $candidate->director === $movie->director) {
                    $score += 4;
                }
                if ($candidate->tmdb_type === $movie->tmdb_type) {
                    $score += 1;
                }
                $candidate->similarity_score = $score;

                return $candidate;
            })
            // Mind. ein Genre-Treffer oder ein stärkeres Signal (Schauspieler/Regisseur)
            ->filter(fn ($candidate) => $candidate->similarity_score >= 3)
            ->sortByDesc('similarity_score')
            ->take($limit)
            ->values();
    }

    public function random(Request $request)
    {
        $query = Movie::query()->where('is_deleted', false)->where('in_collection', true)->whereNull('boxset_parent');

        // Medien-Trennung analog zu index(): in der Serien-Ansicht nur Serien würfeln
        if ($request->get('media') === 'tv') {
            $query->seriesOnly();
        } else {
            $query->moviesOnly();
        }

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->q.'%');
        }

        if ($request->filled('type')) {
            $query->where('collection_type', $request->type);
        }

        if ($request->filled('genre')) {
            $query->where('genre', 'like', '%' . $request->genre . '%');
        }

        if ($request->filled('year_from')) {
            $query->where('year', '>=', (int) $request->year_from);
        }

        if ($request->filled('year_to')) {
            $query->where('year', '<=', (int) $request->year_to);
        }

        if ($request->filled('rating_min')) {
            $query->whereNotNull('rating')->where('rating', '>=', (float) $request->rating_min);
        }

        if ($request->filled('runtime_max')) {
            $query->whereNotNull('runtime')->where('runtime', '<=', (int) $request->runtime_max);
        }

        $movie = $query->inRandomOrder()->first();

        if (! $movie) {
            return response()->json(['error' => 'No movies found'], 404);
        }

        return response()->json([
            'id' => $movie->id,
            'backdrop_url' => $movie->backdrop_url,
        ]);
    }

    public function boxset(Movie $movie)
    {
        $movie->load('boxsetChildren');

        return response()->json([
            'parent_title' => $movie->title,
            'children' => $movie->boxsetChildren->map(function ($child) {
                return [
                    'id' => $child->id,
                    'title' => $child->title,
                    'year' => $child->year,
                    'cover_url' => $child->cover_url,
                    'details_url' => route('movies.show', $child->id),
                ];
            }),
        ]);
    }
}
