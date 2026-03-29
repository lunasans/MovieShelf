<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    const ERR_API_KEY_MISSING = 'TMDb API Key nicht konfiguriert.';

    const ERR_API_REQUEST_FAILED = 'API-Anfrage fehlgeschlagen: ';

    const ERR_CONNECTION_FAILED = 'Verbindung zur TMDb fehlgeschlagen.';

    protected string $apiKey;

    protected string $baseUrl = 'https://api.themoviedb.org/3';

    protected string $language = 'de-DE';

    public function __construct()
    {
        $this->apiKey = Setting::get('tmdb_api_key', '');
    }

    /**
     * Helper to execute API requests
     */
    private function executeRequest(string $endpoint, array $params = [], string $errorPrefix = 'TMDb Error'): array
    {
        if (empty($this->apiKey)) {
            return ['error' => self::ERR_API_KEY_MISSING];
        }

        try {
            $params['api_key'] = $this->apiKey;
            $params['language'] = $this->language;

            $response = Http::withOptions([
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])->get("{$this->baseUrl}{$endpoint}", $params);

            return $response->successful()
                ? $response->json()
                : ['error' => self::ERR_API_REQUEST_FAILED.$response->status()];
        } catch (\Exception $e) {
            Log::error("{$errorPrefix}: ".$e->getMessage());

            return ['error' => self::ERR_CONNECTION_FAILED];
        }
    }

    /**
     * Search for movies by title
     */
    public function searchMovie(string $query, ?int $year = null, int $page = 1): array
    {
        $params = [
            'query' => $query,
            'page' => $page,
            'include_adult' => false,
        ];

        if ($year) {
            $params['primary_release_year'] = $year;
        }

        return $this->executeRequest('/search/movie', $params, 'TMDb Search Error');
    }

    /**
     * Get detailed movie information including credits
     */
    public function getMovieDetails(int $tmdbId): array
    {
        return $this->executeRequest("/movie/{$tmdbId}", [
            'append_to_response' => 'credits,videos,release_dates',
        ], 'TMDb Details Error');
    }

    /**
     * Get detailed person information
     */
    public function getPersonDetails(int $personId): array
    {
        return $this->executeRequest("/person/{$personId}", [], 'TMDb Person Details Error');
    }

    /**
     * Search for a person by name
     */
    public function searchPerson(string $query, int $page = 1): array
    {
        $params = [
            'query' => $query,
            'page' => $page,
            'include_adult' => false,
        ];

        return $this->executeRequest('/search/person', $params, 'TMDb Person Search Error');
    }

    /**
     * Search for TV shows by title
     */
    public function searchTv(string $query, ?int $year = null, int $page = 1): array
    {
        $params = [
            'query' => $query,
            'page' => $page,
            'include_adult' => false,
        ];

        if ($year) {
            $params['first_air_date_year'] = $year;
        }

        return $this->executeRequest('/search/tv', $params, 'TMDb TV Search Error');
    }

    /**
     * Get detailed TV show information including credits
     */
    public function getTvDetails(int $tmdbId): array
    {
        return $this->executeRequest("/tv/{$tmdbId}", [
            'append_to_response' => 'credits,videos,content_ratings',
        ], 'TMDb TV Details Error');
    }

    /**
     * Get detailed season information including episodes
     */
    public function getSeasonDetails(int $tmdbId, int $seasonNumber): array
    {
        return $this->executeRequest("/tv/{$tmdbId}/season/{$seasonNumber}", [], 'TMDb Season Details Error');
    }

    /**
     * Get configuration for image base URLs
     */
    public function getConfiguration(): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        return cache()->remember('tmdb_config', 86400, function () {
            $response = Http::withOptions([
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])->get("{$this->baseUrl}/configuration", [
                'api_key' => $this->apiKey,
            ]);

            return $response->successful() ? $response->json() : [];
        });
    }
}
