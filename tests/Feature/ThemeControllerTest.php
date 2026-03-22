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

    public function test_theme_is_saved_to_settings_for_authenticated_user()
    {
        $user = \App\Models\User::factory()->create();
        
        $response = $this->actingAs($user)->postJson(route('theme.save'), ['theme' => 'light']);

        $response->assertStatus(200);
        $this->assertEquals('light', Session::get('theme'));
        $this->assertEquals('light', Setting::get('theme'));
    }

    public function test_theme_save_validation()
    {
        $response = $this->postJson(route('theme.save'), []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['theme']);
    }
}
