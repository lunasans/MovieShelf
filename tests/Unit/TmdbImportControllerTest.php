<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Admin\TmdbImportController;
use App\Services\TmdbService;

class TmdbImportControllerTest extends TestCase
{
    /**
     * Test the title cleaning logic.
     */
    public function test_it_cleans_movie_titles_correctly(): void
    {
        $tmdbService = $this->createMock(TmdbService::class);
        $controller = new TmdbImportController($tmdbService);

        $this->assertEquals('Iron Man', $controller->cleanTitle('Iron Man (DVD)'));
        $this->assertEquals('The Matrix', $controller->cleanTitle('The Matrix [4K UHD]'));
        $this->assertEquals('Inception', $controller->cleanTitle('Inception Blu-ray'));
        $this->assertEquals('Avatar', $controller->cleanTitle('Avatar (2009) [Extended Edition]'));
        $this->assertEquals('Blade Runner 2049', $controller->cleanTitle('Blade Runner 2049 (Steelbook)'));
    }
}
