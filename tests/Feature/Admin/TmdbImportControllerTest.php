<?php

namespace Tests\Feature\Admin;

use App\Models\Movie;
use App\Models\User;
use App\Services\TmdbImportService;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class TmdbImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_index_displays_view()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.tmdb.index'));
        $response->assertStatus(200);
    }

    public function test_search_returns_results()
    {
        $this->mock(TmdbService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchMovie')->andReturn(['results' => [['title' => 'Avatar']]]);
        });

        $response = $this->actingAs($this->admin)->get(route('admin.tmdb.search', ['query' => 'Avatar', 'type' => 'movie']));
        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Avatar']);
    }

    public function test_auto_link_successfully_finds_and_saves_match()
    {
        $movie = Movie::forceCreate([
            'id' => 10,
            'title' => 'The Matrix',
            'year' => 1999,
            'user_id' => $this->admin->id,
            'is_deleted' => false
        ]);

        $this->mock(TmdbImportService::class, function (MockInterface $mock) {
            $mock->shouldReceive('cleanTitle')->andReturn('The Matrix');
        });

        $this->mock(TmdbService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchMovie')->andReturn([
                'results' => [
                    ['id' => 603, 'title' => 'The Matrix', 'release_date' => '1999-03-30']
                ]
            ]);
        });

        $response = $this->actingAs($this->admin)->post(route('admin.tmdb.auto-link'), ['movie_id' => $movie->id]);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $movie->refresh();
        $this->assertEquals(603, $movie->tmdb_id);
    }

    public function test_bulk_update_calls_import_service()
    {
        $movie = Movie::forceCreate([
            'id' => 11,
            'title' => 'To Update',
            'user_id' => $this->admin->id,
            'is_deleted' => false
        ]);

        $this->mock(TmdbImportService::class, function (MockInterface $mock) use ($movie) {
            $mock->shouldReceive('bulkUpdate')->once()->withArgs(function($arg) use ($movie) {
                return $arg->id === $movie->id;
            });
        });

        $response = $this->actingAs($this->admin)->post(route('admin.tmdb.bulk-update'), ['movie_id' => $movie->id]);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
