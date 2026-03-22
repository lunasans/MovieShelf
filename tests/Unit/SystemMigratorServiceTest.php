<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Models\Counter;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\SystemMigratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SystemMigratorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        config(['database.connections.v1_test' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]]);
    }

    public function test_migrate_settings()
    {
        Schema::connection('v1_test')->create('settings', function ($table) {
            $table->string('key');
            $table->string('value')->nullable();
            $table->string('group')->nullable();
        });

        DB::connection('v1_test')->table('settings')->insert([
            ['key' => 'site_name', 'value' => 'My Old Site', 'group' => 'general']
        ]);

        $migrator = new SystemMigratorService(null, 'v1_test');
        $migrator->migrateSettings();

        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'value' => 'My Old Site'
        ]);
    }

    public function test_migrate_counter()
    {
        Schema::connection('v1_test')->create('counter', function ($table) {
            $table->integer('visits');
            $table->integer('daily_visits');
            $table->date('last_visit_date');
            $table->timestamps();
        });

        DB::connection('v1_test')->table('counter')->insert([
            [
                'visits' => 1500,
                'daily_visits' => 45,
                'last_visit_date' => '2023-10-01',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        $migrator = new SystemMigratorService(null, 'v1_test');
        $migrator->migrateCounter();

        $this->assertDatabaseHas('counter', [
            'page' => 'all',
            'visits' => 1500
        ]);

        $this->assertDatabaseHas('counter', [
            'page' => 'daily:2023-10-01',
            'visits' => 45
        ]);
    }

    public function test_migrate_logs()
    {
        Schema::connection('v1_test')->create('activity_log', function ($table) {
            $table->id();
            $table->integer('user_id');
            $table->string('action');
            $table->text('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        DB::connection('v1_test')->table('activity_log')->insert([
            ['id' => 1, 'user_id' => 1, 'action' => 'login', 'details' => 'Success', 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla', 'created_at' => now(), 'updated_at' => now()]
        ]);
        
        Schema::connection('v1_test')->create('audit_log', function ($table) {
            $table->id();
            $table->integer('user_id');
            $table->string('action');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        DB::connection('v1_test')->table('audit_log')->insert([
            ['id' => 1, 'user_id' => 1, 'action' => 'update_movie', 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla', 'created_at' => now(), 'updated_at' => now()]
        ]);
        User::factory()->create(['id' => 1]);
        \Illuminate\Database\Eloquent\Model::unguard();
        
        $migrator = new SystemMigratorService(null, 'v1_test');
        $migrator->migrateLogs();

        \Illuminate\Database\Eloquent\Model::reguard();
        
        $this->assertDatabaseHas('activity_log', ['action' => 'login']);
    }
}
