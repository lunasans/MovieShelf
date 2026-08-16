<?php

namespace Tests\Unit;

use App\Services\TmdbImportService;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TmdbImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private TmdbImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Since getGermanRating etc don't use the TmdbService, we can mock it empty
        $this->service = new TmdbImportService(new TmdbService());
    }

    public function test_get_german_rating_extracts_numbers()
    {
        $details = [
            'release_dates' => [
                'results' => [
                    [
                        'iso_3166_1' => 'DE',
                        'release_dates' => [
                            ['certification' => '16']
                        ]
                    ]
                ]
            ]
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getGermanRating');
        $method->setAccessible(true);
        $rating = $method->invokeArgs($this->service, [$details]);
        $this->assertEquals(16, $rating);
    }

    public function test_get_german_rating_returns_null_if_not_found()
    {
        $details = [
            'release_dates' => [
                'results' => [
                    [
                        'iso_3166_1' => 'US',
                        'release_dates' => [
                            ['certification' => 'PG']
                        ]
                    ]
                ]
            ]
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getGermanRating');
        $method->setAccessible(true);
        $rating = $method->invokeArgs($this->service, [$details]);
        $this->assertNull($rating);
    }

    public function test_get_german_tv_rating_extracts_numbers()
    {
        $details = [
            'content_ratings' => [
                'results' => [
                    [
                        'iso_3166_1' => 'DE',
                        'rating' => '12'
                    ]
                ]
            ]
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getGermanTvRating');
        $method->setAccessible(true);
        $rating = $method->invokeArgs($this->service, [$details]);
        $this->assertEquals(12, $rating);
    }
    
    public function test_has_latin_characters()
    {
        $this->assertTrue(TmdbImportService::hasLatinCharacters('Kenji Utsumi'));
        $this->assertTrue(TmdbImportService::hasLatinCharacters('Zoë Saldaña'));
        $this->assertFalse(TmdbImportService::hasLatinCharacters('内海賢二'));
        $this->assertFalse(TmdbImportService::hasLatinCharacters('周星馳'));
        $this->assertFalse(TmdbImportService::hasLatinCharacters(''));
    }

    public function test_resolve_actor_name_keeps_latin_name()
    {
        Http::fake();

        $result = $this->invokeResolveActorName([
            'id' => 1,
            'name' => 'Tom Hanks',
            'original_name' => 'Tom Hanks',
        ]);

        $this->assertEquals('Tom Hanks', $result['name']);
        $this->assertNull($result['original_name']);
        Http::assertNothingSent();
    }

    public function test_resolve_actor_name_falls_back_to_english_for_cjk()
    {
        \App\Models\Setting::updateOrCreate(['key' => 'tmdb_api_key'], ['value' => 'test-api-key']);
        $this->service = new TmdbImportService(new TmdbService());

        Http::fake([
            'api.themoviedb.org/3/person/149*' => Http::response(['id' => 149, 'name' => 'Kenji Utsumi'], 200),
        ]);

        $result = $this->invokeResolveActorName([
            'id' => 149,
            'name' => '内海賢二',
            'original_name' => '内海賢二',
        ]);

        $this->assertEquals('Kenji Utsumi', $result['name']);
        $this->assertEquals('内海賢二', $result['original_name']);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/person/149') && $request['language'] === 'en-US';
        });
    }

    public function test_resolve_actor_name_keeps_cjk_if_english_lookup_fails()
    {
        \App\Models\Setting::updateOrCreate(['key' => 'tmdb_api_key'], ['value' => 'test-api-key']);
        $this->service = new TmdbImportService(new TmdbService());

        Http::fake([
            'api.themoviedb.org/3/person/149*' => Http::response(['status_message' => 'Not Found'], 404),
        ]);

        $result = $this->invokeResolveActorName([
            'id' => 149,
            'name' => '内海賢二',
        ]);

        $this->assertEquals('内海賢二', $result['name']);
        $this->assertNull($result['original_name']);
    }

    private function invokeResolveActorName(array $person): array
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('resolveActorName');
        $method->setAccessible(true);

        return $method->invokeArgs($this->service, [$person]);
    }

    public function test_get_german_tv_rating_returns_null_if_not_found()
    {
        $details = [
            'content_ratings' => [
                'results' => [
                    [
                        'iso_3166_1' => 'US',
                        'rating' => 'TV-MA'
                    ]
                ]
            ]
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getGermanTvRating');
        $method->setAccessible(true);
        $rating = $method->invokeArgs($this->service, [$details]);
        $this->assertNull($rating);
    }
}
