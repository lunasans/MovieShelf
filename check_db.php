<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$results = DB::table('movies')->whereNotNull('boxset_parent')->limit(10)->get();
if ($results->isEmpty()) {
    echo "No movies found with boxset_parent.\n";
    $count = DB::table('movies')->count();
    echo "Total movies in DB: $count\n";
} else {
    foreach ($results as $res) {
        echo "Movie: {$res->title} (Parent ID: {$res->boxset_parent})\n";
    }
}
