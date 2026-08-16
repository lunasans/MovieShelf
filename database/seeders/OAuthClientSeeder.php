<?php

namespace Database\Seeders;

use App\Models\OAuthClient;
use Illuminate\Database\Seeder;

class OAuthClientSeeder extends Seeder
{
    public function run(): void
    {
        // Web (filmdb.movieshelf.info)
        OAuthClient::updateOrCreate(
            ['client_id' => 'filmdb'],
            [
                'client_secret' => env('OAUTH_FILMDB_SECRET', 'change_me'),
                'name'          => 'FilmDB',
                'redirect_uri'  => env('OAUTH_FILMDB_REDIRECT', 'https://filmdb.movieshelf.info/auth/callback'),
                'is_active'     => true,
            ]
        );

        // Desktop + Android App (Public Client – kein Secret, PKCE erforderlich)
        OAuthClient::updateOrCreate(
            ['client_id' => 'filmdb-desktop'],
            [
                'client_secret' => null,
                'name'          => 'MovieShelf',
                'redirect_uri'  => 'movieshelf://oauth/callback',
                'is_active'     => true,
                'is_public'     => true,
            ]
        );
    }
}
