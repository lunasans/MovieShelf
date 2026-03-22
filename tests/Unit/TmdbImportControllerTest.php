<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\TmdbImportController;
use App\Services\TmdbService;
use App\Services\TmdbImportService;
use Tests\TestCase;

class TmdbImportControllerTest extends TestCase
{
    /**
     * Test the title cleaning logic.
     */
    public function test_it_cleans_movie_titles_correctly(): void
    {
        $tmdbService = $this->createMock(TmdbService::class);
        $service = new TmdbImportService($tmdbService);

        $this->assertEquals('Iron Man', $service->cleanTitle('Iron Man (DVD)'));
        $this->assertEquals('The Matrix', $service->cleanTitle('The Matrix [4K UHD]'));
        $this->assertEquals('Inception', $service->cleanTitle('Inception Blu-ray'));
        $this->assertEquals('Avatar', $service->cleanTitle('Avatar (2009) [Extended Edition]'));
        $this->assertEquals('Blade Runner 2049', $service->cleanTitle('Blade Runner 2049 (Steelbook)'));
    }
}
