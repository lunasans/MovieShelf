<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MergeDuplicateActors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:merge-duplicate-actors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merges duplicate actors with the same name and moves movie relations to the TMDB record.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Searching for duplicate actors...');

        // 1. Trimming names first to ensure we catch those with accidental spaces
        \Illuminate\Support\Facades\DB::update("UPDATE actors SET first_name = TRIM(first_name), last_name = TRIM(last_name)");

        $duplicates = \App\Models\Actor::select('first_name', 'last_name')
            ->groupBy('first_name', 'last_name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicates found.');
            return 0;
        }

        $this->info('Found ' . $duplicates->count() . ' names with duplicates.');

        foreach ($duplicates as $dup) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($dup) {
                $actors = \App\Models\Actor::where('first_name', $dup->first_name)
                    ->where('last_name', $dup->last_name)
                    ->get();

                // Pick the survivor: Priority to TMDB ID, then profile path, then newest
                $survivor = $actors->sortByDesc(function($a) {
                    return ($a->tmdb_id ? 10 : 0) + ($a->profile_path ? 5 : 0) + ($a->id / 1000000);
                })->first();

                $redundants = $actors->reject(fn($a) => $a->id === $survivor->id);

                $this->warn("Merging duplicates for: {$dup->first_name} {$dup->last_name} (Keeping ID {$survivor->id})");

                foreach ($redundants as $redundant) {
                    // Fetch relations from redundant
                    $relations = \Illuminate\Support\Facades\DB::table('film_actor')
                        ->where('actor_id', $redundant->id)
                        ->get();

                    foreach ($relations as $rel) {
                        try {
                            // Try to update existing relation if exists, otherwise change actor_id
                            $exists = \Illuminate\Support\Facades\DB::table('film_actor')
                                ->where('actor_id', $survivor->id)
                                ->where('film_id', $rel->film_id)
                                ->exists();

                            if ($exists) {
                                // Just delete the redundant relation
                                \Illuminate\Support\Facades\DB::table('film_actor')
                                    ->where('actor_id', $redundant->id)
                                    ->where('film_id', $rel->film_id)
                                    ->delete();
                            } else {
                                // Move it to survivor
                                \Illuminate\Support\Facades\DB::table('film_actor')
                                    ->where('actor_id', $redundant->id)
                                    ->where('film_id', $rel->film_id)
                                    ->update(['actor_id' => $survivor->id]);
                            }
                        } catch (\Exception $e) {
                            $this->error("Failed to move relation for film {$rel->film_id}: " . $e->getMessage());
                        }
                    }

                    // Delete redundant record
                    $redundant->delete();
                }
            });
        }

        $this->info('Finished merging actors.');
        return 0;
    }
}
