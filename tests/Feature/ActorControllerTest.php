<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActorControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        \App\Models\Setting::set('tmdb_api_key', 'fake_key');
    }

    public function test_index_displays_actors_and_filters_by_letter()
    {
        Actor::create(['first_name' => 'Tom', 'last_name' => 'Hanks']);
        Actor::create(['first_name' => 'Brad', 'last_name' => 'Pitt']);
        Actor::create(['first_name' => '7', 'last_name' => 'Days']); // Number start

        $response = $this->actingAs($this->user)->get('/actors');
        $response->assertStatus(200);
        $response->assertViewHas('actors');
        $response->assertSee('Tom Hanks');
        $response->assertSee('Brad Pitt');

        // Test Letter Filter
        $responseLetter = $this->actingAs($this->user)->get('/actors?letter=H');
        $responseLetter->assertStatus(200);
        $responseLetter->assertSee('Tom Hanks');
        $responseLetter->assertDontSee('Brad Pitt');


    }

    public function test_index_searches_by_query()
    {
        Actor::create(['first_name' => 'Leonardo', 'last_name' => 'DiCaprio']);
        Actor::create(['first_name' => 'Morgan', 'last_name' => 'Freeman']);

        $response = $this->actingAs($this->user)->get('/actors?q=Leo');
        $response->assertStatus(200);
        $response->assertSee('Leonardo DiCaprio');
        $response->assertDontSee('Morgan Freeman');
    }

    public function test_show_displays_actor_profile_and_syncs_tmdb()
    {
        Storage::fake('public');
        Http::fake([
            'api.themoviedb.org/3/person/*' => Http::response([
                'id' => 12345,
                'imdb_id' => 'nm12345',
                'biography' => 'A famous actor bio.',
                'birthday' => '1970-01-01',
                'place_of_birth' => 'Hollywood',
                'profile_path' => '/test_profile.jpg'
            ], 200),
            'image.tmdb.org/t/p/w185/test_profile.jpg' => Http::response('fake_image_content', 200)
        ]);

        $actor = Actor::create([
            'tmdb_id' => 12345,
            'first_name' => 'Will',
            'last_name' => 'Smith',
        ]);

        $movie = Movie::forceCreate([
            'id' => 1,
            'title' => 'Test Movie',
            'year' => 2020,
            'user_id' => $this->user->id,
            'is_deleted' => false,
        ]);
        
        $actor->movies()->attach($movie->id, ['role' => 'Lead', 'is_main_role' => true, 'sort_order' => 1]);

        $response = $this->actingAs($this->user)->get(route('actors.show', $actor));
        
        $response->assertStatus(200);
        $response->assertViewHas('actor');
        $response->assertViewHas('movies');
        $response->assertSee('Will Smith');
        $response->assertSee('A famous actor bio.');
        $response->assertSee('Test Movie');

        // Check if DB was updated from TMDB
        $actor->refresh();
        $this->assertEquals('A famous actor bio.', $actor->bio);
        $this->assertEquals('nm12345', $actor->imdb_id);
        $this->assertNotNull($actor->profile_path);
    }

    public function test_details_returns_partial_view()
    {
        $actor = Actor::create([
            'first_name' => 'Matt',
            'last_name' => 'Damon',
            'bio' => 'Cached bio'
        ]);

        $response = $this->actingAs($this->user)->get(route('actors.details', $actor));
        
        $response->assertStatus(200);
        $response->assertViewIs('actors.partials.details');
        $response->assertSee('Matt Damon');
        $response->assertSee('Cached bio');
    }
}
