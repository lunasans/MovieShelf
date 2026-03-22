<?php

namespace Tests\Unit;

use App\Services\TmdbImportService;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
