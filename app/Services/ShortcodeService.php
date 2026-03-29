<?php

namespace App\Services;

use App\Models\Actor;
use Illuminate\Support\Facades\Cache;

class ShortcodeService
{
    /**
     * Parse text for shortcodes and replace them with HTML.
     * Currently supports: {!Actor}Name
     *
     * @param string|null $text
     * @return string
     */
    public static function parse(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // Pattern for {!Actor}Name
        $pattern = '/\{!Actor\}(.+?)\}/';

        return preg_replace_callback($pattern, function ($matches) {
            $rawName = $matches[1];
            $cleanName = trim(strip_tags($rawName)); // Remove tags for DB lookup
            
            if (empty($cleanName)) {
                return $rawName;
            }

            // Cache lookup to avoid redundant queries
            $cacheKey = 'actor_link_'.md5($cleanName);
            
            return Cache::remember($cacheKey, now()->addHours(24), function () use ($cleanName, $rawName) {
                // Find Actor by full name (first_name space last_name)
                // We use a more robust search by concatenating the names in the DB
                $actor = Actor::whereRaw('LOWER(TRIM(CONCAT(first_name, " ", last_name))) = ?', [strtolower($cleanName)])
                    ->orWhereRaw('LOWER(TRIM(first_name)) = ? AND (last_name IS NULL OR last_name = "")', [strtolower($cleanName)])
                    ->first();

                if ($actor) {
                    $url = route('actors.show', $actor);
                    // We keep the $rawName (including potential tags like <strong>) for the link text
                    return '<a href="'.$url.'" class="text-blue-400 hover:text-blue-300 transition-colors font-medium border-b border-blue-400/30 hover:border-blue-300">'.$rawName.'</a>';
                }

                return $rawName; // Fallback to original text if not found
            });
        }, $text);
    }
}
