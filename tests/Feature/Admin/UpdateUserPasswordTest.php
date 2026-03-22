<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_user_password_interactively()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $this->artisan('app:update-user admin@example.com')
            ->expectsQuestion('Neues Passwort? (Wird nicht angezeigt)', 'new-secret-password')
            ->expectsQuestion('Neues Passwort bestätigen?', 'new-secret-password')
            ->expectsOutput("Das Passwort für Benutzer {$user->name} ({$user->email}) wurde erfolgreich aktualisiert!")
            ->assertExitCode(0);

        $this->assertTrue(Hash::check('new-secret-password', $user->refresh()->password));
    }

    public function test_it_fails_if_passwords_not_match()
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        $this->artisan('app:update-user admin@example.com')
            ->expectsQuestion('Neues Passwort? (Wird nicht angezeigt)', 'pass1')
            ->expectsQuestion('Neues Passwort bestätigen?', 'pass2')
            ->expectsOutput('Die Passwörter stimmen nicht überein!')
            ->assertExitCode(1);
    }

    public function test_it_fails_if_user_not_found()
    {
        $this->artisan('app:update-user non-existent@example.com')
            ->expectsOutput('Kein Benutzer mit der E-Mail-Adresse non-existent@example.com gefunden!')
            ->assertExitCode(1);
    }
}
