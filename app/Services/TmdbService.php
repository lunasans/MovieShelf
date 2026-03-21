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
     * Search for movies by title
     */
    public function searchMovie(string $query, ?int $year = null, int $page = 1): array
    {
        if (empty($this->apiKey)) {
            return ['error' => self::ERR_API_KEY_MISSING];
        }

        try {
            $params = [
                'api_key' => $this->apiKey,
                'query' => $query,
                'language' => $this->language,
                'page' => $page,
                'include_adult' => false,
            ];

            if ($year) {
                $params['primary_release_year'] = $year;
            }

            $response = Http::get("{$this->baseUrl}/search/movie", $params);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => self::ERR_API_REQUEST_FAILED.$response->status()];
        } catch (\Exception $e) {
            Log::error('TMDb Search Error: '.$e->getMessage());

            return ['error' => self::ERR_CONNECTION_FAILED];
        }
    }

    /**
     * Get detailed movie information including credits
     */
    public function getMovieDetails(int $tmdbId): array
    {
        if (empty($this->apiKey)) {
            return ['error' => self::ERR_API_KEY_MISSING];
        }

        try {
            $response = Http::get("{$this->baseUrl}/movie/{$tmdbId}", [
                'api_key' => $this->apiKey,
                'language' => $this->language,
                'append_to_response' => 'credits,videos,release_dates',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => self::ERR_API_REQUEST_FAILED.$response->status()];
        } catch (\Exception $e) {
            Log::error('TMDb Details Error: '.$e->getMessage());

            return ['error' => self::ERR_CONNECTION_FAILED];
        }
    }

    /**
     * Get detailed person information
     */
    public function getPersonDetails(int $personId): array
    {
        if (empty($this->apiKey)) {
            return ['error' => self::ERR_API_KEY_MISSING];
        }

        try {
            $response = Http::get("{$this->baseUrl}/person/{$personId}", [
                'api_key' => $this->apiKey,
                'language' => $this->language,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => self::ERR_API_REQUEST_FAILED.$response->status()];
        } catch (\Exception $e) {
            Log::error('TMDb Person Details Error: '.$e->getMessage());

            return ['error' => self::ERR_CONNECTION_FAILED];
        }
    }

    /**
     * Search for TV shows by title
     */
    public function searchTv(string $query, ?int $year = null, int $page = 1): array
    {
        if (empty($this->apiKey)) {
            return ['error' => self::ERR_API_KEY_MISSING];
        }

        try {
            $params = [
                'api_key' => $this->apiKey,
                'query' => $query,
                'language' => $this->language,
                'page' => $page,
                'include_adult' => false,
            ];

            if ($year) {
                $params['first_air_date_year'] = $year;
            }

            $response = Http::get("{$this->baseUrl}/search/tv", $params);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => self::ERR_API_REQUEST_FAILED.$response->status()];
        } catch (\Exception $e) {
            Log::error('TMDb TV Search Error: '.$e->getMessage());

            return ['error' => self::ERR_CONNECTION_FAILED];
        }
    }

    /**
     * Get detailed TV show information including credits
     */
    public function getTvDetails(int $tmdbId): array
    {
        if (empty($this->apiKey)) {
            return ['error' => self::ERR_API_KEY_MISSING];
        }

        try {
            $response = Http::get("{$this->baseUrl}/tv/{$tmdbId}", [
                'api_key' => $this->apiKey,
                'language' => $this->language,
                'append_to_response' => 'credits,videos,content_ratings',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => self::ERR_API_REQUEST_FAILED.$response->status()];
        } catch (\Exception $e) {
            Log::error('TMDb TV Details Error: '.$e->getMessage());

            return ['error' => self::ERR_CONNECTION_FAILED];
        }
    }

    /**
     * Get detailed season information including episodes
     */
    public function getSeasonDetails(int $tmdbId, int $seasonNumber): array
    {
        if (empty($this->apiKey)) {
            return ['error' => self::ERR_API_KEY_MISSING];
        }

        try {
            $response = Http::get("{$this->baseUrl}/tv/{$tmdbId}/season/{$seasonNumber}", [
                'api_key' => $this->apiKey,
                'language' => $this->language,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => self::ERR_API_REQUEST_FAILED.$response->status()];
        } catch (\Exception $e) {
            Log::error('TMDb Season Details Error: '.$e->getMessage());

            return ['error' => self::ERR_CONNECTION_FAILED];
        }
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
            $response = Http::get("{$this->baseUrl}/configuration", [
                'api_key' => $this->apiKey,
            ]);

            return $response->successful() ? $response->json() : [];
        });
    }
}
