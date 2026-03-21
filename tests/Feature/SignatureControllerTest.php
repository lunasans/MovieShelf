<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SignatureControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('signature_enabled', '1');
        Setting::set('signature_cache_time', '0');
        Setting::set('signature_film_count', '3');
        Setting::set('signature_film_source', 'newest');
    }

    public function test_signature_show_disabled()
    {
        Setting::set('signature_enabled', '0');

        $response = $this->get('/signature');
        
        $response->assertStatus(403);
    }

    public function test_signature_type_1_renders()
    {
        $this->createTestMovies();
        $response = $this->get('/signature?type=1');
        
        if (!function_exists('imagecreatetruecolor')) {
            $response->assertStatus(500);
        } else {
            $response->assertStatus(200)->assertHeader('Content-Type', 'image/png');
        }
    }

    public function test_signature_type_2_renders()
    {
        $this->createTestMovies();
        $response = $this->get('/signature?type=2');
        
        if (!function_exists('imagecreatetruecolor')) {
            $response->assertStatus(500);
        } else {
            $response->assertStatus(200)->assertHeader('Content-Type', 'image/png');
        }
    }

    public function test_signature_type_3_renders()
    {
        $this->createTestMovies();
        $response = $this->get('/signature?type=3');
        
        if (!function_exists('imagecreatetruecolor')) {
            $response->assertStatus(500);
        } else {
            $response->assertStatus(200)->assertHeader('Content-Type', 'image/png');
        }
    }

    public function test_cache_is_cleared()
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD Library is not installed.');
        }
        
        Cache::shouldReceive('forget')->once()->with('signature_banner_type_1');
        Cache::shouldReceive('remember')->once()->andReturn(response('image', 200, ['Content-Type' => 'image/png']));

        $response = $this->get('/signature?type=1&clear_cache=1');
        $response->assertStatus(200);
    }

    private function createTestMovies()
    {
        for ($i = 1; $i <= 3; $i++) {
            Movie::forceCreate([
                'id' => $i,
                'title' => "Movie $i",
                'cover_id' => 'test-cover',
                'is_deleted' => false,
                'boxset_parent' => null,
                'year' => 2023,
                'user_id' => \App\Models\User::factory()->create()->id,
            ]);
        }
    }
}
