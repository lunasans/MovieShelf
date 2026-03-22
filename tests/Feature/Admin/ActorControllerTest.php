<?php

namespace Tests\Feature\Admin;

use App\Models\Actor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_admin_can_view_actors_index()
    {
        Actor::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.actors.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.actors.index');
        $response->assertViewHas('actors');
    }

    public function test_admin_can_search_actors()
    {
        Actor::factory()->create(['first_name' => 'Tom', 'last_name' => 'Hanks']);
        Actor::factory()->create(['first_name' => 'Brad', 'last_name' => 'Pitt']);

        $response = $this->actingAs($this->admin)->get(route('admin.actors.index', ['q' => 'Tom']));

        $response->assertStatus(200);
        $response->assertSee('Tom');
        $response->assertDontSee('Brad');
    }

    public function test_admin_can_view_create_actor_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.actors.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.actors.create');
    }

    public function test_admin_can_store_actor()
    {
        $actorData = [
            'first_name' => 'Morgan',
            'last_name' => 'Freeman',
            'birthday' => '1937-06-01',
            'place_of_birth' => 'Memphis, Tennessee',
            'bio' => 'Legendary actor.',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.actors.store'), $actorData);

        $response->assertRedirect(route('admin.actors.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('actors', [
            'first_name' => 'Morgan',
            'last_name' => 'Freeman',
            'slug' => 'morgan-freeman',
        ]);
    }

    public function test_admin_can_view_edit_actor_page()
    {
        $actor = Actor::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.actors.edit', $actor));

        $response->assertStatus(200);
        $response->assertViewIs('admin.actors.edit');
        $response->assertViewHas('actor');
    }

    public function test_admin_can_update_actor()
    {
        $actor = Actor::factory()->create(['first_name' => 'Old', 'last_name' => 'Name']);
        $updateData = [
            'first_name' => 'New',
            'last_name' => 'Name',
            'bio' => 'Updated biography.',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.actors.update', $actor), $updateData);

        $response->assertRedirect(route('admin.actors.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('actors', [
            'id' => $actor->id,
            'first_name' => 'New',
            'last_name' => 'Name',
            'slug' => 'new-name',
        ]);
    }

    public function test_admin_can_delete_actor()
    {
        $actor = Actor::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.actors.destroy', $actor));

        $response->assertRedirect(route('admin.actors.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('actors', ['id' => $actor->id]);
    }
}
