<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Gemeinsame Bild-URL-Auflösung für Movie und WishlistMovie.
 *
 * Standalone-Variante: Medien liegen ausschliesslich lokal unter
 * storage/app/public und werden ueber den /media-Proxy ausgeliefert.
 * Die Cloud kennt hier zusaetzlich eine S3- und eine 'central'-Disk –
 * beides ist Cloud-Infrastruktur und in der Selfhosted-Variante bewusst nicht
 * vorhanden.
 */
trait ResolvesMediaUrls
{
    /**
     * Resolve image URL from ID and type.
     */
    protected function resolveImageUrl($id, $type)
    {
        if (! $id) {
            return null;
        }

        $disk = Storage::disk('public');

        // Check for absolute URLs
        if (str_starts_with($id, 'http')) {
            return $id;
        }

        // 1. Check if the ID itself exists locally as a file
        if ($disk->exists($id)) {
            return '/media/' . $id;
        }

        // Rohe TMDb-Referenzen (tmdb_… oder /pfad) werden NICHT direkt von
        // image.tmdb.org geladen (kein Hotlink). Ist das Bild nicht lokal
        // vorhanden, gibt es einen Platzhalter (null).
        if (str_starts_with($id, 'tmdb_') || str_starts_with($id, '/')) {
            return null;
        }

        // 4. Modern approach: ID is a path (e.g. covers/abc.jpg)
        if (str_contains($id, '/') && str_contains($id, '.')) {
            if ($disk->exists($id)) {
                return '/media/' . $id;
            }
        }

        // 5. Legacy: Use the structured legacy path with fallback extensions
        if (($legacyUrl = $this->resolveLegacyStorageUrl($id, $type)) !== null) {
            return $legacyUrl;
        }

        return null;
    }

    protected function resolveLegacyStorageUrl($id, $type)
    {
        $disk = Storage::disk('public');

        // Check both singular and plural versions for flexibility (e.g. cover vs covers)
        $folders = ($type === 'cover') ? ['covers', 'cover'] : ['backdrops', 'backdrop'];
        $suffix = ($type === 'cover') ? 'f' : '';

        foreach ($folders as $folder) {
            // Try standard extension first
            $path = "$folder/$id$suffix.jpg";

            if ($disk->exists($path)) {
                return '/media/' . $path;
            }

            // Fallback extensions
            $extensions = ['.JPG', '.jpeg', '.JPEG', '.png', '.PNG', '.webp'];
            foreach ($extensions as $ext) {
                $fallbackPath = "$folder/$id$suffix$ext";
                if ($disk->exists($fallbackPath)) {
                    return '/media/' . $fallbackPath;
                }
            }

            // Try without suffix as a last resort
            if ($suffix !== '' && $disk->exists("$folder/$id.jpg")) {
                return '/media/' . "$folder/$id.jpg";
            }
        }

        return null;
    }
}
