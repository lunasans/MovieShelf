<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Admin\SettingController;
use App\Services\TmdbService;

class SettingControllerTest extends TestCase
{
    /**
     * A basic unit test for setting group logic.
     */
    public function test_it_returns_correct_group_for_keys(): void
    {
        // Wir brauchen eine Instanz des Controllers. 
        // Da der Konstruktor einen TmdbService erwartet, "mocken" wir diesen (wir tun so als ob).
        $tmdbService = $this->createMock(TmdbService::class);
        $controller = new SettingController($tmdbService);

        // Der eigentliche Test: Wir prüfen verschiedene Schlüssel
        $this->assertEquals('tmdb', $controller->getSettingGroup('tmdb_api_key'));
        $this->assertEquals('ui', $controller->getSettingGroup('theme'));
        $this->assertEquals('impressum', $controller->getSettingGroup('impressum_name'));
        $this->assertEquals('signature', $controller->getSettingGroup('signature_enabled'));
        $this->assertEquals('mail', $controller->getSettingGroup('mail_host'));
        $this->assertEquals('general', $controller->getSettingGroup('site_title'));
    }
}
