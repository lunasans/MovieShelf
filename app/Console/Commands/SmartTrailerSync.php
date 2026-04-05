<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Services\TmdbService;
use App\Services\YouTubeSearchService;
use Illuminate\Console\Command;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SmartTrailerSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:smart-trailer {--force : Update even if trailer_url is not empty} {--movie= : Nur einen bestimmten Film synchronisieren (ID)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search for missing movie trailers using TMDb API';

    /**
     * Execute the console command.
     */
    public function handle(TmdbService $tmdb, YouTubeSearchService $youtube): int
    {
        $query = Movie::whereNotNull('tmdb_id');

        if ($this->option('movie')) {
            $query->where('id', $this->option('movie'));
        } elseif (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('trailer_url')->orWhere('trailer_url', '');
            });
        }

        $movies = $query->get();
        $total = $movies->count();

        // Start a new sync run
        $run = \App\Models\TrailerSyncRun::create([
            'status' => 'running',
            'total_movies' => $total,
            'started_at' => now(),
        ]);

        if ($total === 0) {
            $this->info('Keine Filme zum Aktualisieren gefunden.');
            
            $run->update([
                'status' => 'success',
                'completed_at' => now(),
            ]);

            Setting::set('smart_trailer_last_run', now()->toDateTimeString());
            Setting::set('smart_trailer_last_status', 'success');
            Setting::set('smart_trailer_last_results', json_encode(['updated' => 0, 'total' => 0]));
            return Command::SUCCESS;
        }

        $this->info("Starte Trailer-Sync für {$total} Filme...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updatedCount = 0;

        try {
            foreach ($movies as $movie) {
                try {
                    $type = $movie->collection_type === 'Serie' ? 'tv' : 'movie';
                    $details = $type === 'tv' ? $tmdb->getTvDetails($movie->tmdb_id) : $tmdb->getMovieDetails($movie->tmdb_id);
                    $trailerUrl = null;

                    // 1. Try TMDb
                    if (isset($details['videos']['results'])) {
                        $videos = $details['videos']['results'];
                        $trailerUrl = $this->findBestTrailer($videos);
                    }

                    // 2. Fallback to direct YouTube Search
                    if (!$trailerUrl) {
                        $this->info("\nFall-back Suche für {$movie->title}...");
                        $trailerUrl = $youtube->searchTrailer($movie->title, $movie->year);
                        
                        if ($trailerUrl) {
                             \App\Models\TrailerSyncLog::create([
                                'run_id' => $run->id,
                                'movie_id' => $movie->id,
                                'movie_title' => $movie->title,
                                'status' => 'found',
                                'message' => "Via YouTube Direktsuche gefunden: {$trailerUrl}",
                            ]);
                        }
                    } else {
                        \App\Models\TrailerSyncLog::create([
                            'run_id' => $run->id,
                            'movie_id' => $movie->id,
                            'movie_title' => $movie->title,
                            'status' => 'found',
                            'message' => "Via TMDb gefunden: {$trailerUrl}",
                        ]);
                    }

                    if ($trailerUrl) {
                        $movie->update(['trailer_url' => $trailerUrl]);
                        $updatedCount++;
                    } else {
                        \App\Models\TrailerSyncLog::create([
                            'run_id' => $run->id,
                            'movie_id' => $movie->id,
                            'movie_title' => $movie->title,
                            'status' => 'not_found',
                            'message' => "Weder bei TMDb noch YouTube etwas passendes gefunden.",
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    $errorMsg = $e->getMessage();
                    Log::error("Failed to sync trailer for movie {$movie->id} ({$movie->title}): " . $errorMsg);
                    
                    \App\Models\TrailerSyncLog::create([
                        'run_id' => $run->id,
                        'movie_id' => $movie->id,
                        'movie_title' => $movie->title,
                        'status' => 'error',
                        'message' => "Fehler: {$errorMsg}",
                    ]);
                }

                $bar->advance();
                usleep(250000); // 250ms
            }

            $run->update([
                'status' => 'success',
                'updated_movies' => $updatedCount,
                'completed_at' => now(),
            ]);

            Setting::set('smart_trailer_last_run', now()->toDateTimeString());
            Setting::set('smart_trailer_last_status', 'success');
            Setting::set('smart_trailer_last_results', json_encode(['updated' => $updatedCount, 'total' => $total]));
            Setting::set('smart_trailer_last_error', null);

        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            Log::error("Critical error in SmartTrailerSync: " . $errorMsg);
            
            $run->update([
                'status' => 'error',
                'error_message' => $errorMsg,
                'completed_at' => now(),
            ]);

            Setting::set('smart_trailer_last_run', now()->toDateTimeString());
            Setting::set('smart_trailer_last_status', 'error');
            Setting::set('smart_trailer_last_error', $errorMsg);
        }

        $bar->finish();
        $this->newLine();
        $this->info("Fertig! {$updatedCount} von {$total} Filmen wurden aktualisiert.");

        return Command::SUCCESS;
    }

    /**
     * Find the best matching trailer from videos results.
     */
    protected function findBestTrailer(array $videos): ?string
    {
        $vids = collect($videos)->where('site', 'YouTube');

        // 1. Trailer in German
        $trailer = $vids->where('type', 'Trailer')->where('iso_639_1', 'de')->first();

        // 2. Trailer in English
        if (!$trailer) {
            $trailer = $vids->where('type', 'Trailer')->where('iso_639_1', 'en')->first();
        }

        // 3. Any type in German with "Trailer" in the name (e.g. Typ 'Clip' but name contains 'Trailer')
        if (!$trailer) {
            $trailer = $vids->where('iso_639_1', 'de')->filter(function($v) {
                return stripos($v['name'] ?? '', 'trailer') !== false;
            })->first();
        }

        // 4. Any type in English with "Trailer" in the name
        if (!$trailer) {
            $trailer = $vids->where('iso_639_1', 'en')->filter(function($v) {
                return stripos($v['name'] ?? '', 'trailer') !== false;
            })->first();
        }

        // 5. Teaser in German
        if (!$trailer) {
            $trailer = $vids->where('type', 'Teaser')->where('iso_639_1', 'de')->first();
        }

        // 6. Teaser in English
        if (!$trailer) {
            $trailer = $vids->where('type', 'Teaser')->where('iso_639_1', 'en')->first();
        }

        // 7. Fallback: Any type that contains "trailer" in the name
        if (!$trailer) {
            $trailer = $vids->filter(function($v) {
                return stripos($v['name'] ?? '', 'trailer') !== false;
            })->first();
        }

        // 8. Fallback: Any Teaser
        if (!$trailer) {
            $trailer = $vids->where('type', 'Teaser')->first();
        }

        if ($trailer && isset($trailer['key'])) {
            return "https://www.youtube.com/watch?v={$trailer['key']}";
        }

        return null;
    }
}
