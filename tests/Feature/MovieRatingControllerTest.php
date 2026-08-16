<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\User;
use App\Models\UserRating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieRatingControllerTest extends TestCase
{
    use RefreshDatabase;

    // ACHTUNG: Diese Tests laufen in der aktuellen Testeinrichtung nicht durch.
    // `movies` und `user_ratings` liegen unter database/migrations/tenant,
    // RefreshDatabase migriert aber nur die zentralen Tabellen — jeder Test
    // scheitert mit "no such table: movies". Das betrifft die bestehende Suite
    // genauso (z. B. MovieWatchedControllerTest) und keine CI fuehrt sie aus.
    // Beide Pfade in dieselbe SQLite zu migrieren geht nicht, weil die
    // Tenant-Migrationen eine eigene `users`-Tabelle mitbringen. Die Tests sind
    // nach der vorhandenen Konvention geschrieben und greifen, sobald die
    // Tenant-Testeinrichtung steht.

    public function test_user_can_rate_a_movie()
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $response = $this->actingAs($user)->postJson(route('movies.rate', $movie), ['rating' => 4]);

        $response->assertStatus(200);
        $response->assertJson(['rating' => 4, 'count' => 1]);
        $this->assertDatabaseHas('user_ratings', [
            'user_id' => $user->id, 'movie_id' => $movie->id, 'rating' => 4,
        ]);
    }

    public function test_rating_again_replaces_the_previous_one()
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)->postJson(route('movies.rate', $movie), ['rating' => 2]);
        $this->actingAs($user)->postJson(route('movies.rate', $movie), ['rating' => 5]);

        $this->assertSame(1, UserRating::where('movie_id', $movie->id)->count());
        $this->assertSame(5, UserRating::where('movie_id', $movie->id)->value('rating'));
    }

    /**
     * Bis dahin galt min:1 — eine einmal abgegebene Bewertung liess sich nur
     * noch aendern, nie entfernen. Clients, die das Loeschen anbieten, konnten
     * es deshalb nicht uebertragen.
     */
    public function test_rating_zero_removes_the_rating()
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();
        $this->actingAs($user)->postJson(route('movies.rate', $movie), ['rating' => 3]);

        $response = $this->actingAs($user)->postJson(route('movies.rate', $movie), ['rating' => 0]);

        $response->assertStatus(200);
        $response->assertJson(['rating' => null, 'count' => 0]);
        $this->assertDatabaseMissing('user_ratings', [
            'user_id' => $user->id, 'movie_id' => $movie->id,
        ]);
    }

    public function test_removing_leaves_other_users_ratings_alone()
    {
        $movie  = Movie::factory()->create();
        $ich    = User::factory()->create();
        $andere = User::factory()->create();

        $this->actingAs($ich)->postJson(route('movies.rate', $movie), ['rating' => 3]);
        $this->actingAs($andere)->postJson(route('movies.rate', $movie), ['rating' => 5]);

        $this->actingAs($ich)->postJson(route('movies.rate', $movie), ['rating' => 0]);

        $this->assertDatabaseMissing('user_ratings', ['user_id' => $ich->id, 'movie_id' => $movie->id]);
        $this->assertDatabaseHas('user_ratings', ['user_id' => $andere->id, 'movie_id' => $movie->id, 'rating' => 5]);
    }

    public function test_rating_out_of_range_is_rejected()
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        foreach ([-1, 6] as $ungueltig) {
            $this->actingAs($user)
                ->postJson(route('movies.rate', $movie), ['rating' => $ungueltig])
                ->assertStatus(422);
        }

        $this->assertDatabaseCount('user_ratings', 0);
    }
}
