<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Admin User
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@movieshelf.com',
            'is_admin' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('movieshelf'),
        ]);

        // OAuth-Clients für Desktop-/Android-App (PKCE)
        $this->call(OAuthClientSeeder::class);
    }
}
