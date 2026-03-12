<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;

$count = Movie::whereNotNull('trailer_url')->count();
echo "Total movies with trailer_url: $count\n";

if ($count > 0) {
    $movies = Movie::whereNotNull('trailer_url')->limit(5)->get();
    foreach ($movies as $movie) {
        echo "ID: {$movie->id}, Title: {$movie->title}, URL: {$movie->trailer_url}\n";
    }
} else {
    // Check one random movie
    $movie = Movie::first();
    if ($movie) {
        echo "Random Movie: {$movie->title}, Trailer URL: [" . ($movie->trailer_url ?? 'NULL') . "]\n";
    }
}
