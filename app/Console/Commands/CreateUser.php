<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    protected $signature = 'app:create-user';

    protected $description = 'Create a new user interactively';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Name des Benutzers?');
        $email = $this->ask('E-Mail-Adresse?');

        if (User::where('email', $email)->exists()) {
            $this->error('Ein Benutzer mit dieser E-Mail existiert bereits!');

            return 1;
        }

        $password = $this->secret('Passwort? (Wird nicht angezeigt)');
        $confirmPassword = $this->secret('Passwort bestätigen?');

        if ($password !== $confirmPassword) {
            $this->error('Passwörter stimmen nicht überein!');

            return 1;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->info("Benutzer {$user->name} ({$user->email}) wurde erfolgreich erstellt!");

        return 0;
    }
}
