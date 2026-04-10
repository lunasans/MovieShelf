<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class BackupImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:512000', // 500MB
        ]);

        Log::info('SaaS Backup Import gestartet');
        $zipFile = $request->file('backup_file');
        $tempDir = storage_path('app/temp_import_' . uniqid());
        
        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFile->getRealPath()) === TRUE) {
            $zip->extractTo($tempDir);
            $zip->close();
            Log::info('ZIP extrahiert nach: ' . $tempDir);
        } else {
            return back()->with('error', 'Fehler beim Entpacken des ZIP-Archivs.');
        }

        $importedDbPath = $tempDir . DIRECTORY_SEPARATOR . 'database.sqlite';
        if (!file_exists($importedDbPath)) {
            File::deleteDirectory($tempDir);
            Log::warning('Import abgebrochen: database.sqlite fehlt im ZIP');
            return back()->with('error', 'Die hochgeladene Datei ist kein gültiges MovieShelf-Backup (database.sqlite fehlt).');
        }

        try {
            // 1. Setup auxiliary connection
            config(['database.connections.import_aux' => [
                'driver' => 'sqlite',
                'database' => $importedDbPath,
                'prefix' => '',
            ]]);
            DB::purge('import_aux');

            DB::beginTransaction();

            // 2. Clear existing collections (Wipe & Replace strategy)
            // We use direct DB calls to bypass potential model events that might interfere with bulk import
            DB::table('film_actor')->delete();
            DB::table('movies')->delete();
            DB::table('actors')->delete();
            
            Log::info('Lokale Tabellen bereinigt (Movies, Actors, film_actor)');

            // Get current table columns to filter imported data
            $movieColumns = Schema::getColumnListing('movies');
            $actorColumns = Schema::getColumnListing('actors');
            $filmActorColumns = Schema::getColumnListing('film_actor');

            // 3. Import Actors
            $importedActors = DB::connection('import_aux')->table('actors')->get();
            foreach ($importedActors as $actorData) {
                $data = array_intersect_key((array)$actorData, array_flip($actorColumns));
                DB::table('actors')->insert($data);
            }
            Log::info(count($importedActors) . ' Schauspieler importiert');

            // 4. Import Movies
            $importedMovies = DB::connection('import_aux')->table('movies')->get();
            foreach ($importedMovies as $movieData) {
                $data = array_intersect_key((array)$movieData, array_flip($movieColumns));
                $data['user_id'] = auth()->id(); // Ensure ownership
                DB::table('movies')->insert($data);
            }
            Log::info(count($importedMovies) . ' Filme importiert');

            // 5. Import Relations
            $importedRelations = DB::connection('import_aux')->table('film_actor')->get();
            foreach ($importedRelations as $relData) {
                $data = array_intersect_key((array)$relData, array_flip($filmActorColumns));
                DB::table('film_actor')->insert($data);
            }
            Log::info(count($importedRelations) . ' Verknüpfungen importiert');

            // 6. Media Assets
            $tenantId = tenancy()->tenant->id;
            // Based on routes/tenant.php serving logic
            $targetPublicPath = base_path("storage/tenant{$tenantId}/app/public");

            if (!File::isDirectory($targetPublicPath)) {
                File::makeDirectory($targetPublicPath, 0755, true);
            }

            $mediaFolders = ['covers', 'backdrops', 'actors'];
            foreach ($mediaFolders as $folder) {
                $sourcePath = $tempDir . DIRECTORY_SEPARATOR . $folder;
                if (File::isDirectory($sourcePath)) {
                    $targetPath = $targetPublicPath . DIRECTORY_SEPARATOR . $folder;
                    if (!File::isDirectory($targetPath)) {
                        File::makeDirectory($targetPath, 0755, true);
                    }
                    File::copyDirectory($sourcePath, $targetPath);
                    Log::info("Medien-Ordner '{$folder}' kopiert nach: " . $targetPath);
                }
            }

            DB::commit();
            File::deleteDirectory($tempDir);
            Log::info('SaaS Backup Import erfolgreich abgeschlossen');

            return back()->with('success', 'Backup erfolgreich importiert! ' . count($importedMovies) . ' Filme und ' . count($importedActors) . ' Schauspieler hinzugefügt.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SaaS Backup Import Error: ' . $e->getMessage());
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            return back()->with('error', 'Fehler beim Importieren der Daten: ' . $e->getMessage());
        }
    }
}
