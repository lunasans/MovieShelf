<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;

$boxsets = Movie::whereHas('boxsetChildren')->limit(5)->get();
if ($boxsets->isEmpty()) {
    echo "No boxsets found with children.\n";
}
foreach ($boxsets as $box) {
    echo "Boxset: {$box->title}\n";
    echo "  ID: {$box->id}\n";
    echo "  Cover ID: [" . ($box->cover_id ?? 'NULL') . "]\n";
    echo "  Backdrop ID: [" . ($box->backdrop_id ?? 'NULL') . "]\n";
    $children = $box->boxsetChildren;
    echo "  Children Count: " . $children->count() . "\n";
    if ($children->count() > 0) {
        $child = $children->first();
        echo "  First Child: {$child->title} (Cover ID: [" . ($child->cover_id ?? 'NULL') . "])\n";
        echo "  First Child Cover URL: " . $child->cover_url . "\n";
    }
    echo "  Boxset Cover URL Accessor: " . $box->cover_url . "\n";
    echo "-------------------\n";
}
