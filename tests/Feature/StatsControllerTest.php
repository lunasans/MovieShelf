<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_statistics()
    {
        $user = User::factory()->create();
        
        Movie::forceCreate([
            'id' => 1,
            'title' => 'Stats Movie',
            'year' => 2000,
            'runtime' => 120,
            'collection_type' => 'DVD',
            'rating_age' => 12,
            'genre' => 'Action, Sci-Fi',
            'user_id' => $user->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($user)->get(route('statistics'));
        
        $response->assertStatus(200);
        $response->assertViewHas('totalFilms', 1);
        $response->assertViewHas('totalRuntime', 120);
        $response->assertViewHas('yearStats');
        $response->assertViewHas('collections');
        $response->assertViewHas('ratings');
        $response->assertViewHas('genres');
        $response->assertSee('Action');
        $response->assertSee('Sci-Fi');
    }

    public function test_index_displays_statistics_via_ajax()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('statistics'), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        
        $response->assertStatus(200);
        $response->assertViewIs('movies.partials.stats');
    }
}
