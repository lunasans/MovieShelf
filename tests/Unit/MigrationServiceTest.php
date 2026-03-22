<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Actor;
use App\Services\MigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MigrationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        config(['database.connections.mysql_v1' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]]);
        
        config(['database.connections.sqlite_v1' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]]);
    }

    public function test_migrate_fresh_truncates_tables()
    {
        $user = User::factory()->create();
        
        $migrator = new MigrationService();
        // Just migrating empty modules to trigger truncation
        $migrator->migrate(true, ['settings']); 
        
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_migrate_users()
    {
        Schema::connection('mysql_v1')->create('users', function ($table) {
            $table->id();
            $table->string('email');
            $table->string('password');
            $table->boolean('twofa_enabled')->default(false);
            $table->timestamps();
        });

        DB::connection('mysql_v1')->table('users')->insert([
            ['id' => 1, 'email' => 'old@example.com', 'password' => 'secret', 'twofa_enabled' => false, 'created_at' => now(), 'updated_at' => now()]
        ]);

        $migrator = new MigrationService();
        $migrator->migrate(false, ['users']);

        $this->assertDatabaseHas('users', [
            'email' => 'old@example.com',
            'name' => 'old'
        ]);
    }

    public function test_migrate_actors()
    {
        Schema::connection('mysql_v1')->create('actors', function ($table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('birth_year')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        DB::connection('mysql_v1')->table('actors')->insert([
            ['id' => 1, 'first_name' => 'Old', 'last_name' => 'Actor', 'birth_year' => 1970, 'bio' => 'Old bio', 'created_at' => now(), 'updated_at' => now()]
        ]);

        $migrator = new MigrationService();
        $migrator->migrate(false, ['actors']);

        $this->assertDatabaseHas('actors', [
            'first_name' => 'Old',
            'last_name' => 'Actor'
        ]);
    }
}
