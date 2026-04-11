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

        // Pattern for {!Actor}Name} or [Actor:Name] - Case-insensitive and robust
        $pattern = '/\{!Actor\}(.*?)[\}\]]/i';

        return preg_replace_callback($pattern, function ($matches) {
            $rawName = $matches[1];
            $cleanName = trim(strip_tags($rawName)); // Remove tags for DB lookup
            
            if (empty($cleanName)) {
                return $rawName;
            }

            // Cache lookup for 24 hours
            $cacheKey = 'actor_link_'.md5($cleanName);
            
            return Cache::remember($cacheKey, now()->addHours(24), function () use ($cleanName, $rawName) {
                // Split name by space to handle first/last name search more robustly
                $parts = preg_split('/\s+/', $cleanName, 2);
                $query = Actor::query();

                if (count($parts) === 2) {
                    $firstName = trim($parts[0]);
                    $lastName = trim($parts[1]);
                    
                    $query->where(function ($q) use ($firstName, $lastName, $cleanName) {
                        // Standard match
                        $q->where('first_name', 'like', $firstName)
                          ->where('last_name', 'like', $lastName);
                    })->orWhere(function ($q) use ($cleanName) {
                        // Match full name in first_name (sometimes happens on imports)
                        $q->where('first_name', 'like', $cleanName)
                          ->where(function($sub) { $sub->whereNull('last_name')->orWhere('last_name', ''); });
                    });
                } else {
                    $query->where('first_name', 'like', $cleanName)
                          ->orWhere('last_name', 'like', $cleanName);
                }

                $actor = $query->first();

                if ($actor) {
                    $url = route('actors.show', $actor);
                    return '<a href="'.e($url).'" class="text-blue-400 hover:text-blue-300 transition-colors font-medium border-b border-blue-400/30 hover:border-blue-300">'.e($rawName).'</a>';
                }

                return $rawName; // Fallback
            });
        }, $text);
    }
}
