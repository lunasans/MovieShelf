<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

try {
    echo "Tenants:\n";
    $tenants = DB::connection('central')->table('tenants')->get();
    foreach ($tenants as $tenant) {
        print_r($tenant);
    }

    echo "\nDomains:\n";
    $domains = DB::connection('central')->table('domains')->get();
    foreach ($domains as $domain) {
        print_r($domain);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
