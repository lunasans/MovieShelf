<?php

namespace Tests\Unit;

use App\Models\Actor;
use App\Models\Counter;
use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_actor_factory_creates_valid_model()
    {
        $actor = Actor::factory()->create();
        $this->assertInstanceOf(Actor::class, $actor);
        $this->assertNotEmpty($actor->full_name);
        $this->assertNotNull($actor->slug);
    }

    public function test_counter_factory_creates_valid_model()
    {
        $counter = Counter::factory()->create();
        $this->assertInstanceOf(Counter::class, $counter);
        $this->assertNotEmpty($counter->page);
        $this->assertIsInt($counter->visits);
    }

    public function test_movie_factory_creates_valid_model_with_all_fields()
    {
        $movie = Movie::factory()->create();
        
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertNotEmpty($movie->title);
        $this->assertNotNull($movie->year);
        $this->assertContains($movie->collection_type, ['Blu-ray', 'DVD', '4K', 'Serie']);
        $this->assertNotEmpty($movie->genre);
        $this->assertIsInt($movie->runtime);
        $this->assertIsInt($movie->rating);
        $this->assertContains($movie->rating_age, [0, 6, 12, 16, 18]);
        $this->assertNotEmpty($movie->overview);
        $this->assertNotNull($movie->user_id);
        $this->assertFalse($movie->is_deleted);
        $this->assertEquals(0, $movie->view_count);
    }

    public function test_movie_factory_deleted_state()
    {
        $movie = Movie::factory()->deleted()->create();
        $this->assertTrue($movie->is_deleted);
    }
}
