<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupController extends Controller
{
    protected $disk = 'local';
    protected $backupPath = 'backups/ms';

    public function index()
    {
        $this->authorizeAdmin();

        if (!Storage::disk($this->disk)->exists($this->backupPath)) {
            Storage::disk($this->disk)->makeDirectory($this->backupPath);
        }

        $files = Storage::disk($this->disk)->files($this->backupPath);
        $msFiles = array_values(array_map(function ($file) {
            return [
                'name' => basename($file),
                'size' => Storage::disk($this->disk)->size($file),
                'modified' => Storage::disk($this->disk)->lastModified($file),
            ];
        }, array_filter($files, fn ($f) => str_ends_with(strtolower($f), '.ms'))));

        usort($msFiles, fn ($a, $b) => $b['modified'] <=> $a['modified']);

        return view('admin.backup.index', compact('msFiles'));
    }

    public function create()
    {
        $this->authorizeAdmin();

        try {
            $result = app(\App\Services\TenantBackupService::class)->create();
            Log::info('MovieShelf Backup erstellt: ' . $result['filename'] . ' (' . $result['movies'] . ' Filme)');

            return response()->download($result['path'], $result['filename']);
        } catch (\Throwable $e) {
            Log::error('Backup Erstellung fehlgeschlagen: ' . $e->getMessage());
            return back()->with('error', 'Backup fehlgeschlagen: ' . $e->getMessage());
        }
    }

    public function restore(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate(['backup_file' => 'required|file|max:512000']);

        $file = $request->file('backup_file');
        if (!str_ends_with(strtolower($file->getClientOriginalName()), '.ms')) {
            return back()->with('error', 'Ungültiges Dateiformat. Nur .ms-Dateien werden unterstützt.');
        }

        return $this->processMs($file->getRealPath());
    }

    public function restoreLocal(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate(['filename' => 'required|string']);

        $safeFilename = $this->sanitizeFilename($request->filename);
        $filePath = Storage::disk($this->disk)->path($this->backupPath . '/' . $safeFilename);

        if (!file_exists($filePath)) {
            return back()->with('error', 'Datei nicht auf dem Server gefunden.');
        }

        return $this->processMs($filePath);
    }

    public function destroy($filename)
    {
        $this->authorizeAdmin();

        $safeFilename = $this->sanitizeFilename($filename);
        $path = $this->backupPath . '/' . $safeFilename;

        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
            return back()->with('success', 'Backup gelöscht.');
        }

        return back()->with('error', 'Datei nicht gefunden.');
    }

    protected function processMs(string $msPath): mixed
    {
        set_time_limit(300);
        Log::info('MS Backup Restore gestartet: ' . $msPath);

        $tempDir = storage_path('app/temp_restore_' . uniqid());

        try {
            File::makeDirectory($tempDir, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($msPath) !== true) {
                return back()->with('error', 'Ungültige .ms-Datei: kein gültiges Archiv.');
            }

            // Zip-Slip protection
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->statIndex($i)['name'];
                if (str_contains($name, '..') || str_starts_with($name, '/') || str_starts_with($name, '\\')) {
                    $zip->close();
                    return back()->with('error', 'Sicherheitsrisiko: Ungültige Pfade im Archiv entdeckt.');
                }
            }

            $zip->extractTo($tempDir);
            $zip->close();

            // Validate manifest
            $manifestPath = $tempDir . '/manifest.json';
            if (!file_exists($manifestPath)) {
                File::deleteDirectory($tempDir);
                return back()->with('error', 'Ungültiges Backup: manifest.json fehlt.');
            }

            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (!isset($manifest['version']) || ($manifest['app'] ?? '') !== 'MovieShelf SaaS') {
                File::deleteDirectory($tempDir);
                return back()->with('error', 'Inkompatibles Backup-Format (kein MovieShelf SaaS Backup).');
            }

            $dbPath = $tempDir . '/database.json';
            if (!file_exists($dbPath)) {
                File::deleteDirectory($tempDir);
                return back()->with('error', 'Ungültiges Backup: database.json fehlt.');
            }

            $database = json_decode(file_get_contents($dbPath), true);
            if (!is_array($database)) {
                File::deleteDirectory($tempDir);
                return back()->with('error', 'Datenbankdatei konnte nicht gelesen werden.');
            }

            Schema::disableForeignKeyConstraints();
            DB::beginTransaction();

            // Clear and reimport core data
            DB::table('film_actor')->delete();
            DB::table('movies')->delete();
            DB::table('actors')->delete();

            $movieCols    = array_flip(Schema::getColumnListing('movies'));
            $actorCols    = array_flip(Schema::getColumnListing('actors'));
            $filmActorCols = array_flip(Schema::getColumnListing('film_actor'));

            foreach ($database['actors'] ?? [] as $row) {
                DB::table('actors')->insert(array_intersect_key($row, $actorCols));
            }

            foreach ($database['movies'] ?? [] as $row) {
                $data = array_intersect_key($row, $movieCols);
                $data['user_id'] = auth()->id();
                DB::table('movies')->insert($data);
            }

            foreach ($database['film_actor'] ?? [] as $row) {
                DB::table('film_actor')->insert(array_intersect_key($row, $filmActorCols));
            }

            // Restore optional tables
            foreach (['movie_lists', 'movie_user_watched', 'movie_user_wishlist'] as $table) {
                if (!empty($database[$table]) && Schema::hasTable($table)) {
                    DB::table($table)->delete();
                    $cols = array_flip(Schema::getColumnListing($table));
                    foreach ($database[$table] as $row) {
                        DB::table($table)->insert(array_intersect_key($row, $cols));
                    }
                }
            }

            // Merge settings (don't wipe existing ones)
            if (!empty($database['settings'])) {
                foreach ($database['settings'] as $row) {
                    DB::table('settings')->updateOrInsert(['key' => $row['key']], $row);
                }
            }

            // Restore media assets
            $targetPublicPath = storage_path('app/public');
            foreach (['covers', 'backdrops', 'actors'] as $folder) {
                $src = $tempDir . '/media/' . $folder;
                if (File::isDirectory($src)) {
                    $target = $targetPublicPath . '/' . $folder;
                    File::ensureDirectoryExists($target);
                    File::copyDirectory($src, $target);
                }
            }

            DB::commit();
            Schema::enableForeignKeyConstraints();
            File::deleteDirectory($tempDir);

            Log::info('MS Backup Restore erfolgreich. Filme: ' . count($database['movies'] ?? []) . ', User: ' . auth()->id());
            return back()->with('success', 'Backup erfolgreich wiederhergestellt! (' . count($database['movies'] ?? []) . ' Filme, ' . count($database['actors'] ?? []) . ' Schauspieler)');

        } catch (\Exception $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            Log::error('MS Restore Fehler: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Fehler beim Wiederherstellen: ' . $e->getMessage());
        }
    }

    protected function authorizeAdmin(): void
    {
        if (auth()->id() !== 1) {
            abort(403, 'Nur der Hauptadministrator darf Backups verwalten.');
        }
    }

    protected function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^A-Za-z0-9._-]/', '', basename($filename));
    }
}
