<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Services\TmdbService;
use Illuminate\Http\Request;

class ActorController extends Controller
{
    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    /**
     * Display a listing of actors.
     */
    public function index(Request $request)
    {
        $query = $request->get('q');
        
        $actors = Actor::query()
            ->when($query, function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->withCount('movies')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(24);

        return view('actors.index', compact('actors'));
    }

    /**
     * Display the specified actor profile.
     */
    public function show(Actor $actor)
    {
        $data = $this->getActorData($actor);
        return view('actors.show', $data);
    }

    public function details(Actor $actor)
    {
        $data = $this->getActorData($actor);
        return view('actors.partials.details', $data);
    }

    protected function getActorData(Actor $actor)
    {
        // Increment view count
        $actor->increment('view_count');

        // Fetch or update details from TMDb if bio or imdb_id is empty and tmdb_id exists
        if ((empty($actor->bio) || empty($actor->imdb_id)) && $actor->tmdb_id) {
            $details = $this->tmdb->getPersonDetails($actor->tmdb_id);
            if (!isset($details['error'])) {
                $actor->update([
                    'imdb_id' => $details['imdb_id'] ?? $actor->imdb_id,
                    'bio' => $details['biography'] ?? $actor->bio,
                    'birthday' => $details['birthday'] ?? $actor->birthday,
                    'deathday' => $details['deathday'] ?? $actor->deathday,
                    'place_of_birth' => $details['place_of_birth'] ?? $actor->place_of_birth,
                    'homepage' => $details['homepage'] ?? $actor->homepage,
                ]);
            }
        }

        $movies = $actor->movies()
            ->orderBy('year', 'desc')
            ->get();

        // Calculate Stats
        $stats = [
            'total_movies' => $movies->count(),
            'main_roles' => $movies->where('pivot.is_main_role', true)->count(),
            'year_span' => $movies->isEmpty() ? null : $movies->min('year') . ' - ' . $movies->max('year'),
            'top_genres' => $this->calculateTopGenres($movies),
        ];

        // Generate JSON-LD
        $jsonLd = $this->getJsonLd($actor);

        return compact('actor', 'movies', 'stats', 'jsonLd');
    }

    protected function getJsonLd(Actor $actor)
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $actor->full_name,
            'birthDate' => $actor->birthday,
            'birthPlace' => $actor->place_of_birth,
            'deathDate' => $actor->deathday,
            'description' => \Illuminate\Support\Str::limit(strip_tags($actor->bio), 160),
            'image' => $actor->profile_path ? asset('storage/' . $actor->profile_path) : null,
            'url' => route('actors.show', $actor)
        ];
    }

    protected function calculateTopGenres($movies)
    {
        $genres = [];
        foreach ($movies as $movie) {
            $parts = array_map('trim', explode(',', $movie->genre));
            foreach ($parts as $genre) {
                if (empty($genre)) continue;
                $genres[$genre] = ($genres[$genre] ?? 0) + 1;
            }
        }
        arsort($genres);
        return array_slice($genres, 0, 5);
    }
}
