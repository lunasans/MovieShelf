<?php

namespace Tests\Feature\Services;

use App\Models\Actor;
use App\Models\Movie;
use App\Models\User;
use App\Services\TmdbImportService;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class TmdbImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TmdbImportService $service;
    protected MockInterface $tmdbMock;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmdbMock = $this->mock(TmdbService::class);
        $this->service = new TmdbImportService($this->tmdbMock);
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Http::fake([
            'image.tmdb.org/*' => Http::response('fake-image-content', 200),
        ]);
        Storage::fake('public');
    }

    public function test_import_movie_success()
    {
        $tmdbId = 123;
        $movieData = [
            'title' => 'Test Movie',
            'release_date' => '2024-01-01',
            'vote_average' => 8.5,
            'genres' => [['name' => 'Action'], ['name' => 'Sci-Fi']],
            'runtime' => 120,
            'overview' => 'Test Overview',
            'poster_path' => '/poster.jpg',
            'backdrop_path' => '/backdrop.jpg',
            'credits' => [
                'cast' => [
                    [
                        'id' => 1,
                        'name' => 'Actor One',
                        'character' => 'Role One',
                        'order' => 0,
                        'profile_path' => '/actor1.jpg',
                    ]
                ],
                'crew' => [
                    ['job' => 'Director', 'name' => 'Director Name']
                ]
            ],
            'release_dates' => [
                'results' => [
                    [
                        'iso_3166_1' => 'DE',
                        'release_dates' => [['certification' => 'FSK 12']]
                    ]
                ]
            ]
        ];

        $this->tmdbMock->shouldReceive('getMovieDetails')
            ->with($tmdbId)
            ->once()
            ->andReturn($movieData);

        $movie = $this->service->importMovie($tmdbId);

        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals('Test Movie', $movie->title);
        $this->assertEquals(2024, $movie->year);
        $this->assertEquals(12, $movie->rating_age);
        $this->assertEquals('Director Name', $movie->director);
        $this->assertEquals('Action, Sci-Fi', $movie->genre);
        $this->assertEquals($this->user->id, $movie->user_id);
        
        $this->assertCount(1, $movie->actors);
        $this->assertEquals('Actor One', $movie->actors->first()->full_name);
        
        Storage::disk('public')->assertExists($movie->cover_id);
        Storage::disk('public')->assertExists($movie->backdrop_id);
    }

    public function test_import_tv_success()
    {
        $tmdbId = 456;
        $tvData = [
            'name' => 'Test TV Show',
            'first_air_date' => '2023-01-01',
            'vote_average' => 9.0,
            'genres' => [['name' => 'Drama']],
            'episode_run_time' => [45],
            'overview' => 'TV Overview',
            'poster_path' => '/tv_poster.jpg',
            'created_by' => [['name' => 'Creator Name']],
            'credits' => ['cast' => []],
            'seasons' => [
                ['season_number' => 1, 'name' => 'Season 1', 'overview' => 'S1 Overview']
            ],
            'content_ratings' => [
                'results' => [['iso_3166_1' => 'DE', 'rating' => '16']]
            ]
        ];

        $this->tmdbMock->shouldReceive('getTvDetails')
            ->with($tmdbId)
            ->once()
            ->andReturn($tvData);

        $this->tmdbMock->shouldReceive('getSeasonDetails')
            ->with($tmdbId, 1)
            ->once()
            ->andReturn(['episodes' => [['episode_number' => 1, 'name' => 'Ep 1', 'overview' => 'E1 O']]]);

        $movie = $this->service->importTv($tmdbId, [1]);

        $this->assertEquals('Test TV Show', $movie->title);
        $this->assertEquals('Serie', $movie->collection_type);
        $this->assertEquals(16, $movie->rating_age);
        
        $this->assertCount(1, $movie->seasons);
        $this->assertCount(1, $movie->seasons->first()->episodes);
    }

    public function test_bulk_update_success()
    {
        $movie = Movie::factory()->create(['tmdb_id' => 123, 'tmdb_type' => 'movie']);
        
        $updateData = [
            'title' => 'Updated Title',
            'release_date' => '2025-01-01',
            'vote_average' => 9.5,
            'genres' => [['name' => 'Action']],
            'runtime' => 130,
            'overview' => 'Updated Overview',
            'credits' => ['cast' => []],
            'release_dates' => ['results' => []]
        ];

        $this->tmdbMock->shouldReceive('getMovieDetails')
            ->with($movie->tmdb_id)
            ->once()
            ->andReturn($updateData);

        $this->service->bulkUpdate($movie);

        $this->assertEquals('Updated Title', $movie->fresh()->title);
        $this->assertEquals(2025, $movie->fresh()->year);
    }

    public function test_extract_rating_logic()
    {
        // German Movie Rating
        $data = ['release_dates' => ['results' => [['iso_3166_1' => 'DE', 'release_dates' => [['certification' => 'FSK 18']]]]]];
        $this->assertEquals(18, $this->service->extractRating($data));

        // German TV Rating
        $data = ['content_ratings' => ['results' => [['iso_3166_1' => 'DE', 'rating' => '12']]]];
        $this->assertEquals(12, $this->service->extractRating($data));

        // US Fallback
        $data = ['content_ratings' => ['results' => [['iso_3166_1' => 'US', 'rating' => 'TV-14']]]];
        $this->assertEquals(12, $this->service->extractRating($data));
    }

    public function test_clean_title()
    {
        $this->assertEquals('The Dark Knight', $this->service->cleanTitle('The Dark Knight (2008)'));
        $this->assertEquals('Inception', $this->service->cleanTitle('Inception [Steelbook]'));
        $this->assertEquals('Avatar', $this->service->cleanTitle('Avatar Blu-ray UHD'));
    }
}
