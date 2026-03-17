<?php

namespace App\Console\Commands;

use App\Models\Movie;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FindDuplicateMovies extends Command
{
    protected $signature = 'app:find-duplicate-movies {--merge : Automatically attempt to merge duplicates}';
    protected $description = 'Find (and optionally merge) duplicate movies with the same title and collection type.';

    public function handle()
    {
        $this->info('Searching for duplicate movies...');

        $duplicates = Movie::select('title', 'year', 'collection_type')
            ->groupBy('title', 'year', 'collection_type')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate movies found.');
            return 0;
        }

        $this->warn('Found ' . $duplicates->count() . ' titles with multiple entries.');

        foreach ($duplicates as $duplicate) {
            $movies = Movie::where('title', $duplicate->title)
                ->where('year', $duplicate->year)
                ->where('collection_type', $duplicate->collection_type)
                ->get();

            $this->line("\nDuplicates for: \"{$duplicate->title}\" ({$duplicate->year}) [{$duplicate->collection_type}]");
            
            foreach ($movies as $movie) {
                $this->line("  ID: {$movie->id} | Year: {$movie->year} | TMDb: " . ($movie->tmdb_id ?? 'N/A'));
            }

            if ($this->option('merge')) {
                $this->mergeMovies($movies);
            }
        }

        return 0;
    }

    private function mergeMovies($movies)
    {
        // Sort by 'completeness' (TMDB ID, then actors count, then ID)
        $survivor = $movies->sortByDesc(function ($movie) {
            return ($movie->tmdb_id ? 10 : 0) + ($movie->actors->count() > 0 ? 5 : 0) + ($movie->id / 1000000);
        })->first();

        $redundants = $movies->reject(fn($m) => $m->id === $survivor->id);

        $this->warn("  Merging into survivor: ID {$survivor->id}");

        DB::transaction(function () use ($survivor, $redundants) {
            foreach ($redundants as $redundant) {
                // Transfer actors
                foreach ($redundant->actors as $actor) {
                    if (!$survivor->actors()->where('actor_id', $actor->id)->exists()) {
                        $survivor->actors()->attach($actor->id, [
                            'role' => $actor->pivot->role,
                            'is_main_role' => $actor->pivot->is_main_role,
                            'sort_order' => $actor->pivot->sort_order,
                        ]);
                    }
                }

                // Transfer watched status
                foreach ($redundant->watchedByUsers as $user) {
                    if (!$survivor->watchedByUsers()->where('user_id', $user->id)->exists()) {
                        $survivor->watchedByUsers()->attach($user->id);
                    }
                }

                // Delete the redundant one
                $redundant->delete();
            }
        });
        
        $this->info('  Merged successfully.');
    }
}
