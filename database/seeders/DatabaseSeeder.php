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
        // Only seed the central database with a default admin
        // Tenants will have their own admins created during registration
        if (function_exists('tenancy') && tenancy()->initialized) {
            return;
        }

        // Default Admin User (Central only)
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@movieshelf.com',
            'password' => \Illuminate\Support\Facades\Hash::make('movieshelf'),
        ]);
    }
}
