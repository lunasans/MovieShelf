<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Entfernt gesperrte Filme endgueltig, samt Bildern und abhaengigen Daten.
 *
 * Gesperrte Filme (is_deleted) bleiben absichtlich in der Datenbank, damit der
 * Delta-Sync die Loeschung an die Desktop-App melden kann. Nach Ablauf der
 * Frist ist dieser Zweck erfuellt und der Datensatz kann weg.
 */
class PurgeDeletedMovies extends Command
{
    protected $signature = 'movies:purge
                            {--older-than= : Frist in Tagen (Vorgabe aus den Einstellungen)}
                            {--dry-run : Nur anzeigen, was entfernt wuerde}';

    protected $description = 'Entfernt gesperrte Filme endgültig, inklusive Cover, Backdrops und abhängiger Daten';

    public function handle(): int
    {
        $days = (int) ($this->option('older-than') ?? Setting::get('movie_purge_days', 90));

        if ($days < 1) {
            $this->error('Die Frist muss mindestens 1 Tag betragen.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $dryRun = (bool) $this->option('dry-run');

        $this->info(sprintf(
            '%sEntferne Filme, die seit dem %s gesperrt sind (%d Tage).',
            $dryRun ? '[Trockenlauf] ' : '',
            $cutoff->format('d.m.Y'),
            $days,
        ));

        try {
            [$totalMovies, $totalFiles] = $this->purge($cutoff, $dryRun);
        } catch (\Throwable $e) {
            report($e);
            $this->error('Abgebrochen: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info(sprintf(
            '%s%d Filme und %d Dateien.',
            $dryRun ? '[Trockenlauf] Betroffen wären ' : 'Entfernt: ',
            $totalMovies,
            $totalFiles,
        ));

        return self::SUCCESS;
    }

    /**
     * @return array{0: int, 1: int} Anzahl Filme und geloeschter Dateien
     */
    protected function purge(\Carbon\Carbon $cutoff, bool $dryRun): array
    {
        $movies = Movie::where('is_deleted', true)
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '<=', $cutoff)
            ->get();

        if ($movies->isEmpty()) {
            return [0, 0];
        }

        $files = 0;

        foreach ($movies as $movie) {
            $files += $this->deleteImages($movie, $dryRun);

            if ($dryRun) {
                $this->line(sprintf('    - %s (%s)', $movie->title, $movie->deleted_at?->format('d.m.Y')));

                continue;
            }

            $this->deleteRelatedRows($movie);
            $movie->delete();
        }

        return [$movies->count(), $files];
    }

    /**
     * Abhaengige Zeilen entfernen.
     *
     * Ein Teil der Tabellen haengt per Fremdschluessel mit ON DELETE CASCADE
     * am Film — darauf wird bewusst nicht vertraut, weil SQLite die
     * Fremdschluesselpruefung nur bei aktiviertem PRAGMA durchsetzt und die
     * Zeilen sonst als Waisen zurueckblieben.
     */
    protected function deleteRelatedRows(Movie $movie): void
    {
        $seasonIds = DB::table('seasons')->where('movie_id', $movie->id)->pluck('id');

        if ($seasonIds->isNotEmpty()) {
            DB::table('episodes')->whereIn('season_id', $seasonIds)->delete();
            DB::table('seasons')->whereIn('id', $seasonIds)->delete();
        }

        DB::table('film_actor')->where('film_id', $movie->id)->delete();

        foreach (['user_ratings', 'user_watched', 'user_wishlist', 'movie_user_watched', 'list_items'] as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                DB::table($table)->where('movie_id', $movie->id)->delete();
            }
        }

        // Trailer-Protokoll behaelt seine Zeilen (movie_id wird auf null
        // gesetzt), damit der Verlauf nicht verschwindet.
        if (\Illuminate\Support\Facades\Schema::hasTable('trailer_sync_logs')) {
            DB::table('trailer_sync_logs')->where('movie_id', $movie->id)->update(['movie_id' => null]);
        }

        // Kinder eines Boxsets wuerden sonst auf einen nicht mehr
        // existierenden Elterneintrag zeigen.
        Movie::where('boxset_parent', $movie->id)->update(['boxset_parent' => null]);
    }

    /**
     * Cover und Backdrop entfernen — aber nur, wenn kein anderer Film
     * dieselbe Datei referenziert. Beim Import teilen sich Filme gelegentlich
     * ein Bild, und ein geloeschter Pfad wuerde dort zum Platzhalter fuehren.
     */
    protected function deleteImages(Movie $movie, bool $dryRun): int
    {
        $deleted = 0;

        foreach ([['cover_id', 'cover'], ['backdrop_id', 'backdrop']] as [$field, $label]) {
            $id = $movie->getRawOriginal($field);

            if (! $id || str_starts_with($id, 'http') || str_starts_with($id, 'tmdb_') || str_starts_with($id, '/')) {
                continue; // Externe oder nicht lokal abgelegte Referenz
            }

            $stillUsed = Movie::where($field, $id)->where('id', '!=', $movie->id)->exists();

            if ($stillUsed) {
                continue;
            }

            foreach (['public'] as $diskName) {
                try {
                    $disk = Storage::disk($diskName);

                    if ($disk->exists($id)) {
                        if ($dryRun) {
                            $this->line(sprintf('      %s auf %s: %s', $label, $diskName, $id));
                        } else {
                            $disk->delete($id);
                        }

                        $deleted++;
                    }
                } catch (\Throwable $e) {
                    // Eine nicht konfigurierte Disk (z. B. S3 ohne Zugangsdaten)
                    // darf den Aufraeumlauf nicht abbrechen.
                    continue;
                }
            }
        }

        return $deleted;
    }
}
