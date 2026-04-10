<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupController extends Controller
{
    public function export()
    {
        Log::info('Backup export started');
        $zipFileName = 'movieshelf_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $tempZip = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'msb_' . uniqid() . '.zip';
        
        $zip = new ZipArchive();

        if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            Log::info('ZipArchive opened locally: ' . $tempZip);

            // 1. Add Database
            $dbPath = database_path('database.sqlite');
            if (file_exists($dbPath)) {
                $tempDb = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'db_' . uniqid() . '.sqlite';
                copy($dbPath, $tempDb);
                $zip->addFile($tempDb, 'database.sqlite');
                Log::info('Database added to ZIP');
            }

            // 2. Add Media Folders
            $mediaFolders = ['covers', 'backdrops', 'actors'];
            $publicPath = storage_path('app/public');

            foreach ($mediaFolders as $folder) {
                $folderPath = $publicPath . DIRECTORY_SEPARATOR . $folder;
                if (is_dir($folderPath)) {
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($folderPath),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );

                    foreach ($files as $name => $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = $folder . '/' . str_replace('\\', '/', substr($filePath, strlen($folderPath) + 1));
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                }
            }

            if (!$zip->close()) {
                Log::error('ZipArchive close failed: ' . $zip->getStatusString());
                return back()->with('error', 'Fehler beim Finalisieren des Backups: ' . $zip->getStatusString());
            }

            Log::info('ZipArchive closed successfully');

            if (isset($tempDb) && file_exists($tempDb)) {
                @unlink($tempDb);
            }

            if (file_exists($tempZip)) {
                $size = filesize($tempZip);
                Log::info('Serving zip for download: ' . $zipFileName . ' (' . $size . ' bytes)');
                
                return response()->download($tempZip, $zipFileName, [
                    'Content-Type' => 'application/zip',
                    'Content-Length' => $size,
                    'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
                ]);
                // Note: Not using deleteFileAfterSend(true) here as it causes 500 errors on some Windows setups
            }
        }

        Log::error('Failed to open ZipArchive at ' . $tempZip);
        return back()->with('error', 'Fehler beim Erstellen des Backups.');
    }
}
