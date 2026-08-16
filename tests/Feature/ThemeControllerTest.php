<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ThemeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_can_be_saved_to_session()
    {
        $response = $this->postJson(route('theme.save'), ['theme' => 'dark']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'theme' => 'dark']);
        $this->assertEquals('dark', Session::get('theme'));
    }

    /**
     * Das Theme-Setting gilt instanzweit (auch fuer Gaeste) und darf deshalb
     * nur von Admins geschrieben werden. Die Sitzungs-Vorschau bekommt jeder.
     */
    public function test_theme_is_saved_to_settings_for_admin()
    {
        $admin = \App\Models\User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson(route('theme.save'), ['theme' => 'light']);

        $response->assertStatus(200);
        $this->assertEquals('light', Session::get('theme'));
        $this->assertEquals('light', Setting::get('theme'));
    }

    public function test_theme_setting_is_not_writable_by_regular_user()
    {
        Setting::set('theme', 'default', 'ui');
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('theme.save'), ['theme' => 'hacked']);

        $response->assertStatus(200);
        $this->assertEquals('hacked', Session::get('theme'), 'Vorschau in der Sitzung bleibt erlaubt');
        $this->assertEquals('default', Setting::get('theme'), 'Das globale Setting darf sich nicht aendern');
    }

    public function test_theme_save_validation()
    {
        $response = $this->postJson(route('theme.save'), []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['theme']);
    }
}
