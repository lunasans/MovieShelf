<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Tests\TestCase;

class TwoFactorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_view_two_factor_management_on_profile()
    {
        $response = $this->actingAs($this->user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Two-Factor Authentication');
    }

    public function test_user_can_enable_two_factor()
    {
        Google2FA::shouldReceive('generateSecretKey')
            ->once()
            ->andReturn('SECRETKEY123');

        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->post(route('two-factor.enable'));

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'two-factor-enabled-step-1');
        
        $this->user->refresh();
        $this->assertEquals('SECRETKEY123', $this->user->two_factor_secret);
        $this->assertNull($this->user->two_factor_confirmed_at);
    }

    public function test_user_can_confirm_two_factor()
    {
        $this->user->update(['two_factor_secret' => 'SECRETKEY123']);

        Google2FA::shouldReceive('verifyKey')
            ->once()
            ->with('SECRETKEY123', '123456')
            ->andReturn(true);

        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->post(route('two-factor.confirm'), [
                'code' => '123456',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'two-factor-confirmed');
        
        $this->user->refresh();
        $this->assertNotNull($this->user->two_factor_confirmed_at);
    }

    public function test_user_can_disable_two_factor()
    {
        $this->user->update([
            'two_factor_secret' => 'SECRETKEY123',
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['two_factor_verified' => true])
            ->from(route('profile.edit'))
            ->delete(route('two-factor.disable'));

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'two-factor-disabled');
        
        $this->user->refresh();
        $this->assertNull($this->user->two_factor_secret);
        $this->assertNull($this->user->two_factor_confirmed_at);
    }

    public function test_user_can_verify_two_factor_challenge()
    {
        $this->user->update(['two_factor_secret' => 'SECRETKEY123']);

        Google2FA::shouldReceive('verifyKey')
            ->once()
            ->andReturn(true);

        $response = $this->actingAs($this->user)->post(route('two-factor.verify'), [
            'code' => '123456',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(session('two_factor_verified'));
    }
}
