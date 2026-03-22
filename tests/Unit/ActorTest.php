<?php

namespace Tests\Unit;

use App\Models\Actor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActorTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_profile_url_attribute_returns_null_when_empty()
    {
        $actor = new Actor();
        $this->assertNull($actor->profile_url);
    }

    public function test_get_profile_url_attribute_returns_http_url()
    {
        $actor = new Actor(['profile_path' => 'https://example.com/image.jpg']);
        $this->assertEquals('https://example.com/image.jpg', $actor->profile_url);
    }

    public function test_get_profile_url_attribute_returns_tmdb_url_with_leading_slash()
    {
        $actor = new Actor(['profile_path' => '/some_image.jpg']);
        $this->assertEquals('https://image.tmdb.org/t/p/w185/some_image.jpg', $actor->profile_url);
    }

    public function test_get_profile_url_attribute_returns_storage_url_with_extension()
    {
        Storage::fake('public');
        Storage::disk('public')->put('custom/actor.jpg', 'content');
        
        $actor = new Actor(['profile_path' => 'custom/actor.jpg']);
        $this->assertEquals(Storage::disk('public')->url('custom/actor.jpg'), $actor->profile_url);
    }

    public function test_get_profile_url_attribute_returns_storage_url_with_actors_prefix()
    {
        Storage::fake('public');
        Storage::disk('public')->put('actors/actor_123', 'content');
        
        $actor = new Actor(['profile_path' => 'actor_123']);
        $this->assertEquals(Storage::disk('public')->url('actors/actor_123'), $actor->profile_url);
    }

    public function test_get_profile_url_attribute_returns_null_if_storage_file_missing()
    {
        Storage::fake('public');
        
        $actor = new Actor(['profile_path' => 'missing.jpg']);
        $this->assertNull($actor->profile_url);
    }
}
