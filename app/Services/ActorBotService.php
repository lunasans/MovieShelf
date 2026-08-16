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
                // Auch gegen original_name prüfen, sonst scheitert der Vergleich
                // Kanji (DB) gegen romanisierten Namen (TMDb) und umgekehrt.
                $dbName = mb_strtolower(trim($actor->full_name));
                $candidates = array_filter([
                    mb_strtolower($foundName),
                    mb_strtolower($firstResult['original_name'] ?? ''),
                ]);

                $isSimilar = false;
                foreach ($candidates as $candidate) {
                    if (str_contains($candidate, $dbName) || str_contains($dbName, $candidate)) {
                        $isSimilar = true;
                        break;
                    }
                }

                if (!$isSimilar) {
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

        // Fetch specifics (inkl. Übersetzungen: TMDb liefert im biography-Feld
        // sonst stillschweigend Englisch, wenn die eingestellte Sprache fehlt)
        $details = $this->tmdb->getPersonDetails($actor->tmdb_id, null, true);

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
        $englishDetails = null;

        // Namens-Reparatur: Name in Originalschrift (z. B. Kanji) durch den
        // romanisierten Namen aus den englischen Personendaten ersetzen.
        if (!TmdbImportService::hasLatinCharacters($actor->full_name)) {
            $englishDetails = $this->tmdb->getPersonDetails($actor->tmdb_id, 'en-US');
            $englishName = trim($englishDetails['name'] ?? '');

            if (empty($englishDetails['error']) && TmdbImportService::hasLatinCharacters($englishName)) {
                $originalName = trim($actor->full_name);
                $nameParts = explode(' ', $englishName, 2);
                $actor->first_name = $nameParts[0];
                $actor->last_name = $nameParts[1] ?? '';
                $actor->original_name = $actor->original_name ?: $originalName;
                if (empty($actor->slug)) {
                    $actor->slug = Str::slug($englishName);
                }
                $updated = true;

                BotLog::create([
                    'bot_run_id' => $botRun->id,
                    'actor_id' => $actor->id,
                    'status' => 'success',
                    'message' => "Name romanisiert: '{$originalName}' → '{$englishName}'.",
                ]);
            }
        }

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
        // Bio: bevorzugt in der eingestellten Sprache, sonst Fallback auf Englisch
        // (bio_locale = 'en'). Taucht später eine Bio in der eingestellten Sprache
        // auf, ersetzt sie die automatisch importierte englische. Manuell gepflegte
        // Bios (bio_locale = null) bleiben unberührt. Wichtig: das biography-Feld
        // selbst ist unzuverlässig, weil TMDb bei fehlender Übersetzung stillschweigend
        // Englisch liefert – die echte Sprachfassung steht nur in den translations.
        $primaryLocale = substr($this->tmdb->getLanguage(), 0, 2);
        $primaryBio = $this->getTranslatedBio($details, $primaryLocale);
        $englishBio = $primaryLocale === 'en'
            ? $primaryBio
            : ($this->getTranslatedBio($details, 'en') ?: trim($details['biography'] ?? ''));

        if ($primaryBio !== '' && (empty($actor->bio) || ($actor->bio_locale === 'en' && $primaryLocale !== 'en'))) {
            $actor->bio = $primaryBio;
            $actor->bio_locale = $primaryLocale;
            $updated = true;
        } elseif ($primaryBio === '' && empty($actor->bio) && $englishBio !== '') {
            $actor->bio = $englishBio;
            $actor->bio_locale = 'en';
            $updated = true;
        } elseif (
            $primaryBio === '' && $primaryLocale !== 'en'
            && $actor->bio_locale === $primaryLocale && trim($actor->bio ?? '') === $englishBio
        ) {
            // Reparatur: ein früherer Lauf hat den englischen TMDb-Fallback
            // fälschlich als Primärsprache markiert – neu einordnen, damit eine
            // später erscheinende Übersetzung nachgezogen werden kann.
            $actor->bio_locale = 'en';
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
     * Biografie einer bestimmten Sprache aus den TMDb-Übersetzungen holen.
     */
    protected function getTranslatedBio(array $details, string $locale): string
    {
        foreach ($details['translations']['translations'] ?? [] as $translation) {
            if (($translation['iso_639_1'] ?? '') === $locale) {
                return trim($translation['data']['biography'] ?? '');
            }
        }

        return '';
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
