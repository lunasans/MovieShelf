<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeSearchService
{
    /**
     * Search for a trailer on YouTube without an API key.
     *
     * @param string $title
     * @param int|null $year
     * @param string $suffix
     * @return string|null YouTube URL or null
     */
    public function searchTrailer(string $title, ?int $year = null, string $suffix = 'Trailer Deutsch'): ?string
    {
        $query = trim("{$title} " . ($year ? "{$year} " : "") . $suffix);
        $url = "https://www.youtube.com/results?search_query=" . urlencode($query);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => 'de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
            ])->get($url);

            if ($response->failed()) {
                Log::warning("YouTube Search failed for query: {$query}");
                return null;
            }

            $html = $response->body();

            // Pattern for video ID in YouTube search results JSON (rendered in HTML)
            if (preg_match('/"videoRenderer":\{"videoId":"([^"]+)"/', $html, $matches)) {
                return "https://www.youtube.com/watch?v=" . $matches[1];
            }

        } catch (\Exception $e) {
            Log::error("Error during YouTube Search for query [{$query}]: " . $e->getMessage());
        }

        return null;
    }
}
