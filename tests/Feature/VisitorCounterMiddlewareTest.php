<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Counter;

class VisitorCounterMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_is_counted()
    {
        \Illuminate\Support\Facades\Route::get('/test-count', function() {
            return 'OK';
        })->middleware(\App\Http\Middleware\VisitorCounterMiddleware::class);
        
        $response = $this->get('/test-count');
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('counter', [
            'page' => 'all'
        ]);
        
        $this->assertEquals(1, Counter::where('page', 'all')->first()->visits);
    }

    public function test_bot_is_not_counted()
    {
        $this->withHeaders([
            'User-Agent' => 'Googlebot'
        ])->get('/');

        $this->assertEquals(0, Counter::count());
    }

    public function test_assets_are_not_counted()
    {
        // Define a dummy route to test the asset exclusion
        \Illuminate\Support\Facades\Route::get('/test.png', function() {
            return 'image';
        })->middleware(\App\Http\Middleware\VisitorCounterMiddleware::class);

        $this->get('/test.png');

        $this->assertEquals(0, Counter::count());
    }
}
