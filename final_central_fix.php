<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "Starting Global Fix...\n";

// 1. Create missing tables in central DB to avoid 500 errors
$tables = [
    'settings' => function (Blueprint $table) {
        $table->id();
        $table->string('key')->unique();
        $table->text('value')->nullable();
        $table->timestamps();
    },
    'counter' => function (Blueprint $table) {
        $table->id();
        $table->string('page')->unique();
        $table->integer('visits')->default(0);
        $table->timestamps();
    },
    'movies' => function (Blueprint $table) {
        $table->id();
        $table->string('title')->nullable();
        $table->boolean('is_deleted')->default(false);
        $table->timestamps();
    },
    'actors' => function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    }
];

foreach ($tables as $name => $callback) {
    if (!Schema::connection('central')->hasTable($name)) {
        Schema::connection('central')->create($name, $callback);
        echo "Created table '$name' in central DB.\n";
    }
}

// 2. Insert test data for hallo1
$tenantId = 'hallo1';
$domains = [$tenantId, $tenantId . '.localhost'];

foreach ($domains as $domain) {
    $exists = DB::connection('central')->table('domains')->where('domain', $domain)->exists();
    if (!$exists) {
        DB::connection('central')->table('domains')->insert([
            'domain' => $domain,
            'tenant_id' => $tenantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Inserted domain '$domain' for tenant '$tenantId'.\n";
    }
}

echo "Global Fix finished successfully.\n";
