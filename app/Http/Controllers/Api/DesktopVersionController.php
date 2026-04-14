<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesktopRelease;

class DesktopVersionController extends Controller
{
    /**
     * Gibt das aktuellste öffentliche Desktop-Release zurück.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $release = DesktopRelease::latestPublic();

        if (!$release) {
            return response()->json([
                'version' => '0.0.0',
                'url' => null,
                'changelog' => 'No releases found.'
            ]);
        }

        return response()->json([
            'version'      => $release->version,
            'url'          => $release->download_url,
            'sha256'       => $release->file_hash,
            'changelog'    => $release->changelog,
            'published_at' => $release->created_at->toIso8601String(),
        ]);
    }
}
