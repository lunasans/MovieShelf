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
Route::get('/', [\App\Http\Controllers\Central\LandingController::class, 'index'])->name('landing');

Route::get('/privacy', function () {
    return view('central.privacy');
})->name('privacy');

Route::get('/account-deletion', function () {
    return view('central.account-deletion');
})->name('account-deletion');

Route::get('/impressum', function () {
    return view('central.impressum');
})->name('saas.impressum');

Route::get('/p/{slug}', [\App\Http\Controllers\Central\LandingController::class, 'page'])->name('landing.page');

Route::get('/api/check-subdomain', [\App\Http\Controllers\Central\RegisterTenantController::class, 'checkSubdomain'])->name('api.check.subdomain')->middleware('throttle:30,1');
Route::post('/claim', [\App\Http\Controllers\Central\RegisterTenantController::class, 'store'])->name('tenant.register')->middleware('throttle:3,10');
Route::get('/activate/{token}', [\App\Http\Controllers\Central\RegisterTenantController::class, 'activate'])->name('tenant.activate');
Route::get('/forget-shelf/{tenant}', [\App\Http\Controllers\Central\CentralDeletionController::class, 'confirm'])->name('central.tenant.forget')->middleware(['signed']);

// Central Storage Proxy (Required when public/storage symlink is removed)
Route::get('/media/{path}', function ($path) {
    $storageBase = realpath(base_path('storage/app/public'));

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
        $resolvedPath = realpath($fullPath);
        if ($resolvedPath !== false && $storageBase !== false
            && str_starts_with($resolvedPath, $storageBase . DIRECTORY_SEPARATOR)) {
            return response()->file($resolvedPath, ['X-Storage-Proxy' => 'central-web']);
        }
    }

    return response('File not found', 404, ['X-Storage-Proxy' => 'fail-web']);
})->where('path', '.*');

// Master Routes (Global ACP)
Route::middleware(['web', 'auth', 'central.admin'])->group(function () {
    Route::prefix('cadmin')->name('cadmin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Cadmin\GlobalAdminController::class, 'index'])->name('dashboard');
        Route::get('/tenants', [\App\Http\Controllers\Cadmin\GlobalAdminController::class, 'tenants'])->name('tenants');
        Route::post('/tenants/{tenant}/activate', [\App\Http\Controllers\Cadmin\GlobalAdminController::class, 'activate'])->name('tenants.activate');
        Route::delete('/tenants/{tenant}/delete', [\App\Http\Controllers\Cadmin\GlobalAdminController::class, 'delete'])->name('tenants.delete');
        Route::get('/settings', [\App\Http\Controllers\Cadmin\GlobalAdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Cadmin\GlobalAdminController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/test-mail', [\App\Http\Controllers\Cadmin\GlobalAdminController::class, 'testMail'])->name('settings.test-mail');

        // FAQ Management
        Route::resource('faqs', \App\Http\Controllers\Cadmin\CentralFaqController::class);

        // Landing Page Editor
        Route::prefix('landing')->name('landing.')->group(function () {
            // Screenshots
            Route::get('screenshots', [\App\Http\Controllers\Cadmin\LandingScreenshotController::class, 'index'])->name('screenshots');
            Route::post('screenshots', [\App\Http\Controllers\Cadmin\LandingScreenshotController::class, 'store'])->name('screenshots.store');
            Route::patch('screenshots/{screenshot}', [\App\Http\Controllers\Cadmin\LandingScreenshotController::class, 'update'])->name('screenshots.update');
            Route::delete('screenshots/{screenshot}', [\App\Http\Controllers\Cadmin\LandingScreenshotController::class, 'destroy'])->name('screenshots.destroy');
            Route::post('screenshots/reorder', [\App\Http\Controllers\Cadmin\LandingScreenshotController::class, 'reorder'])->name('screenshots.reorder');

            // Sub-pages
            Route::resource('pages', \App\Http\Controllers\Cadmin\LandingPageController::class);
        });
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
