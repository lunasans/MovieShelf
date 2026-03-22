<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveRouteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Movie $movie;
    protected Actor $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->movie = Movie::factory()->create();
        $this->actor = Actor::factory()->create();
    }

    public function test_root_redirects_to_dashboard()
    {
        $this->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_all_public_get_routes()
    {
        $routes = [
            'dashboard',
            'actors.index',
            'statistics',
            'movies.trailers',
            'impressum',
        ];

        foreach ($routes as $routeName) {
            $this->get(route($routeName))->assertStatus(200);
        }
    }

    public function test_public_parameter_routes()
    {
        $this->get(route('movies.show', $this->movie))->assertStatus(200);
        $this->get(route('movies.details', $this->movie))->assertStatus(200);
        $this->get(route('movies.boxset', $this->movie))->assertStatus(200);
        $this->get(route('movies.random'))->assertStatus(200);

        $this->get(route('actors.show', $this->actor))->assertStatus(200);
        $this->get(route('actors.details', $this->actor))->assertStatus(200);
    }

    public function test_language_switch()
    {
        $this->get(route('lang.switch', ['locale' => 'en']))->assertSessionHas('locale', 'en');
        $this->get(route('lang.switch', ['locale' => 'de']))->assertSessionHas('locale', 'de');
    }

    public function test_all_admin_get_routes_authenticated()
    {
        $this->actingAs($this->user);

        $adminRoutes = [
            'admin.dashboard',
            'admin.movies.index',
            'admin.movies.create',
            'admin.actors.index',
            'admin.actors.create',
            'admin.settings.index',
            'admin.tmdb.index',
            'admin.tmdb.search',
            'admin.tmdb.update-list',
            'admin.tmdb.unlinked-list',
            'admin.import.index',
            'admin.users.index',
            'admin.users.create',
            'admin.update.index',
            'admin.migration.index',
            'admin.stats.index',
        ];

        foreach ($adminRoutes as $routeName) {
            $this->get(route($routeName))->assertStatus(200);
        }
    }

    public function test_profile_routes_authenticated()
    {
        $this->actingAs($this->user);
        $this->get(route('profile.edit'))->assertStatus(200);
    }

    public function test_two_factor_challenge_route_unauthenticated_redirects()
    {
        // Challenge requires auth
        $this->get(route('two-factor.challenge'))->assertRedirect(route('login'));
    }

    public function test_admin_routes_unauthenticated_redirects()
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
