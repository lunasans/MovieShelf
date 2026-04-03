<?php

namespace App\Services;

use App\Models\Actor;
use App\Models\BotLog;
use App\Models\BotRun;
use App\Services\TmdbService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ActorBotService
{
    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function processChunk(BotRun $botRun, int $chunkSize = 10): bool
    {
        $actors = Actor::where('id', '>', $botRun->last_actor_id)
            ->orderBy('id', 'asc')
            ->limit($chunkSize)
            ->get();

        if ($actors->isEmpty()) {
            $botRun->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            return false; // Done processing
        }

        $processedCount = 0;
        $lastId = $botRun->last_actor_id;

        foreach ($actors as $actor) {
            $this->processActor($actor, $botRun);
            $lastId = $actor->id;
            $processedCount++;
        }

        $botRun->update([
            'last_actor_id' => $lastId,
            'processed_actors' => $botRun->processed_actors + $processedCount,
        ]);

        return true; // Still has more
    }

    protected function processActor(Actor $actor, BotRun $botRun): void
    {
        // Delete actor if they have exactly 0 movies
        if ($actor->movies()->count() === 0) {
            $actorName = $actor->full_name;
            $actorId = $actor->id; // store before delete
            $actor->delete();
            
            BotLog::create([
                'bot_run_id' => $botRun->id,
                'actor_id' => null, // Actor is soft-deleted or permanently deleted, foreign key constraint uses nullOnDelete
                'status' => 'success',
                'message' => "Schauspieler '{$actorName}' (ID: {$actorId}) wurde gelöscht: 0 Filme zugeordnet.",
            ]);
            return;
        }

        if (!$actor->tmdb_id) {
            // Try to find tmdb_id via search
            $search = $this->tmdb->searchPerson($actor->full_name);
            if (isset($search['results']) && count($search['results']) > 0) {
                // Heuristic: take the first match but only if the name is reasonably similar
                $firstResult = $search['results'][0];
                $foundTmdbId = $firstResult['id'];
                $foundName = $firstResult['name'];

                // Safety Check: Name Similarity (Simple exact match or fuzzy)
                $dbName = strtolower($actor->full_name);
                $tmdbNameLower = strtolower($foundName);
                
                // If it's not a very close match, skip it for safety
                if (!str_contains($tmdbNameLower, $dbName) && !str_contains($dbName, $tmdbNameLower)) {
                     BotLog::create([
                        'bot_run_id' => $botRun->id,
                        'actor_id' => $actor->id,
                        'status' => 'skipped',
                        'message' => "Sicherheits-Skip: Name '{$foundName}' (TMDb) weicht zu stark von '{$actor->full_name}' (DB) ab.",
                    ]);
                    return;
                }

                // Check if this TMDb ID is already taken by another actor
                $existingActor = Actor::where('tmdb_id', $foundTmdbId)->first();
                
                if ($existingActor && $existingActor->id !== $actor->id) {
                    $actorName = $actor->full_name;
                    $actorId = $actor->id;
                    
                    // Merge movies safely
                    $pivotData = [];
                    foreach ($actor->movies as $movie) {
                        $pivotData[$movie->id] = [
                            'role' => $movie->pivot->role ?? null,
                            'is_main_role' => $movie->pivot->is_main_role ?? false,
                            'sort_order' => $movie->pivot->sort_order ?? 0,
                        ];
                    }
                    $existingActor->movies()->syncWithoutDetaching($pivotData);
                    
                    // Delete the duplicate
                    $actor->delete();
                    BotLog::create([
                        'bot_run_id' => $botRun->id,
                        'actor_id' => null,
                        'status' => 'success',
                        'message' => "Schauspieler '{$actorName}' (ID: {$actorId}) wurde gelöscht (Duplikat zu bestehender TMDb ID {$foundTmdbId}). Filme zusammengeführt.",
                    ]);
                    return;
                }

                $actor->tmdb_id = $foundTmdbId;
                $actor->save();
            } else {
                BotLog::create([
                    'bot_run_id' => $botRun->id,
                    'actor_id' => $actor->id,
                    'status' => 'skipped',
                    'message' => 'Keine TMDb ID gefunden oder zugeordnet.',
                ]);
                return;
            }
        }

        // Fetch specifics
        $details = $this->tmdb->getPersonDetails($actor->tmdb_id);

        if (isset($details['error'])) {
            BotLog::create([
                'bot_run_id' => $botRun->id,
                'actor_id' => $actor->id,
                'status' => 'error',
                'message' => 'TMDb API Fehler: ' . $details['error'],
            ]);
            return;
        }

        $updated = false;

        if (empty($actor->birthday) && !empty($details['birthday'])) {
            $actor->birthday = $details['birthday'];
            $updated = true;
        }
        if (empty($actor->deathday) && !empty($details['deathday'])) {
            $actor->deathday = $details['deathday'];
            $updated = true;
        }
        if (empty($actor->place_of_birth) && !empty($details['place_of_birth'])) {
            $actor->place_of_birth = $details['place_of_birth'];
            $updated = true;
        }
        if (empty($actor->bio) && !empty($details['biography'])) {
            $actor->bio = $details['biography'];
            $updated = true;
        }

        // Profile image
        if (empty($actor->profile_path) && !empty($details['profile_path'])) {
            try {
                $profileUrl = 'https://image.tmdb.org/t/p/w185' . $details['profile_path'];
                $imageContent = Http::withOptions([
                    'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
                ])->get($profileUrl)->body();
                $filename = 'actors/' . Str::random(20) . '.jpg';
                Storage::disk('public')->put($filename, $imageContent);
                $actor->profile_path = $filename;
                $updated = true;
            } catch (\Exception $e) {
                // Ignore image download errors
            }
        }

        if ($updated) {
            $actor->save();
            BotLog::create([
                'bot_run_id' => $botRun->id,
                'actor_id' => $actor->id,
                'status' => 'success',
                'message' => 'Fehlende Felder via API aktualisiert.',
            ]);
        }

        // Always run cleanup/validation if we have a TMDB ID
        $this->validateAndPruneMovies($actor, $botRun);
    }

    /**
     * Verifies current movie associations against TMDb credits and removes mismatches.
     */
    protected function validateAndPruneMovies(Actor $actor, BotRun $botRun): void
    {
        if (!$actor->tmdb_id) {
            return;
        }

        $credits = $this->tmdb->getPersonCombinedCredits($actor->tmdb_id);
        
        if (isset($credits['error']) || !isset($credits['cast'])) {
            return;
        }

        // Get all TMDb IDs from credits (both movies and TV)
        $tmdbCastIds = collect($credits['cast'])->pluck('id')->unique()->toArray();
        
        // Also get titles for cases where local movies don't have tmdb_id yet
        $tmdbCastTitles = collect($credits['cast'])->map(function($c) {
            return strtolower($c['title'] ?? $c['name'] ?? '');
        })->filter()->unique()->toArray();

        $localMovies = $actor->movies;
        $detachedCount = 0;

        foreach ($localMovies as $movie) {
            $isFound = false;

            // 1. Check by TMDb ID (most reliable)
            if ($movie->tmdb_id && in_array((int)$movie->tmdb_id, $tmdbCastIds)) {
                $isFound = true;
            } 
            
            // 2. Fallback: Check by Title (case-insensitive)
            if (!$isFound) {
                $localTitle = strtolower($movie->title);
                if (in_array($localTitle, $tmdbCastTitles)) {
                    $isFound = true;
                }
            }

            // 3. Special Case: Boxsets might have children that don't have individual credits
            // but the parent might. This is complex, so we stick to 1 & 2 for now.

            if (!$isFound) {
                $actor->movies()->detach($movie->id);
                $detachedCount++;
                
                BotLog::create([
                    'bot_run_id' => $botRun->id,
                    'actor_id' => $actor->id,
                    'status' => 'info',
                    'message' => "Zuordnung zu '{$movie->title}' (ID: {$movie->id}) entfernt: Nicht in TMDb-Credits gefunden.",
                ]);
            }
        }

        if ($detachedCount > 0) {
            BotLog::create([
                'bot_run_id' => $botRun->id,
                'actor_id' => $actor->id,
                'status' => 'success',
                'message' => "Bereinigung: {$detachedCount} fehlerhafte Film-Zuordnungen entfernt.",
            ]);
        }
    }
}
