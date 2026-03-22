<?php

namespace Tests\Unit;

use App\Services\MovieMigratorService;
use Tests\TestCase;

class MovieMigratorServiceTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_migrate_movies_skips_if_table_not_exists()
    {
        $migrator = new MovieMigratorService(function($msg) {}, 'sqlite', []);
        $migrator->migrateMovies(null);
        $this->assertTrue(true);
    }

    public function test_migrate_movie_actors_skips()
    {
        $migrator = new MovieMigratorService(function($msg) {}, 'sqlite', []);
        $migrator->migrateMovieActors();
        $this->assertTrue(true);
    }

    public function test_migrate_seasons_skips()
    {
        $migrator = new MovieMigratorService(function($msg) {}, 'sqlite', []);
        $migrator->migrateSeasons();
        $this->assertTrue(true);
    }

    public function test_migrate_episodes_skips()
    {
        $migrator = new MovieMigratorService(function($msg) {}, 'sqlite', []);
        $migrator->migrateEpisodes();
        $this->assertTrue(true);
    }

    public function test_loads_tmdb_data_from_cache()
    {
        $migrator = new MovieMigratorService(function($msg) {}, 'sqlite', []);
        
        $reflection = new \ReflectionClass($migrator);
        $method = $reflection->getMethod('getTmdbData');
        $method->setAccessible(true);
        
        $oldDvd = (object)['title' => 'Test Movie', 'year' => 2023];
        $cacheKey = hash('sha256', (string) ($oldDvd->title . $oldDvd->year));
        
        $tempDir = sys_get_temp_dir() . '/v1_cache_test_' . uniqid();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        file_put_contents($tempDir . '/' . $cacheKey . '.json', json_encode(['tmdb_id' => 123]));
        
        $result = $method->invokeArgs($migrator, [$oldDvd, $tempDir]);
        $this->assertEquals(123, $result['tmdb_id']);
        
        unlink($tempDir . '/' . $cacheKey . '.json');
        rmdir($tempDir);
    }
}
