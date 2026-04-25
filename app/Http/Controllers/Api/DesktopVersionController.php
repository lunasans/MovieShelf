<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesktopRelease;
use Illuminate\Http\Request;

class DesktopVersionController extends Controller
{
    public function index(Request $request)
    {
        $release = DesktopRelease::latestPublic();

        if (!$release) {
            return response()->json(['version' => '0.0.0', 'url' => null]);
        }

        $platform = $request->query('platform', 'win');

        $url    = $platform === 'linux' ? $release->download_url_linux_deb  : $release->download_url;
        $sha256 = $platform === 'linux' ? $release->file_hash_linux_deb     : $release->file_hash;

        return response()->json([
            'version'      => $release->version,
            'url'          => $url,
            'sha256'       => $sha256,
            'changelog'    => $release->changelog,
            'published_at' => $release->created_at->toIso8601String(),
        ]);
    }

    /**
     * Webhook – wird von GitHub Actions nach einem Release aufgerufen.
     * Header: Authorization: Bearer <DESKTOP_WEBHOOK_SECRET>
     */
    public function webhook(Request $request)
    {
        $secret = config('app.desktop_webhook_secret');
        if (!$secret || $request->bearerToken() !== $secret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'version'                => 'required|string|max:50',
            'download_url'           => 'nullable|string|max:500',
            'download_url_linux_deb' => 'nullable|string|max:500',
            'file_hash'              => 'nullable|string|max:128',
            'file_hash_linux_deb'    => 'nullable|string|max:128',
            'changelog'              => 'nullable|string',
        ]);

        $existing = DesktopRelease::where('version', $request->version)->first();

        if ($existing) {
            $existing->update([
                'download_url'           => $request->download_url,
                'download_url_linux_deb' => $request->download_url_linux_deb,
                'file_hash'              => $request->file_hash,
                'file_hash_linux_deb'    => $request->file_hash_linux_deb,
                'changelog'              => $request->changelog,
            ]);
            return response()->json(['ok' => true, 'action' => 'updated']);
        }

        DesktopRelease::create([
            'version'                => $request->version,
            'download_url'           => $request->download_url,
            'download_url_linux_deb' => $request->download_url_linux_deb,
            'file_hash'              => $request->file_hash,
            'file_hash_linux_deb'    => $request->file_hash_linux_deb,
            'changelog'              => $request->changelog,
            'is_public'              => true,
        ]);

        return response()->json(['ok' => true, 'action' => 'created'], 201);
    }
}
