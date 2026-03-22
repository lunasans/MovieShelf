<?php

namespace Tests\Feature\Services;

use App\Models\Actor;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Season;
use App\Models\User;
use App\Services\MovieMigratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MovieMigratorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $v1Conn = 'sqlite_v1';
    protected $v1CacheDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup V1 SQLite in-memory connection
        Config::set("database.connections.{$this->v1Conn}", [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $this->setupV1Schema();
        
        $this->v1CacheDir = sys_get_temp_dir() . '/v1_cache_' . uniqid();
        mkdir($this->v1CacheDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->v1CacheDir)) {
            array_map('unlink', glob("{$this->v1CacheDir}/*"));
            rmdir($this->v1CacheDir);
        }
        parent::tearDown();
    }

    protected function setupV1Schema()
    {
        $schema = Schema::connection($this->v1Conn);

        $schema->create('dvds', function ($table) {
            $table->integer('id')->primary();
            $table->string('title');
            $table->integer('user_id');
            $table->integer('year')->nullable();
            $table->string('genre')->nullable();
            $table->float('rating')->nullable();
            $table->string('cover_id')->nullable();
            $table->string('collection_type')->nullable();
            $table->integer('runtime')->nullable();
            $table->integer('rating_age')->nullable();
            $table->text('overview')->nullable();
            $table->string('trailer_url')->nullable();
            $table->integer('boxset_parent')->nullable();
            $table->integer('view_count')->default(0);
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });

        $schema->create('film_actor', function ($table) {
            $table->integer('film_id');
            $table->integer('actor_id');
            $table->string('role')->nullable();
            $table->boolean('is_main_role')->default(false);
            $table->integer('sort_order')->default(0);
        });

        $schema->create('seasons', function ($table) {
            $table->integer('id')->primary();
            $table->integer('series_id');
            $table->integer('season_number');
            $table->string('name')->nullable();
            $table->text('overview')->nullable();
            $table->timestamps();
        });

        $schema->create('episodes', function ($table) {
            $table->integer('id')->primary();
            $table->integer('season_id');
            $table->integer('episode_number');
            $table->string('title')->nullable();
            $table->text('overview')->nullable();
            $table->timestamps();
        });

        $schema->create('activity_log', function ($table) {
            $table->id();
            $table->string('action');
            $table->text('details');
        });
    }

    public function test_migrate_movies_success()
    {
        $user = User::factory()->create(['id' => 1]);

        // Seed V1
        DB::connection($this->v1Conn)->table('dvds')->insert([
            'id' => 10,
            'title' => 'V1 Movie',
            'user_id' => $user->id,
            'year' => 2020,
            'genre' => 'Action',
            'collection_type' => 'Blu-ray',
            'cover_id' => 'tmdb_123',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add TMDB Mapping in log
        DB::connection($this->v1Conn)->table('activity_log')->insert([
            'action' => 'FILM_UPDATE_TMDB',
            'details' => json_encode(['film_id' => 10, 'tmdb_id' => 123]),
        ]);

        $logs = [];
        $callback = function ($msg) use (&$logs) { $logs[] = $msg; };
        $service = new MovieMigratorService($callback, $this->v1Conn, []);

        $service->migrateMovies($this->v1CacheDir);

        $this->assertDatabaseHas('movies', [
            'id' => 10,
            'title' => 'V1 Movie',
            'tmdb_id' => 123,
            'collection_type' => 'Blu-ray',
        ]);
    }

    public function test_migrate_movie_actors()
    {
        // Seed V2 Actors first (since they are referenced)
        $actor = Actor::factory()->create(['id' => 5]);
        $movie = Movie::factory()->create(['id' => 10]);

        // Seed V1 Link
        DB::connection($this->v1Conn)->table('film_actor')->insert([
            'film_id' => 10,
            'actor_id' => 5,
            'role' => 'Protagonist',
            'is_main_role' => true,
            'sort_order' => 1,
        ]);

        $service = new MovieMigratorService(null, $this->v1Conn, []);
        $service->migrateMovieActors();

        $this->assertDatabaseHas('film_actor', [
            'film_id' => 10,
            'actor_id' => 5,
            'role' => 'Protagonist',
        ]);
    }

    public function test_migrate_seasons_and_episodes()
    {
        $movie = Movie::factory()->create(['id' => 10]);

        // Seed V1
        DB::connection($this->v1Conn)->table('seasons')->insert([
            'id' => 1,
            'series_id' => 10,
            'season_number' => 1,
            'name' => 'Season 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection($this->v1Conn)->table('episodes')->insert([
            'id' => 1,
            'season_id' => 1,
            'episode_number' => 1,
            'title' => 'Ep 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new MovieMigratorService(null, $this->v1Conn, []);
        $service->migrateSeasons();
        $service->migrateEpisodes();

        $this->assertDatabaseHas('seasons', ['id' => 1, 'movie_id' => 10]);
        $this->assertDatabaseHas('episodes', ['id' => 1, 'season_id' => 1]);
    }

    public function test_prepare_movie_data_with_tmdb_cache()
    {
        $oldDvd = (object)[
            'id' => 1,
            'title' => 'Cached Movie',
            'user_id' => 1,
            'year' => 2010,
            'genre' => 'Old Genre',
            'rating' => 0, // Should be overwritten by cache
            'cover_id' => 'cover.jpg',
            'collection_type' => 'DVD',
            'runtime' => 90,
            'rating_age' => 12,
            'overview' => 'Old Overview',
            'trailer_url' => null,
            'boxset_parent' => null,
            'view_count' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $tmdbData = [
            'vote_average' => 8.8,
            'overview' => 'New Cached Overview',
        ];

        $service = new MovieMigratorService(null, $this->v1Conn, ['rating', 'overview']);
        
        // Use Reflection to test protected method
        $reflection = new \ReflectionClass(MovieMigratorService::class);
        $method = $reflection->getMethod('prepareMovieData');
        $method->setAccessible(true);
        
        $result = $method->invoke($service, $oldDvd, 123, $tmdbData);

        $this->assertEquals(8.8, $result['rating']);
        $this->assertEquals('Old Overview', $result['overview']);
        $this->assertEquals('Cached Movie', $result['title']);
        $this->assertArrayNotHasKey('genre', $result);
        $this->assertArrayNotHasKey('year', $result);
    }
}
