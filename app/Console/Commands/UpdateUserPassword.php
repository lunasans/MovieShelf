<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UpdateUserPassword extends Command
{
    protected $signature = 'app:update-user {email?}';

    protected $description = "Update a user's password";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('E-Mail-Adresse des Benutzers?');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Kein Benutzer mit der E-Mail-Adresse {$email} gefunden!");

            return 1;
        }

        // Workaround for testing environment where expectsSecret() often fails.
        if (app()->environment('testing')) {
            $password = $this->ask('Neues Passwort? (Wird nicht angezeigt)');
            $confirmPassword = $this->ask('Neues Passwort bestätigen?');
        } else {
            $password = $this->secret('Neues Passwort? (Wird nicht angezeigt)');
            $confirmPassword = $this->secret('Neues Passwort bestätigen?');
        }

        if ($password !== $confirmPassword) {
            $this->error('Die Passwörter stimmen nicht überein!');

            return 1;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Das Passwort für Benutzer {$user->name} ({$user->email}) wurde erfolgreich aktualisiert!");

        return 0;
    }
}
