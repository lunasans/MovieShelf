<?php

namespace Tests\Feature\Admin;

use App\Models\Actor;
use App\Models\Movie;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class MovieControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_admin_can_view_movies_index()
    {
        Movie::factory()->count(5)->create(['user_id' => $this->admin->id, 'is_deleted' => false]);

        $response = $this->actingAs($this->admin)->get(route('admin.movies.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.movies.index');
        $response->assertViewHas('movies');
    }

    public function test_admin_can_filter_movies_missing_tmdb()
    {
        Movie::factory()->create(['user_id' => $this->admin->id, 'tmdb_id' => null, 'title' => 'No TMDB']);
        Movie::factory()->create(['user_id' => $this->admin->id, 'tmdb_id' => 123, 'title' => 'With TMDB']);

        $response = $this->actingAs($this->admin)->get(route('admin.movies.index', ['filter' => 'missing_tmdb']));

        $response->assertSee('No TMDB');
        $response->assertDontSee('With TMDB');
    }

    public function test_admin_can_update_movie_and_sync_actors()
    {
        Storage::fake('public');
        Http::fake([
            'https://image.tmdb.org/t/p/*' => Http::response('fake-image-content', 200),
        ]);

        $movie = Movie::factory()->create(['user_id' => $this->admin->id, 'tmdb_id' => null]);
        
        $this->mock(TmdbService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getMovieDetails')->andReturn([
                'credits' => [
                    'cast' => [
                        ['id' => 1, 'name' => 'Actor One', 'character' => 'Role One', 'order' => 0, 'profile_path' => '/path1.jpg'],
                        ['id' => 2, 'name' => 'Actor Two', 'character' => 'Role Two', 'order' => 1, 'profile_path' => '/path2.jpg'],
                    ]
                ]
            ]);
        });

        $updateData = [
            'title' => 'Updated Movie',
            'year' => 2024,
            'collection_type' => 'Film',
            'tmdb_id' => 999,
            'cover_id' => '/new_cover.jpg',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.movies.update', $movie), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('movies', ['id' => $movie->id, 'title' => 'Updated Movie', 'tmdb_id' => 999]);
        
        $movie->refresh();
        $this->assertCount(2, $movie->actors);
        $this->assertDatabaseHas('actors', ['first_name' => 'Actor', 'last_name' => 'One']);
        
        Storage::disk('public')->assertExists('covers/tmdb_new_cover.jpg');
    }

    public function test_admin_can_delete_movie()
    {
        $movie = Movie::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.movies.destroy', $movie));

        $response->assertRedirect();
        $this->assertDatabaseMissing('movies', ['id' => $movie->id]);
        
        $this->assertDatabaseHas('activity_log', [
            'action' => 'MOVIE_DELETE',
            'user_id' => $this->admin->id,
        ]);
    }
}
