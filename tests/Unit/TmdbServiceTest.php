<?php

namespace Tests\Unit;

use App\Services\TmdbService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TmdbServiceTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    private TmdbService $service;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\Setting::updateOrCreate(['key' => 'tmdb_api_key'], ['value' => 'test-api-key']);
        $this->service = new TmdbService();
    }

    public function test_search_movie_returns_results()
    {
        Http::fake([
            'api.themoviedb.org/3/search/movie*' => Http::response(['results' => [['id' => 123, 'title' => 'Test Movie']]], 200),
        ]);

        $response = $this->service->searchMovie('Test');
        
        $this->assertArrayHasKey('results', $response);
        $this->assertEquals('Test Movie', $response['results'][0]['title']);
    }

    public function test_api_key_missing_returns_error()
    {
        \App\Models\Setting::updateOrCreate(['key' => 'tmdb_api_key'], ['value' => '']);
        $service = new TmdbService();
        
        $response = $service->searchMovie('Test');
        
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('TMDb API Key nicht konfiguriert.', $response['error']);
    }

    public function test_http_request_fails_returns_error()
    {
        Http::fake([
            'api.themoviedb.org/3/movie/*' => Http::response(['status_message' => 'Not Found'], 404),
        ]);

        $response = $this->service->getMovieDetails(12345);
        
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('API-Anfrage fehlgeschlagen', $response['error']);
    }

    public function test_different_endpoints_use_execute_request()
    {
        Http::fake([
            'api.themoviedb.org/3/search/tv*' => Http::response(['results' => [['id' => 1, 'name' => 'Test TV']]], 200),
        ]);

        $response = $this->service->searchTv('Test TV', 2020);
        
        $this->assertArrayHasKey('results', $response);
        $this->assertEquals('Test TV', $response['results'][0]['name']);
    }


}
