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
        $this->trimActorNames();

        $duplicates = $this->getDuplicateNames();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicates found.');
            return 0;
        }

        $this->info('Found ' . $duplicates->count() . ' names with duplicates.');

        foreach ($duplicates as $dup) {
            $this->mergeDuplicateGroup($dup);
        }

        $this->info('Finished merging actors.');
        return 0;
    }

    private function trimActorNames()
    {
        \Illuminate\Support\Facades\DB::update("UPDATE actors SET first_name = TRIM(first_name), last_name = TRIM(last_name)");
    }

    private function getDuplicateNames()
    {
        return \App\Models\Actor::select('first_name', 'last_name')
            ->groupBy('first_name', 'last_name')
            ->havingRaw('COUNT(*) > 1')
            ->get();
    }

    private function mergeDuplicateGroup($dup)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($dup) {
            $actors = \App\Models\Actor::where('first_name', $dup->first_name)
                ->where('last_name', $dup->last_name)
                ->get();

            $survivor = $this->identifySurvivor($actors);
            $redundants = $actors->reject(fn($a) => $a->id === $survivor->id);

            $this->warn("Merging duplicates for: {$dup->first_name} {$dup->last_name} (Keeping ID {$survivor->id})");

            foreach ($redundants as $redundant) {
                $this->mergeRedundantActor($redundant, $survivor);
                $redundant->delete();
            }
        });
    }

    private function identifySurvivor($actors)
    {
        return $actors->sortByDesc(function($a) {
            return ($a->tmdb_id ? 10 : 0) + ($a->profile_path ? 5 : 0) + ($a->id / 1000000);
        })->first();
    }

    private function mergeRedundantActor($redundant, $survivor)
    {
        $relations = \Illuminate\Support\Facades\DB::table('film_actor')
            ->where('actor_id', $redundant->id)
            ->get();

        foreach ($relations as $rel) {
            $this->moveRelation($rel, $redundant->id, $survivor->id);
        }
    }

    private function moveRelation($rel, $redundantId, $survivorId)
    {
        try {
            $this->resolveRelationConflict($rel->film_id, $redundantId, $survivorId);
        } catch (\Exception $e) {
            $this->error("Failed to move relation for film {$rel->film_id}: " . $e->getMessage());
        }
    }

    private function resolveRelationConflict($filmId, $redundantId, $survivorId)
    {
        $exists = \Illuminate\Support\Facades\DB::table('film_actor')
            ->where('actor_id', $survivorId)
            ->where('film_id', $filmId)
            ->exists();

        if ($exists) {
            \Illuminate\Support\Facades\DB::table('film_actor')
                ->where('actor_id', $redundantId)
                ->where('film_id', $filmId)
                ->delete();
            return;
        }

        \Illuminate\Support\Facades\DB::table('film_actor')
            ->where('actor_id', $redundantId)
            ->where('film_id', $filmId)
            ->update(['actor_id' => $survivorId]);
    }
}
