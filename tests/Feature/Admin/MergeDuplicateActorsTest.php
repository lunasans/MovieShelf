<?php

namespace Tests\Feature\Admin;

use App\Models\Actor;
use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MergeDuplicateActorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_merges_duplicate_actors_and_moves_relations()
    {
        // 1. Create a survivor (with TMDB ID)
        $survivor = Actor::factory()->create([
            'first_name' => 'Tom',
            'last_name' => 'Hanks',
            'tmdb_id' => 31,
        ]);

        // 2. Create a redundant duplicate (without TMDB ID)
        $redundant = Actor::factory()->create([
            'first_name' => ' Tom ', // Extra space to test trimming
            'last_name' => 'Hanks',
            'tmdb_id' => null,
        ]);

        // 3. Create movies and link them to both
        $movie1 = Movie::factory()->create();
        $movie2 = Movie::factory()->create();
        
        $survivor->movies()->attach($movie1, ['role' => 'Hero']);
        $redundant->movies()->attach($movie2, ['role' => 'Protagonist']);

        // 4. Run the command
        $this->artisan('app:merge-duplicate-actors')
            ->expectsOutput('Searching for duplicate actors...')
            ->expectsOutput('Found 1 names with duplicates.')
            ->expectsOutput('Finished merging actors.')
            ->assertExitCode(0);

        // 5. Assertions
        $this->assertDatabaseHas('actors', ['id' => $survivor->id]);
        $this->assertDatabaseMissing('actors', ['id' => $redundant->id]);
        
        // Check that movie2 is now linked to survivor
        $this->assertTrue($survivor->movies()->where('film_id', $movie2->id)->exists());
        $this->assertEquals('Protagonist', $survivor->movies()->where('film_id', $movie2->id)->first()->pivot->role);
    }

    public function test_it_handles_no_duplicates()
    {
        Actor::factory()->create(['first_name' => 'Unique', 'last_name' => 'Actor']);

        $this->artisan('app:merge-duplicate-actors')
            ->expectsOutput('No duplicates found.')
            ->assertExitCode(0);
    }
}
