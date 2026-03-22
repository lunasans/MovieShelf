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

    public function test_movie_factory_creates_valid_model()
    {
        $movie = Movie::factory()->create();
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertNotEmpty($movie->title);
    }
}
