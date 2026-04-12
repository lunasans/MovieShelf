<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Movie;
use App\Models\Actor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_are_accessible()
    {
        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('actors.index'))->assertStatus(200);
        $this->get(route('statistics'))->assertStatus(200);
        $this->get(route('impressum'))->assertStatus(200);
        $this->get(route('movies.trailers'))->assertStatus(200);
    }

    public function test_movie_details_route()
    {
        $movie = Movie::factory()->create();
        $this->get(route('movies.show', $movie))->assertStatus(200);
        $this->get(route('movies.details', $movie))->assertStatus(200);
    }

    public function test_actor_details_route()
    {
        $actor = Actor::factory()->create();
        $this->get(route('actors.show', $actor))->assertStatus(200);
        $this->get(route('actors.details', $actor))->assertStatus(200);
    }

    public function test_protected_routes_redirect_to_login()
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->get(route('cadmin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_dashboard_is_accessible_to_authenticated_users()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('cadmin.dashboard'))->assertStatus(200);
    }

    public function test_language_switch_route()
    {
        $response = $this->get(route('lang.switch', ['locale' => 'de']));
        $response->assertSessionHas('locale', 'de');
    }
}
