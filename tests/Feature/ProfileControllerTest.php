<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_page_displays_qr_code_when_2fa_pending()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create([
            'two_factor_secret' => 'test-secret',
            'two_factor_confirmed_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/profile');
        file_put_contents('q:/cloud.neuhaus.or.at/repos/dvd/versions/v2/tmp/response.html', $response->getContent());
        $response->assertOk();
        $response->assertViewHas('qrCodeSvg');
        $response->assertSee('Confirm 2FA'); // If this is seen, the QR code block is rendered
    }

    public function test_profile_information_validation_errors()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_email_must_be_unique_during_update()
    {
        $user1 = User::factory()->create(['email' => 'other@example.com']);
        $user2 = User::factory()->create();

        $response = $this->actingAs($user2)->patch('/profile', [
            'name' => 'New Name',
            'email' => 'other@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_can_delete_their_account()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/');

        $this->assertGuest();
        $this->assertModelMissing($user);
    }

    public function test_correct_password_must_be_provided_to_delete_account()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/profile')->delete('/profile', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrorsIn('userDeletion', 'password')->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
