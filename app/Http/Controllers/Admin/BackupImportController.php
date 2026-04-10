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
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupImportController extends Controller
{
    protected $importDisk = 'local';
    protected $importPath = 'backups/import';

    public function index()
    {
        $this->authorizeAdmin();

        // Ensure directory exists
        if (!Storage::disk($this->importDisk)->exists($this->importPath)) {
            Storage::disk($this->importDisk)->makeDirectory($this->importPath);
        }

        $files = Storage::disk($this->importDisk)->files($this->importPath);
        $zipFiles = array_map(function ($file) {
            return [
                'name' => basename($file),
                'size' => Storage::disk($this->importDisk)->size($file),
                'modified' => Storage::disk($this->importDisk)->lastModified($file),
            ];
        }, array_filter($files, fn ($f) => str_ends_with(strtolower($f), '.zip')));

        usort($zipFiles, fn ($a, $b) => $b['modified'] <=> $a['modified']);

        return view('admin.import.backup', compact('zipFiles'));
    }

    public function import(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:512000',
        ]);

        return $this->processZip($request->file('backup_file')->getRealPath());
    }

    public function importLocal(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'filename' => 'required|string',
        ]);

        $safeFilename = $this->sanitizeFilename($request->filename);
        $filePath = Storage::disk($this->importDisk)->path($this->importPath . '/' . $safeFilename);
        
        if (!file_exists($filePath)) {
            return back()->with('error', 'Datei nicht auf dem Server gefunden.');
        }

        return $this->processZip($filePath);
    }

    protected function processZip($zipPath)
    {
        Log::info('SaaS Backup Import gestartet von: ' . $zipPath);
        $tempDir = storage_path('app/temp_import_' . uniqid());
        
        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) === TRUE) {
            // Security Check: Zip Slip protection
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = $stat['name'];
                if (strpos($name, '..') !== false || str_starts_with($name, '/') || str_starts_with($name, '\\')) {
                    $zip->close();
                    File::deleteDirectory($tempDir);
                    Log::warning('Sicherheitsrisiko blockiert: Ungültiger Dateipfad im ZIP: ' . $name);
                    return back()->with('error', 'Sicherheitsrisiko: Ungültige Pfade im ZIP-Archiv entdeckt.');
                }
            }

            $zip->extractTo($tempDir);
            $zip->close();
        } else {
            return back()->with('error', 'Fehler beim Entpacken des ZIP-Archivs.');
        }

        $importedDbPath = $tempDir . DIRECTORY_SEPARATOR . 'database.sqlite';
        if (!file_exists($importedDbPath)) {
            File::deleteDirectory($tempDir);
            return back()->with('error', 'Ungültiges MovieShelf-Backup (database.sqlite fehlt).');
        }

        // Security Check: Validate SQLite Header
        if (!$this->isValidSqlite($importedDbPath)) {
            File::deleteDirectory($tempDir);
            Log::warning('Sicherheitsrisiko blockiert: database.sqlite ist keine gültige SQLite-Datei');
            return back()->with('error', 'Die Datenbank im Backup ist beschädigt oder keine gültige SQLite-Datei.');
        }

        try {
            config(['database.connections.import_aux' => [
                'driver' => 'sqlite',
                'database' => $importedDbPath,
                'prefix' => '',
            ]]);
            DB::purge('import_aux');

            DB::beginTransaction();

            // Clear tables (Tenant scope)
            DB::table('film_actor')->delete();
            DB::table('movies')->delete();
            DB::table('actors')->delete();
            
            $movieColumns = Schema::getColumnListing('movies');
            $actorColumns = Schema::getColumnListing('actors');
            $filmActorColumns = Schema::getColumnListing('film_actor');

            // Actors
            $importedActors = DB::connection('import_aux')->table('actors')->get();
            foreach ($importedActors as $actorData) {
                $data = array_intersect_key((array)$actorData, array_flip($actorColumns));
                DB::table('actors')->insert($data);
            }

            // Movies
            $importedMovies = DB::connection('import_aux')->table('movies')->get();
            foreach ($importedMovies as $movieData) {
                $data = array_intersect_key((array)$movieData, array_flip($movieColumns));
                $data['user_id'] = auth()->id(); // Ensure tenant owner association
                DB::table('movies')->insert($data);
            }

            // Relations
            $importedRelations = DB::connection('import_aux')->table('film_actor')->get();
            foreach ($importedRelations as $relData) {
                $data = array_intersect_key((array)$relData, array_flip($filmActorColumns));
                DB::table('film_actor')->insert($data);
            }

            // Media
            $tenantId = tenancy()->tenant->id;
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
                }
            }

            DB::commit();
            File::deleteDirectory($tempDir);
            Log::info('Backup Import erfolgreich abgeschlossen für Tenant: ' . $tenantId);

            return back()->with('success', 'Backup erfolgreich importiert! ' . count($importedMovies) . ' Filme hinzugefügt.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Backup Import Error: ' . $e->getMessage());
            if (File::isDirectory($tempDir)) { File::deleteDirectory($tempDir); }
            return back()->with('error', 'Fehler beim Importieren: ' . $e->getMessage());
        }
    }

    public function destroy($filename)
    {
        $this->authorizeAdmin();

        $safeFilename = $this->sanitizeFilename($filename);
        $path = $this->importPath . '/' . $safeFilename;

        if (Storage::disk($this->importDisk)->exists($path)) {
            Storage::disk($this->importDisk)->delete($path);
            return back()->with('success', 'Backup-Datei gelöscht.');
        }
        return back()->with('error', 'Datei nicht gefunden.');
    }

    // --- Security Helpers ---

    protected function authorizeAdmin()
    {
        // In this architecture, the tenant administrator is usually User ID 1
        if (auth()->id() !== 1) {
            abort(403, 'Nur der Hauptadministrator darf Backups einspielen.');
        }
    }

    protected function sanitizeFilename($filename)
    {
        $filename = basename($filename);
        // Remove everything except basic file characters to prevent directory traversal
        return preg_replace('/[^A-Za-z0-9._-]/', '', $filename);
    }

    protected function isValidSqlite($path)
    {
        if (!file_exists($path)) return false;
        $handle = fopen($path, 'rb');
        if (!$handle) return false;
        $header = fread($handle, 16);
        fclose($handle);
        return str_starts_with($header, "SQLite format 3");
    }
}
