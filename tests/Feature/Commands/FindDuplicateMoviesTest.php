<?php

namespace Tests\Feature\Commands;

use App\Models\Actor;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindDuplicateMoviesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_no_duplicates_if_none_exist()
    {
        $this->artisan('app:find-duplicate-movies')
            ->expectsOutputToContain('No duplicate movies found.')
            ->assertExitCode(0);
    }

    public function test_it_finds_and_merges_duplicates()
    {
        $user = User::factory()->create();
        
        Movie::forceCreate([
            'id' => 1,
            'title' => 'Duplicate Movie',
            'year' => 2023,
            'collection_type' => 'Blu-ray',
            'tmdb_id' => null, // Lower priority
            'user_id' => $user->id,
            'is_deleted' => false,
        ]);

        $survivor = Movie::forceCreate([
            'id' => 2,
            'title' => 'Duplicate Movie',
            'year' => 2023,
            'collection_type' => 'Blu-ray',
            'tmdb_id' => 12345, // Higher priority
            'user_id' => $user->id,
            'is_deleted' => false,
        ]);

        $actor = Actor::create(['first_name' => 'Test', 'last_name' => 'Actor']);
        
        // Attach actor and watched status to the duplicate (to be merged)
        $duplicate = Movie::find(1);
        $duplicate->actors()->attach($actor->id, ['role' => 'Lead', 'is_main_role' => true, 'sort_order' => 1]);
        $duplicate->watchedByUsers()->attach($user->id);

        $this->artisan('app:find-duplicate-movies', ['--merge' => true])
            ->expectsOutputToContain('Found 1 titles with multiple entries.')
            ->expectsOutputToContain('Merging into survivor: ID 2')
            ->assertExitCode(0);

        // Assert duplicate was deleted
        $this->assertDatabaseMissing('movies', ['id' => 1]);
        
        // Assert survivor remains
        $this->assertDatabaseHas('movies', ['id' => 2]);

        // Assert relations were merged
        $survivor->load(['actors', 'watchedByUsers']);
        $this->assertTrue($survivor->actors->contains($actor->id));
        $this->assertTrue($survivor->watchedByUsers->contains($user->id));
    }
}
