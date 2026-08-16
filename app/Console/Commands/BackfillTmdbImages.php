<?php

namespace App\Console\Commands;

use App\Models\Movie;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Laedt verbliebene TMDb-Cover/Backdrops (rohe Referenzen: /pfad oder tmdb_…)
 * einmalig von TMDb herunter, legt sie lokal unter storage/app/public ab und
 * schreibt den neuen Key ins Movie-Feld. Danach werden die Bilder lokal statt
 * per Hotlink von image.tmdb.org ausgeliefert.
 */
class BackfillTmdbImages extends Command
{
    protected $signature = 'movies:backfill-tmdb-images
                            {--dry-run : Nur anzeigen, was heruntergeladen wuerde}';

    protected $description = 'Laedt verbliebene TMDb-Cover/Backdrops herunter und legt sie lokal ab';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $totalDone = 0;
        $totalFail = 0;

        $movies = Movie::where(function ($q) {
            $q->where('cover_id', 'like', '/%')->orWhere('cover_id', 'like', 'tmdb_%')
              ->orWhere('backdrop_id', 'like', '/%')->orWhere('backdrop_id', 'like', 'tmdb_%');
        })->get();

        $this->line("{$movies->count()} Film(e) mit TMDb-Referenz");

        foreach ($movies as $movie) {
            // [Feld, Ordner, TMDb-Groesse]
            foreach ([['cover_id', 'covers', 'w500'], ['backdrop_id', 'backdrops', 'w1280']] as [$field, $folder, $size]) {
                $val = $movie->{$field};
                if (! $val || ! (str_starts_with($val, '/') || str_starts_with($val, 'tmdb_'))) {
                    continue;
                }

                $path = str_starts_with($val, 'tmdb_') ? '/' . substr($val, 5) : $val;

                if ($dry) {
                    $this->line("  [dry] #{$movie->id} {$field}: {$val}  ->  download w={$size}{$path}");
                    continue;
                }

                $newKey = $this->download($path, $size, $folder);
                if ($newKey) {
                    $movie->{$field} = $newKey;
                    $movie->save();
                    $totalDone++;
                    $this->info("  #{$movie->id} {$field}: -> {$newKey}");
                } else {
                    $totalFail++;
                    $this->warn("  #{$movie->id} {$field}: Download fehlgeschlagen ({$val})");
                }
            }
        }

        if ($dry) {
            $this->comment('Dry-Run – es wurde nichts geaendert.');
        } else {
            $this->info("Fertig. {$totalDone} Bild(er) uebertragen, {$totalFail} fehlgeschlagen.");
        }

        return self::SUCCESS;
    }

    /** Laedt ein TMDb-Bild und legt es auf der Upload-Disk ab; gibt den neuen Key zurueck. */
    private function download(string $path, string $size, string $folder): ?string
    {
        try {
            $response = Http::timeout(30)->get("https://image.tmdb.org/t/p/{$size}" . $path);
            if (! $response->successful()) {
                return null;
            }
            $prefix = $folder === 'backdrops' ? 'tmdb_backdrop_' : 'tmdb_';
            $filename = $prefix . ltrim($path, '/');
            Storage::disk('public')->put($folder . '/' . $filename, $response->body());

            return $folder . '/' . $filename;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
