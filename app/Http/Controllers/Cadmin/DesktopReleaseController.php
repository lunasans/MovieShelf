<?php

namespace App\Http\Controllers\Cadmin;

use App\Http\Controllers\Controller;
use App\Models\DesktopRelease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DesktopReleaseController extends Controller
{
    public function index()
    {
        $releases = DesktopRelease::orderBy('created_at', 'desc')->get();
        return view('cadmin.desktop.index', compact('releases'));
    }

    public function create()
    {
        return view('cadmin.desktop.form', ['release' => new DesktopRelease()]);
    }

    public function store(Request $request)
    {
        // Prüfe ob PHP die Datei wegen post_max_size still verworfen hat
        if ($request->server('CONTENT_LENGTH') > 0 && empty($_FILES) && empty($request->all())) {
            return back()->withErrors(['exe_file' => 'Die Datei ist zu groß. Bitte prüfe die PHP-Einstellung post_max_size.']);
        }

        $request->validate([
            'version'                      => 'required|string|unique:desktop_releases,version',
            'changelog'                    => 'nullable|string',
            'download_url'                 => 'nullable|string|max:500',
            'download_url_linux_appimage'  => 'nullable|string|max:500',
            'download_url_linux_deb'       => 'nullable|string|max:500',
            'file_hash'                    => 'nullable|string|max:128',
            'file_hash_linux_appimage'     => 'nullable|string|max:128',
            'file_hash_linux_deb'          => 'nullable|string|max:128',
            'exe_file'                     => [
                'nullable',
                'file',
                'max:204800', // 200MB
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['exe', 'msi', 'zip'])) {
                        $fail('Erlaubte Dateitypen: .exe, .msi, .zip');
                    }
                },
            ],
            'is_public'                    => 'nullable|boolean',
        ]);

        $data = $request->only(['version', 'changelog', 'download_url', 'download_url_linux_appimage', 'download_url_linux_deb', 'file_hash', 'file_hash_linux_appimage', 'file_hash_linux_deb']);
        $data['is_public'] = $request->boolean('is_public');

        if ($request->hasFile('exe_file')) {
            $file = $request->file('exe_file');
            $filename = 'MovieShelf_v' . preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $request->version)
                        . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('releases', $filename, 'public');
            $data['file_path'] = $path;

            if (!$data['download_url']) {
                $data['download_url'] = Storage::disk('public')->url($path);
            }
        }

        DesktopRelease::create($data);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'redirect' => route('cadmin.desktop.index')]);
        }

        return redirect()->route('cadmin.desktop.index')->with('success', 'Release wurde erfolgreich angelegt.');
    }

    public function edit(DesktopRelease $desktop)
    {
        return view('cadmin.desktop.form', ['release' => $desktop]);
    }

    public function update(Request $request, DesktopRelease $desktop)
    {
        $request->validate([
            'version'                      => 'required|string|unique:desktop_releases,version,' . $desktop->id,
            'changelog'                    => 'nullable|string',
            'download_url'                 => 'nullable|string|max:500',
            'download_url_linux_appimage'  => 'nullable|string|max:500',
            'download_url_linux_deb'       => 'nullable|string|max:500',
            'file_hash'                    => 'nullable|string|max:128',
            'file_hash_linux_appimage'     => 'nullable|string|max:128',
            'file_hash_linux_deb'          => 'nullable|string|max:128',
            'is_public'                    => 'nullable|boolean',
        ]);

        $data = $request->only(['version', 'changelog', 'download_url', 'download_url_linux_appimage', 'download_url_linux_deb', 'file_hash', 'file_hash_linux_appimage', 'file_hash_linux_deb']);
        $data['is_public'] = $request->boolean('is_public');

        $desktop->update($data);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'redirect' => route('cadmin.desktop.index')]);
        }

        return redirect()->route('cadmin.desktop.index')->with('success', 'Release wurde aktualisiert.');
    }

    public function destroy(DesktopRelease $desktop)
    {
        if ($desktop->file_path) {
            Storage::disk('public')->delete($desktop->file_path);
        }
        $desktop->delete();
        return back()->with('success', 'Release wurde gelöscht.');
    }

    /**
     * Setzt Chunks einer einzelnen Datei zusammen und gibt URL + SHA-256 zurück.
     * Legt kein Release an – das übernimmt store()/update().
     */
    public function assembleFile(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'upload_id'    => 'required|string|alpha_num|max:64',
                'total_chunks' => 'required|integer|min:1',
                'filename'     => 'required|string|max:255',
                'version'      => 'required|string|max:50',
                'platform'     => 'required|in:win,appimage,deb',
            ]);

            $uploadId    = $request->input('upload_id');
            $totalChunks = (int) $request->input('total_chunks');
            $ext         = pathinfo($request->input('filename'), PATHINFO_EXTENSION);
            $safeVersion = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $request->input('version'));
            $platform    = $request->input('platform');

            $suffix = match ($platform) {
                'appimage' => '_linux',
                'deb'      => '_linux_deb',
                default    => '',
            };

            $finalName = "MovieShelf_v{$safeVersion}{$suffix}.{$ext}";
            $finalPath = storage_path("app/public/releases/{$finalName}");
            $tmpDir    = storage_path("app/chunks/{$uploadId}");

            if (!is_dir(dirname($finalPath))) {
                mkdir(dirname($finalPath), 0755, true);
            }

            $out = fopen($finalPath, 'wb');
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkFile = "{$tmpDir}/chunk_{$i}";
                if (!file_exists($chunkFile)) {
                    fclose($out);
                    return response()->json(['error' => "Chunk {$i} fehlt."], 422);
                }
                fwrite($out, file_get_contents($chunkFile));
                unlink($chunkFile);
            }
            fclose($out);
            @rmdir($tmpDir);

            $storagePath = "releases/{$finalName}";

            return response()->json([
                'ok'   => true,
                'url'  => Storage::disk('public')->url($storagePath),
                'hash' => hash_file('sha256', $finalPath),
                'path' => $storagePath,
            ]);

        } catch (\Throwable $e) {
            \Log::error('assembleFile error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Empfängt einen einzelnen Chunk und speichert ihn temporär.
     */
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'chunk'       => 'required|file',
            'upload_id'   => 'required|string|alpha_num|max:64',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks'=> 'required|integer|min:1',
            'filename'    => 'required|string|max:255',
        ]);

        $uploadId   = $request->input('upload_id');
        $chunkIndex = (int) $request->input('chunk_index');
        $tmpDir     = storage_path("app/chunks/{$uploadId}");

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $request->file('chunk')->move($tmpDir, "chunk_{$chunkIndex}");

        return response()->json(['ok' => true, 'chunk' => $chunkIndex]);
    }

    /**
     * Setzt alle Chunks zusammen und legt das Release an.
     */
    public function finalizeUpload(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'upload_id'                    => 'required|string|alpha_num|max:64',
                'total_chunks'                 => 'required|integer|min:1',
                'filename'                     => 'required|string|max:255',
                'version'                      => 'required|string|unique:desktop_releases,version',
                'changelog'                    => 'nullable|string',
                'download_url'                 => 'nullable|string|max:500',
                'download_url_linux_appimage'  => 'nullable|string|max:500',
                'download_url_linux_deb'       => 'nullable|string|max:500',
                'file_hash_linux_appimage'     => 'nullable|string|max:128',
                'file_hash_linux_deb'          => 'nullable|string|max:128',
                'is_public'                    => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first(),
                ], 422);
            }

            $uploadId    = $request->input('upload_id');
            $totalChunks = (int) $request->input('total_chunks');
            $tmpDir      = storage_path("app/chunks/{$uploadId}");
            $ext         = pathinfo($request->input('filename'), PATHINFO_EXTENSION);
            $safeVersion = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $request->input('version'));
            $finalName   = "MovieShelf_v{$safeVersion}.{$ext}";
            $finalPath   = storage_path("app/public/releases/{$finalName}");

            if (!is_dir(dirname($finalPath))) {
                mkdir(dirname($finalPath), 0755, true);
            }

            // Zusammenfügen
            $out = fopen($finalPath, 'wb');
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkFile = "{$tmpDir}/chunk_{$i}";
                if (!file_exists($chunkFile)) {
                    fclose($out);
                    return response()->json(['error' => "Chunk {$i} fehlt."], 422);
                }
                fwrite($out, file_get_contents($chunkFile));
                unlink($chunkFile);
            }
            fclose($out);
            @rmdir($tmpDir);

            $storagePath = "releases/{$finalName}";
            $downloadUrl = $request->input('download_url') ?: Storage::disk('public')->url($storagePath);
            $fileHash    = hash_file('sha256', $finalPath);

            $release = DesktopRelease::create([
                'version'                     => $request->input('version'),
                'changelog'                   => $request->input('changelog'),
                'download_url'                => $downloadUrl,
                'download_url_linux_appimage' => $request->input('download_url_linux_appimage'),
                'download_url_linux_deb'      => $request->input('download_url_linux_deb'),
                'file_path'                   => $storagePath,
                'file_hash'                   => $fileHash,
                'file_hash_linux_appimage'    => $request->input('file_hash_linux_appimage'),
                'file_hash_linux_deb'         => $request->input('file_hash_linux_deb'),
                'is_public'                   => $request->boolean('is_public'),
            ]);

            return response()->json([
                'ok'       => true,
                'release'  => $release->id,
                'redirect' => route('cadmin.desktop.index'),
            ]);

        } catch (\Throwable $e) {
            \Log::error('finalizeUpload error: ' . $e->getMessage());
            return response()->json(['error' => 'Serverfehler: ' . $e->getMessage()], 500);
        }
    }
}
