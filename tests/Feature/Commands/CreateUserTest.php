<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_new_user_interactively()
    {
        $this->artisan('app:create-user')
            ->expectsQuestion('Name des Benutzers?', 'Alice Test')
            ->expectsQuestion('E-Mail-Adresse?', 'alice@test.com')
            ->expectsQuestion('Passwort? (Wird nicht angezeigt)', 'password123')
            ->expectsQuestion('Passwort bestätigen?', 'password123')
            ->expectsOutputToContain('erfolgreich erstellt!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'alice@test.com',
            'name' => 'Alice Test'
        ]);

        $user = User::where('email', 'alice@test.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_it_fails_if_email_exists()
    {
        User::factory()->create(['email' => 'bob@test.com']);

        $this->artisan('app:create-user')
            ->expectsQuestion('Name des Benutzers?', 'Bob Duplicate')
            ->expectsQuestion('E-Mail-Adresse?', 'bob@test.com')
            ->expectsOutputToContain('existiert bereits!')
            ->assertExitCode(1);
    }

    public function test_it_fails_if_passwords_do_not_match()
    {
        $this->artisan('app:create-user')
            ->expectsQuestion('Name des Benutzers?', 'Charlie')
            ->expectsQuestion('E-Mail-Adresse?', 'charlie@test.com')
            ->expectsQuestion('Passwort? (Wird nicht angezeigt)', 'password123')
            ->expectsQuestion('Passwort bestätigen?', 'different')
            ->expectsOutputToContain('stimmen nicht überein!')
            ->assertExitCode(1);
    }
}
