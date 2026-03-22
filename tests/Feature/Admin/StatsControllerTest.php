<?php

namespace Tests\Feature\Admin;

use App\Models\Counter;
use App\Models\User;
use App\Http\Middleware\VisitorCounterMiddleware;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2024, 1, 1, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_view_stats_index()
    {
        $today = Carbon::now()->format('Y-m-d');
        Counter::create(['page' => "daily:$today", 'visits' => 50]);
        Counter::create(['page' => 'all', 'visits' => 1000]);

        // Disable VisitorCounterMiddleware to avoid accidental increments
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VisitorCounterMiddleware::class)
            ->get(route('admin.stats.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.stats.index');
        
        $this->assertEquals(50, $response->viewData('todayCount'));
        $this->assertEquals(1000, $response->viewData('allTimeTotal'));
    }

    public function test_stats_calculations()
    {
        // Mocking some daily visits
        // $i=0 is today (2024-01-01)
        // $i=1 is yesterday (2023-12-31)
        for ($i = 0; $i < 10; $i++) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            Counter::create(['page' => "daily:$date", 'visits' => 10 + $i]);
        }

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VisitorCounterMiddleware::class)
            ->get(route('admin.stats.index'));

        $response->assertStatus(200);
        
        // todayCount corresponds to $i=0 -> 10 visits
        $this->assertEquals(10, $response->viewData('todayCount'));
        
        // yesterdayCount corresponds to $i=1 -> 11 visits
        $this->assertEquals(11, $response->viewData('yesterdayCount'));
        
        // peak should be 19 (for $i=9)
        $this->assertEquals(19, $response->viewData('peak'));
    }
}
