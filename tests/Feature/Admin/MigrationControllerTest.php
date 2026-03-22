<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use App\Services\MigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\TestCase;

class MigrationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Setting::set('migration_enabled', '1');
        
        // Setup a fake connection for v1
        Config::set('database.connections.mysql_v1', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    public function test_admin_can_view_migration_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.migration.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.migration.index');
        $response->assertViewHas('connectionStatus');
    }

    public function test_migration_is_disabled_by_setting()
    {
        Setting::set('migration_enabled', '0');

        $response = $this->actingAs($this->admin)->get(route('admin.migration.index'));

        $response->assertStatus(404);
    }

    public function test_admin_can_run_migration()
    {
        $this->mock(MigrationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('migrate')
                ->once()
                ->with(false, ['actors'], [], null, \Closure::class)
                ->andReturn(true);
        });

        $response = $this->actingAs($this->admin)->post(route('admin.migration.run'), [
            'modules' => ['actors'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_migration_failure_handling()
    {
        $this->mock(MigrationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('migrate')
                ->once()
                ->andThrow(new \Exception('Migration failed error.'));
        });

        $response = $this->actingAs($this->admin)->post(route('admin.migration.run'), [
            'modules' => ['movies'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Migration fehlgeschlagen: Migration failed error.');
    }
}
