<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Starting repair...\n";

$tenants = Tenant::all();
foreach ($tenants as $t) {
    $domain = $t->id . '.localhost';
    $exists = DB::table('domains')->where('domain', $domain)->exists();
    
    if (!$exists) {
        $t->domains()->create(['domain' => $domain]);
        echo "Created domain $domain for tenant {$t->id}\n";
    } else {
        echo "Domain $domain already exists for tenant {$t->id}\n";
    }
}

echo "Repair finished.\n";
