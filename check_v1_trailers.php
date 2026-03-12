<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $count = DB::connection('mysql_v1')->table('dvds')->whereNotNull('trailer_url')->count();
    echo "MySQL v1 trailers count: $count\n";
    if ($count > 0) {
        $sample = DB::connection('mysql_v1')->table('dvds')->whereNotNull('trailer_url')->limit(1)->first();
        echo "Sample Trailer URL: " . $sample->trailer_url . "\n";
    }
} catch (\Exception $e) {
    echo "Error connecting to mysql_v1: " . $e->getMessage() . "\n";
}
