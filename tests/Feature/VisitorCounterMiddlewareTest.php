<?php

namespace Tests\Feature;

use App\Models\Counter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VisitorCounterMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // counter und visitor_hits sind Tabellen der Regal-Datenbank. Die
        // Testumgebung migriert nur database/migrations, deshalb hier
        // ausdruecklich nachziehen - sonst laeuft der Test gegen ein Schema
        // ohne die Tabellen, die er prueft.
        // Nicht das ganze Verzeichnis: es enthaelt auch users und andere
        // Tabellen, die die Testumgebung bereits angelegt hat.
        $this->artisan('migrate', ['--path' => [
            'database/migrations/tenant/2026_03_10_165206_create_counter_table.php',
            'database/migrations/tenant/2026_08_10_120000_create_visitor_hits_table.php',
            'database/migrations/tenant/2026_08_10_120100_change_counter_last_visit_to_datetime.php',
        ]]);

        Route::get('/test-count', fn () => response('<html><body>OK</body></html>'))
            ->middleware(\App\Http\Middleware\VisitorCounterMiddleware::class);

        Route::get('/test-json', fn () => response()->json(['ok' => true]))
            ->middleware(\App\Http\Middleware\VisitorCounterMiddleware::class);

        Route::get('/test-redirect', fn () => redirect('/test-count'))
            ->middleware(\App\Http\Middleware\VisitorCounterMiddleware::class);
    }

    /** Ein Seitenaufruf im Browser wird gezaehlt. */
    public function test_page_visit_is_counted(): void
    {
        $this->browserRequest('/test-count')->assertOk();

        $this->assertSame(1, (int) Counter::where('page', 'all')->value('visits'));
        $this->assertSame(1, (int) Counter::where('page', 'daily:'.now()->toDateString())->value('visits'));
    }

    /**
     * Kern des Fehlers: Aus 6640 Aufrufen derselben Seite wurden 3720
     * Besucher, weil die Eindeutigkeitspruefung still ausfiel.
     */
    public function test_same_ip_is_counted_once_per_day(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->browserRequest('/test-count')->assertOk();
        }

        $this->assertSame(1, (int) Counter::where('page', 'all')->value('visits'));
    }

    public function test_different_ips_are_counted_separately(): void
    {
        $this->browserRequest('/test-count', '10.0.0.1')->assertOk();
        $this->browserRequest('/test-count', '10.0.0.2')->assertOk();

        $this->assertSame(2, (int) Counter::where('page', 'all')->value('visits'));
    }

    /** Hintergrundabfragen per fetch() melden Sec-Fetch-Dest: empty. */
    public function test_background_fetch_is_not_counted(): void
    {
        $this->withHeaders([
            'User-Agent'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Sec-Fetch-Dest' => 'empty',
            'Sec-Fetch-Mode' => 'cors',
        ])->get('/test-count');

        $this->assertSame(0, Counter::count());
    }

    /** JSON-Antworten sind keine Seitenbesuche, auch bei Navigation nicht. */
    public function test_json_response_is_not_counted(): void
    {
        $this->browserRequest('/test-json');

        $this->assertSame(0, Counter::count());
    }

    /** Weiterleitungen - etwa auf /login - sind kein Seitenbesuch. */
    public function test_redirect_is_not_counted(): void
    {
        $this->browserRequest('/test-redirect');

        $this->assertSame(0, Counter::count());
    }

    /**
     * Scanner senden keine Sec-Fetch-Header, auch wenn sie sich als Browser
     * ausgeben. Genau diese Aufrufe haben den Zaehler aufgeblaeht.
     */
    public function test_request_without_browser_signature_is_not_counted(): void
    {
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; irgendein Scanner)'])
            ->get('/test-count');

        $this->assertSame(0, Counter::count());
    }

    public function test_bot_is_not_counted(): void
    {
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1'])->get('/test-count');

        $this->assertSame(0, Counter::count());
    }

    public function test_post_is_not_counted(): void
    {
        Route::post('/test-post', fn () => response('<html>OK</html>'))
            ->middleware(\App\Http\Middleware\VisitorCounterMiddleware::class);

        $this->withHeaders([
            'User-Agent'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Sec-Fetch-Dest' => 'document',
        ])->post('/test-post');

        $this->assertSame(0, Counter::count());
    }

    /** Ein Seitenaufruf, der eine Uhrzeit hinterlaesst - nicht nur ein Datum. */
    public function test_last_visit_keeps_the_time(): void
    {
        $this->browserRequest('/test-count')->assertOk();

        $lastVisit = Counter::where('page', 'all')->value('last_visit');

        $this->assertNotNull($lastVisit);
        $this->assertStringContainsString(now()->format('H:i'), (string) $lastVisit);
    }

    /** Ein Aufruf, wie ihn ein Browser bei einer Seitennavigation sendet. */
    protected function browserRequest(string $uri, string $ip = '203.0.113.10')
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->withHeaders([
                'User-Agent'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
            ])->get($uri);
    }
}
