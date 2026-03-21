<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
        $availableLetters = $this->getAvailableLetters();
        $actorsQuery = $this->buildActorsQuery($request);
        
        $totalActors = Actor::count();
        $filteredActorsCount = $actorsQuery->count();

        $actors = $actorsQuery->paginate(60);
        $groupedActors = $this->groupActors($actors->getCollection());

        $letter = strtoupper($request->get('letter'));

        return view('actors.index', compact(
            'actors', 
            'groupedActors', 
            'availableLetters', 
            'totalActors', 
            'filteredActorsCount',
            'letter'
        ));
    }

    protected function getAvailableLetters(): array
    {
        return Actor::select(DB::raw('UPPER(SUBSTRING(last_name, 1, 1)) as first_letter'))
            ->whereNotNull('last_name')
            ->where('last_name', '!=', '')
            ->distinct()
            ->orderBy('first_letter')
            ->pluck('first_letter')
            ->toArray();
    }

    protected function buildActorsQuery(Request $request)
    {
        $query = $request->get('q');
        $letter = strtoupper($request->get('letter'));

        return Actor::query()
            ->when($query, function ($q) use ($query) {
                $q->where(function($sub) use ($query) {
                    $sub->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                });
            })
            ->when($letter && preg_match('/^[A-Z#]$/', $letter), function ($q) use ($letter) {
                if ($letter === '#') {
                    $q->whereRaw('last_name REGEXP "^[^A-Za-z]"');
                } else {
                    $q->where('last_name', 'like', $letter . '%');
                }
            })
            ->withCount('movies')
            ->orderBy('last_name')
            ->orderBy('first_name');
    }

    protected function groupActors($collection)
    {
        return $collection->groupBy(function($actor) {
            $char = strtoupper(mb_substr($actor->last_name, 0, 1));
            return preg_match('/^[A-Z]$/', $char) ? $char : '#';
        });
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
        $this->syncActorWithTmdb($actor);

        $movies = $actor->movies()
            ->orderBy('year', 'desc')
            ->get();

        $stats = $this->calculateActorStats($movies);
        $jsonLd = $this->getJsonLd($actor);

        return compact('actor', 'movies', 'stats', 'jsonLd');
    }

    protected function syncActorWithTmdb(Actor $actor)
    {
        $isLegacyPath = str_starts_with($actor->profile_path ?? '', 'tmdb_');
        $repairId = $actor->tmdb_id;

        if ($isLegacyPath && !$repairId) {
            $repairId = (int) str_replace('tmdb_', '', $actor->profile_path);
            $actor->tmdb_id = $repairId;
        }

        if (!$repairId || !(empty($actor->bio) || empty($actor->imdb_id) || $isLegacyPath || empty($actor->profile_path))) {
            return;
        }

        $details = $this->tmdb->getPersonDetails($repairId);
        if (isset($details['error'])) {
            return;
        }

        $updateData = [
            'tmdb_id' => $repairId,
            'imdb_id' => $details['imdb_id'] ?? $actor->imdb_id,
            'bio' => $details['biography'] ?? $actor->bio,
            'birthday' => $details['birthday'] ?? $actor->birthday,
            'deathday' => $details['deathday'] ?? $actor->deathday,
            'place_of_birth' => $details['place_of_birth'] ?? $actor->place_of_birth,
            'homepage' => $details['homepage'] ?? $actor->homepage,
        ];

        // Handle Image Repair
        if (($isLegacyPath || empty($actor->profile_path)) && !empty($details['profile_path'])) {
            $filename = $this->downloadActorProfile($details['profile_path']);
            if ($filename) {
                $updateData['profile_path'] = $filename;
            }
        } elseif ($isLegacyPath) {
            $updateData['profile_path'] = null;
        }

        $actor->update($updateData);
    }

    protected function downloadActorProfile(string $path): ?string
    {
        try {
            $profileUrl = "https://image.tmdb.org/t/p/w185" . $path;
            $imageContent = Http::get($profileUrl)->body();
            $filename = 'actors/' . Str::random(20) . '.jpg';
            Storage::disk('public')->put($filename, $imageContent);
            return $filename;
        } catch (\Exception $e) {
            Log::error("Could not download actor profile: " . $e->getMessage());
            return null;
        }
    }

    protected function calculateActorStats($movies)
    {
        return [
            'total_movies' => $movies->count(),
            'main_roles' => $movies->where('pivot.is_main_role', true)->count(),
            'year_span' => $movies->isEmpty() ? null : $movies->min('year') . ' - ' . $movies->max('year'),
            'top_genres' => $this->calculateTopGenres($movies),
        ];
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
                if (empty($genre)) {
                    continue;
                }
                $genres[$genre] = ($genres[$genre] ?? 0) + 1;
            }
        }
        arsort($genres);
        return array_slice($genres, 0, 5);
    }
}
