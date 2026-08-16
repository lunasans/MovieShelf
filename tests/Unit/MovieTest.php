<?php

namespace Tests\Unit;

use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MovieTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_image_url_returns_null_for_empty_id()
    {
        $movie = new Movie();
        $this->assertNull($movie->cover_url);
        $this->assertNull($movie->backdrop_url);
    }

    public function test_resolve_image_url_returns_http_url()
    {
        $movie = new Movie();
        $movie->forceFill(['cover_id' => 'https://example.com/cover.jpg']);
        $this->assertEquals('https://example.com/cover.jpg', $movie->cover_url);
    }

    /**
     * Rohe TMDb-Pfade werden bewusst NICHT gehotlinkt (kein Abfluss der
     * Besucher-IPs an image.tmdb.org). Fehlt das Bild lokal, gibt es einen
     * Platzhalter statt einer Fremd-URL.
     */
    public function test_resolve_image_url_does_not_hotlink_tmdb()
    {
        $movie = new Movie();
        $movie->forceFill(['cover_id' => '/tmdb_image.jpg']);
        $this->assertNull($movie->cover_url);
    }

    public function test_resolve_image_url_returns_storage_url_if_exists()
    {
        Storage::fake('public');
        Storage::disk('public')->put('custom_cover.jpg', 'content');

        $movie = new Movie();
        $movie->forceFill([
            'cover_id' => 'custom_cover.jpg',
        ]);
        
        // Ausgeliefert wird ueber den /media-Proxy, nicht ueber /storage.
        $this->assertEquals('/media/custom_cover.jpg', $movie->cover_url);
    }

    public function test_resolve_image_url_returns_legacy_url_if_exists_in_fallback()
    {
        Storage::fake('public');
        Storage::disk('public')->put('covers/123f.jpg', 'content');

        $movie = new Movie();
        $movie->forceFill([
            'cover_id' => '123',
        ]);
        
        $this->assertEquals('/media/covers/123f.jpg', $movie->cover_url);
    }

    public function test_resolve_image_url_returns_null_if_storage_file_missing()
    {
        Storage::fake('public');
        
        $movie = new Movie();
        $movie->forceFill(['cover_id' => 'missing_cover']);
        $this->assertNull($movie->cover_url);
    }
}
