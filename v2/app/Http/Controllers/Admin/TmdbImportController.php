<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TmdbService;
use App\Models\Movie;
use App\Models\Actor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TmdbImportController extends Controller
{
    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function index()
    {
        return view('admin.tmdb.index');
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        $results = $this->tmdb->searchMovie($query);
        return response()->json($results);
    }

    public function import(Request $request)
    {
        $tmdbId = $request->get('tmdb_id');
        if (!$tmdbId) {
            return back()->with('error', 'Keine TMDb ID angegeben.');
        }

        $details = $this->tmdb->getMovieDetails($tmdbId);
        if (isset($details['error'])) {
            return back()->with('error', $details['error']);
        }

        try {
            DB::beginTransaction();

            $movie = Movie::create([
                'title' => $details['title'],
                'year' => isset($details['release_date']) ? (int)substr($details['release_date'], 0, 4) : null,
                'rating' => $details['vote_average'] ?? null,
                'genre' => implode(', ', array_column($details['genres'], 'name')),
                'runtime' => $details['runtime'] ?? null,
                'overview' => $details['overview'] ?? null,
                'director' => $this->extractDirector($details),
                'trailer_url' => $this->extractTrailer($details),
                'collection_type' => 'Blu-ray', // Default
                'rating_age' => $this->extractRating($details),
                'user_id' => auth()->id(),
            ]);

            // Handle Images (Poster & Backdrop)
            if (!empty($details['poster_path'])) {
                $posterUrl = "https://image.tmdb.org/t/p/w500" . $details['poster_path'];
                $imageContent = Http::get($posterUrl)->body();
                $filename = 'covers/' . Str::random(20) . '.jpg';
                Storage::disk('public')->put($filename, $imageContent);
                $movie->update(['cover_id' => $filename]);
            }

            if (!empty($details['backdrop_path'])) {
                $backdropUrl = "https://image.tmdb.org/t/p/original" . $details['backdrop_path'];
                $imageContent = Http::get($backdropUrl)->body();
                $filename = 'backdrops/' . Str::random(20) . '.jpg';
                Storage::disk('public')->put($filename, $imageContent);
                $movie->update(['backdrop_id' => $filename]);
            }

            // Handle Actors (Top 10)
            if (isset($details['credits']['cast'])) {
                $cast = array_slice($details['credits']['cast'], 0, 10);
                foreach ($cast as $person) {
                    $nameParts = explode(' ', $person['name'], 2);
                    $firstName = $nameParts[0];
                    $lastName = $nameParts[1] ?? '';

                    $actor = Actor::updateOrCreate(
                        ['tmdb_id' => $person['id']],
                        [
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                        ]
                    );

                    // Handle Profile Image
                    if (!empty($person['profile_path']) && empty($actor->profile_path)) {
                        try {
                            $profileUrl = "https://image.tmdb.org/t/p/w185" . $person['profile_path'];
                            $imageContent = Http::get($profileUrl)->body();
                            $filename = 'actors/' . Str::random(20) . '.jpg';
                            Storage::disk('public')->put($filename, $imageContent);
                            $actor->update(['profile_path' => $filename]);
                        } catch (\Exception $e) {
                            Log::error("Could not download actor profile: " . $e->getMessage());
                        }
                    }

                    $movie->actors()->syncWithoutDetaching([
                        $actor->id => [
                            'role' => $person['character'],
                            'is_main_role' => $person['order'] < 3,
                            'sort_order' => $person['order']
                        ]
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.movies.index')->with('success', "Filme '{$movie->title}' wurde erfolgreich importiert.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Fehler beim Import: ' . $e->getMessage());
        }
    }

    protected function extractRating(array $details): ?int
    {
        if (isset($details['release_dates']['results'])) {
            foreach ($details['release_dates']['results'] as $result) {
                if ($result['iso_3166_1'] === 'DE') {
                    foreach ($result['release_dates'] as $release) {
                        if (!empty($release['certification'])) {
                            return (int)$release['certification'];
                        }
                    }
                }
            }
        }
        return null;
    }

    protected function extractDirector(array $details): ?string
    {
        if (isset($details['credits']['crew'])) {
            foreach ($details['credits']['crew'] as $person) {
                if ($person['job'] === 'Director') {
                    return $person['name'];
                }
            }
        }
        return null;
    }

    protected function extractTrailer(array $details): ?string
    {
        if (isset($details['videos']['results'])) {
            foreach ($details['videos']['results'] as $video) {
                if ($video['site'] === 'YouTube' && $video['type'] === 'Trailer') {
                    return "https://www.youtube.com/watch?v=" . $video['key'];
                }
            }
        }
        return null;
    }
}
