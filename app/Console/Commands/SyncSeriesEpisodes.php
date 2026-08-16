<?php

namespace App\Console\Commands;

use App\Mail\SeriesNewEpisodesMail;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Season;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Gleicht laufende Serien mit TMDb ab und benachrichtigt Follower per Mail,
 * wenn eine bereits vorhandene Staffel neue (ausgestrahlte) Episoden hat.
 * Importiert NICHTS in die DB - das bleibt dem manuellen Import (Admin-Panel /
 * Desktop-App) vorbehalten, da man nicht zwangsläufig jede Staffel besitzt.
 */
class SyncSeriesEpisodes extends Command
{
    protected $signature = 'series:sync
        {--serie= : Nur eine bestimmte Serie synchronisieren (Movie-ID)}
        {--dry-run : Nur anzeigen, was erkannt würde (keine Mails)}';

    protected $description = 'Serien mit TMDb abgleichen und Nutzer über neue Episoden bereits vorhandener Staffeln informieren';

    public function handle(): int
    {
        try {
            $this->sync();
        } catch (\Throwable $e) {
            Log::error('series:sync fehlgeschlagen: '.$e->getMessage());
            $this->error('Fehler: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function sync(): void
    {
        $tmdb = new TmdbService();

        $query = Movie::where('is_deleted', false)
            ->where('in_collection', true)
            ->where('tmdb_type', 'tv')
            ->whereNotNull('tmdb_id');

        if ($this->option('serie')) {
            $query->where('id', $this->option('serie'));
        }

        $seriesList = $query->get();
        if ($seriesList->isEmpty()) {
            $this->line('  Keine Serien in der Sammlung.');

            return;
        }

        foreach ($seriesList as $serie) {
            $newEpisodes = $this->syncSeries($serie, $tmdb);

            if ($newEpisodes->isEmpty()) {
                continue;
            }

            $this->info("  {$serie->title}: {$newEpisodes->count()} neue Episode(n)");

            if (! $this->option('dry-run')) {
                $this->notifyFollowers($serie, $newEpisodes);
            }
        }
    }

    /** Neue (bereits ausgestrahlte) Episoden einer Serie erkennen (ohne DB-Änderung). */
    protected function syncSeries(Movie $serie, TmdbService $tmdb): Collection
    {
        $details = $tmdb->getTvDetails($serie->tmdb_id);
        if (isset($details['error'])) {
            $this->warn("  {$serie->title}: {$details['error']}");

            return collect();
        }

        $created = collect();

        foreach ($details['seasons'] ?? [] as $tmdbSeason) {
            $seasonNumber = $tmdbSeason['season_number'] ?? 0;
            if ($seasonNumber === 0) {
                continue; // Specials überspringen (wie beim Import)
            }

            $season = Season::where('movie_id', $serie->id)
                ->where('season_number', $seasonNumber)
                ->first();

            // Der Cron legt nie eigenständig eine neue Staffel an - man besitzt
            // ja nicht zwangsläufig jede Staffel einer Serie. Neue Staffeln
            // bleiben dem manuellen Import (Admin-Panel) vorbehalten; hier
            // werden nur Episoden bereits vorhandener Staffeln nachgezogen.
            if (! $season) {
                continue;
            }

            $localCount = $season->episodes()->count();

            // API-Schonung: Season-Details nur laden, wenn TMDb mehr Episoden kennt
            if ($localCount >= ($tmdbSeason['episode_count'] ?? 0)) {
                continue;
            }

            $seasonDetails = $tmdb->getSeasonDetails($serie->tmdb_id, $seasonNumber);
            if (isset($seasonDetails['error'])) {
                continue;
            }

            $existingNumbers = $season->episodes()->pluck('episode_number')->flip();

            foreach ($seasonDetails['episodes'] ?? [] as $tmdbEpisode) {
                $episodeNumber = $tmdbEpisode['episode_number'] ?? null;
                if ($episodeNumber === null || $existingNumbers->has($episodeNumber)) {
                    continue;
                }

                // Nur bereits ausgestrahlte Episoden importieren
                $airDate = $tmdbEpisode['air_date'] ?? null;
                if (empty($airDate) || $airDate > now()->toDateString()) {
                    continue;
                }

                // Nur zur Erkennung/Mail - bewusst nicht in der DB gespeichert.
                $episode = new Episode([
                    'episode_number' => $episodeNumber,
                    'title' => $tmdbEpisode['name'] ?? null,
                ]);

                $episode->season_number = $seasonNumber;
                $created->push($episode);
            }
        }

        return $created;
    }

    /** Follower benachrichtigen: Nutzer mit mindestens einer gesehenen Episode der Serie. */
    protected function notifyFollowers(Movie $serie, Collection $newEpisodes): void
    {
        $followerIds = DB::table('episode_user_watched')
            ->join('episodes', 'episodes.id', '=', 'episode_user_watched.episode_id')
            ->join('seasons', 'seasons.id', '=', 'episodes.season_id')
            ->where('seasons.movie_id', $serie->id)
            ->distinct()
            ->pluck('episode_user_watched.user_id');

        if ($followerIds->isEmpty()) {
            return;
        }

        $rootUrl = config('app.url');
        $seriesUrl = "{$rootUrl}/movies/{$serie->id}";

        $followers = User::whereIn('id', $followerIds)
            ->where('notify_new_episodes', true)
            ->get();

        foreach ($followers as $user) {
            try {
                // Signierter Abmelde-Link (ohne Login nutzbar)
                URL::forceRootUrl($rootUrl);
                $unsubscribeUrl = URL::signedRoute('series.unsubscribe', ['user' => $user->id]);
                URL::forceRootUrl(null);

                Mail::to($user->email)->send(new SeriesNewEpisodesMail($user, $serie, $newEpisodes, $seriesUrl, $unsubscribeUrl));
            } catch (\Throwable $e) {
                Log::error("series:sync Mail an {$user->email} fehlgeschlagen: ".$e->getMessage());
            }
        }
    }
}
