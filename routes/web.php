<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Web Routes
|--------------------------------------------------------------------------
|
| These routes are for the central application (movieshelf.info).
| No tenant logic should be placed here.
|
*/

// Landing Page (SaaS Home)
Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('landing');
Route::get('/debug-domain', function () {
    return [
        'hostname' => request()->getHost(),
        'is_central' => in_array(request()->getHost(), config('tenancy.central_domains')),
        'central_domains' => config('tenancy.central_domains'),
        'tenancy_initialized' => function_exists('tenancy') && tenancy()->initialized,
    ];
});

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/account-deletion', function () {
    return view('account-deletion');
})->name('account-deletion');

Route::get('/api/check-subdomain', [\App\Http\Controllers\RegisterTenantController::class, 'checkSubdomain'])->name('api.check.subdomain');
Route::post('/claim', [\App\Http\Controllers\RegisterTenantController::class, 'store'])->name('tenant.register');
Route::get('/activate/{token}', [\App\Http\Controllers\RegisterTenantController::class, 'activate'])->name('tenant.activate');

// Central Storage Proxy (Required when public/storage symlink is removed)
Route::get('/media/{path}', function ($path) {
    $path = str_replace('..', '', $path);
    
    $tryPaths = [$path];
    
    // Add singular/plural variants for robustness
    if (str_starts_with($path, 'cover/')) {
        $tryPaths[] = str_replace('cover/', 'covers/', $path);
    } elseif (str_starts_with($path, 'covers/')) {
        $tryPaths[] = str_replace('cover/', 'covers/', $path);
    } elseif (str_starts_with($path, 'backdrop/')) {
        $tryPaths[] = str_replace('backdrop/', 'backdrops/', $path);
    } elseif (str_starts_with($path, 'backdrops/')) {
        $tryPaths[] = str_replace('backdrops/', 'backdrop/', $path);
    }

    foreach ($tryPaths as $tryPath) {
        $fullPath = base_path("storage/app/public/$tryPath");
        if (file_exists($fullPath)) {
            return response()->file($fullPath, ['X-Storage-Proxy' => 'central-web']);
        }
    }

    return response('File not found', 404, ['X-Storage-Proxy' => 'fail-web']);
})->where('path', '.*');

// Master Routes (Global ACP)
Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\GlobalAdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/tenants', [\App\Http\Controllers\Admin\GlobalAdminController::class, 'tenants'])->name('admin.tenants');
        Route::post('/tenants/{tenant}/activate', [\App\Http\Controllers\Admin\GlobalAdminController::class, 'activate'])->name('admin.tenants.activate');
        Route::delete('/tenants/{tenant}/delete', [\App\Http\Controllers\Admin\GlobalAdminController::class, 'delete'])->name('admin.tenants.delete');
        Route::get('/settings', [\App\Http\Controllers\Admin\GlobalAdminController::class, 'settings'])->name('admin.settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\GlobalAdminController::class, 'updateSettings'])->name('admin.settings.update');
        Route::post('/settings/test-mail', [\App\Http\Controllers\Admin\GlobalAdminController::class, 'testMail'])->name('admin.settings.test-mail');
    });

    // Telemetry API (Master only, gitignored)
    if (class_exists(\App\Http\Controllers\Api\TelemetryStoreController::class)) {
        Route::post('api/telemetry', [\App\Http\Controllers\Api\TelemetryStoreController::class, 'store'])->name('api.telemetry');
    }
});

// Avoid 500 on central /register
Route::get('/register', function () {
    return redirect()->route('landing');
});

require __DIR__.'/auth.php';
