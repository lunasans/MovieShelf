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
            $name = trim($matches[1]);
            
            // Cache lookup to avoid redundant queries in loops (e.g. list items)
            $cacheKey = 'actor_link_'.md5($name);
            
            return Cache::remember($cacheKey, now()->addHours(24), function () use ($name) {
                // Find Actor by full name (first_name space last_name)
                // Using a split to be more precise if possible
                $parts = explode(' ', $name, 2);
                
                $query = Actor::query();
                
                if (count($parts) === 2) {
                    $query->where(function ($q) use ($parts) {
                        $q->where('first_name', $parts[0])
                          ->where('last_name', $parts[1]);
                    });
                } else {
                    $query->where('first_name', $name)
                          ->orWhere('last_name', $name);
                }

                $actor = $query->first();

                if ($actor) {
                    $url = route('actors.show', $actor);
                    return '<a href="'.$url.'" class="text-blue-400 hover:text-blue-300 transition-colors font-medium border-b border-blue-400/30 hover:border-blue-300">'.$name.'</a>';
                }

                return $name; // Fallback to plain text
            });
        }, $text);
    }
}
