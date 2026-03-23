<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_shows_dashboard_and_movies()
    {
        Movie::forceCreate([
            'id' => 1,
            'title' => 'Inception',
            'year' => 2010,
            'collection_type' => 'Blu-ray',
            'user_id' => $this->user->id,
            'is_deleted' => false,
        ]);
        
        Movie::forceCreate([
            'id' => 2,
            'title' => 'Avatar',
            'year' => 2009,
            'collection_type' => 'DVD',
            'user_id' => $this->user->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewHas('movies');
        $response->assertSee('Inception');
        $response->assertSee('Avatar');

        // Test search
        $responseSearch = $this->actingAs($this->user)->get(route('dashboard', ['q' => 'Inception']));
        $responseSearch->assertViewHas('movies', function ($collection) {
            return $collection->contains('title', 'Inception') && !$collection->contains('title', 'Avatar');
        });

        // Test type filter
        $responseType = $this->actingAs($this->user)->get(route('dashboard', ['type' => 'DVD']));
        $responseType->assertViewHas('movies', function ($collection) {
            return $collection->contains('title', 'Avatar') && !$collection->contains('title', 'Inception');
        });
    }

    public function test_index_ajax_request_returns_partial()
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $response->assertStatus(200);
        // It returns a rendered string, so we just check it is OK.
    }

    public function test_show_displays_single_movie()
    {
        $movie = Movie::forceCreate([
            'id' => 1,
            'title' => 'The Matrix',
            'year' => 1999,
            'user_id' => $this->user->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($this->user)->get(route('movies.show', $movie));
        
        $response->assertStatus(200);
        $response->assertViewHas('movie');
        $response->assertSee('The Matrix');
    }

    public function test_details_returns_partial_with_similar_movies()
    {
        $movie1 = Movie::forceCreate(['id' => 1, 'title' => 'SciFi 1', 'genre' => 'Sci-Fi', 'user_id' => $this->user->id, 'is_deleted' => false]);
        $movie2 = Movie::forceCreate(['id' => 2, 'title' => 'SciFi 2', 'genre' => 'Sci-Fi', 'user_id' => $this->user->id, 'is_deleted' => false]);

        $response = $this->actingAs($this->user)->get(route('movies.details', $movie1));
        
        $response->assertStatus(200);
        $response->assertViewIs('movies.partials.details');
        $response->assertSee('SciFi 1');
    }

    public function test_random_returns_random_movie_json()
    {
        $movie = Movie::forceCreate([
            'id' => 10,
            'title' => 'Interstellar',
            'backdrop_id' => '/backdrop.jpg',
            'user_id' => $this->user->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($this->user)->get(route('movies.random'));
        
        $response->assertStatus(200);
        $response->assertJson([
            'id' => 10,
            'backdrop_url' => 'https://image.tmdb.org/t/p/w1280/backdrop.jpg'
        ]);
    }

    public function test_random_returns_404_if_no_movies()
    {
        $response = $this->actingAs($this->user)->get(route('movies.random'));
        $response->assertStatus(404);
    }

    public function test_boxset_returns_children_sorted_by_year()
    {
        $parent = Movie::forceCreate(['id' => 1, 'title' => 'Trilogy', 'user_id' => $this->user->id, 'is_deleted' => false]);
        
        Movie::forceCreate(['id' => 2, 'title' => 'Part 2', 'year' => 2010, 'boxset_parent' => 1, 'user_id' => $this->user->id, 'is_deleted' => false]);
        Movie::forceCreate(['id' => 3, 'title' => 'Part 1', 'year' => 2005, 'boxset_parent' => 1, 'user_id' => $this->user->id, 'is_deleted' => false]);
        Movie::forceCreate(['id' => 4, 'title' => 'Part 3', 'year' => 2015, 'boxset_parent' => 1, 'user_id' => $this->user->id, 'is_deleted' => false]);

        $response = $this->actingAs($this->user)->get(route('movies.boxset', $parent));
        
        $response->assertStatus(200);
        $data = $response->json('children');
        
        $this->assertCount(3, $data);
        $this->assertEquals('Part 1', $data[0]['title']);
        $this->assertEquals('Part 2', $data[1]['title']);
        $this->assertEquals('Part 3', $data[2]['title']);
    }
}
