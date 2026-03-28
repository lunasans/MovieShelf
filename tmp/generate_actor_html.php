<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Actor;

$actor = Actor::where('last_name', 'Keaton')->first();
if (!$actor) {
    echo "Actor not found.\n";
    exit;
}

echo "Generating HTML for actor ID: " . $actor->id . " (" . $actor->full_name . ")\n";

$html = view('actors.show', [
    'actor' => $actor,
    'movies' => $actor->movies()->orderBy('year', 'desc')->get(),
    'stats' => [
        'total_movies' => $actor->movies()->count(),
        'main_roles' => 0,
        'year_span' => 'N/A',
        'top_genres' => []
    ],
    'jsonLd' => []
])->render();

file_put_contents(__DIR__.'/actor_profile.html', $html);
echo "HTML saved to tmp/actor_profile.html\n";
