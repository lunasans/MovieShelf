<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieWatchedControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_toggle_movie_watched_status()
    {
        $user = User::factory()->create();
        $movie = Movie::factory()->create();

        // 1. Mark as watched
        $response = $this->actingAs($user)->postJson(route('movies.watched.toggle', $movie));

        $response->assertStatus(200);
        $response->assertJson(['watched' => true, 'count' => 1]);
        $this->assertTrue($user->watchedMovies()->where('movie_id', $movie->id)->exists());

        // 2. Mark as unwatched
        $response = $this->actingAs($user)->postJson(route('movies.watched.toggle', $movie));

        $response->assertStatus(200);
        $response->assertJson(['watched' => false, 'count' => 0]);
        $this->assertFalse($user->watchedMovies()->where('movie_id', $movie->id)->exists());
    }

    public function test_unauthenticated_user_cannot_toggle_watched_status()
    {
        $movie = Movie::factory()->create();

        $response = $this->postJson(route('movies.watched.toggle', $movie));

        $response->assertStatus(401);
    }
}
