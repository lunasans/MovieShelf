<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;

$movie = Movie::where('title', '2001 Maniacs')->first();
if ($movie) {
    echo "ID: " . $movie->id . "\n";
    echo "Title: " . $movie->title . "\n";
    echo "Rating: " . ($movie->rating ?? 'NULL') . "\n";
    echo "Raw Rating from DB: " . \DB::table('movies')->where('id', $movie->id)->value('rating') . "\n";
} else {
    echo "Movie not found\n";
}
