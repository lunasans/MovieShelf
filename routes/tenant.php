<?php

declare(strict_types=1);

use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
*/

// --- ULTIMATE IMAGE RESOLUTION PROXY ---
$serveImage = function ($path) {
    $tenantId = tenancy()->tenant->id;
    $tenantBase = realpath(base_path("storage/tenant{$tenantId}/app/public")) ?: '';
    $centralBase = realpath(base_path('storage/app/public')) ?: '';

    $tryPaths = [$path];

    // Add folder variants (cover/covers, backdrop/backdrops)
    if (str_contains($path, 'cover')) {
        $tryPaths[] = 'covers/' . basename($path);
        $tryPaths[] = 'cover/' . basename($path);
        $tryPaths[] = basename($path); // also try root if it's already covers/something
    } elseif (str_contains($path, 'backdrop')) {
        $tryPaths[] = 'backdrops/' . basename($path);
        $tryPaths[] = 'backdrop/' . basename($path);
        $tryPaths[] = basename($path);
    } elseif (str_contains($path, 'actor')) {
        $tryPaths[] = 'actors/' . basename($path);
    }

    foreach (array_unique($tryPaths) as $tp) {
        $files = [
            base_path("storage/tenant{$tenantId}/app/public/{$tp}"),
            base_path("storage/app/public/{$tp}")
        ];
        foreach ($files as $f) {
            $resolved = realpath($f);
            if ($resolved !== false) {
                $inTenant = $tenantBase && str_starts_with($resolved, $tenantBase . DIRECTORY_SEPARATOR);
                $inCentral = $centralBase && str_starts_with($resolved, $centralBase . DIRECTORY_SEPARATOR);
                if ($inTenant || $inCentral) {
                    return response()->file($resolved, [
                        'X-Storage-Proxy' => 'hit',
                        'X-Storage-Route' => 'hit',
                        'Cache-Control' => 'no-cache, private'
                    ]);
                }
            }
        }
    }
    return response('File not found', 404, ['X-Storage-Proxy' => 'fail']);
};

// Catch /media/{path}
Route::get('/media/{path}', function ($path) use ($serveImage) {
    return $serveImage($path);
})->where('path', '.*');

// Catch root level images (e.g. rene.movieshelf.info/tmdb_abc.jpg)
Route::get('/{file}', function ($file) use ($serveImage) {
    if (preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $file)) {
        return $serveImage($file);
    }
    abort(404);
})->where('file', '.*\.(jpg|jpeg|png|webp|gif|svg)$');


Route::middleware([
    'tenant.activated',
])->group(function () {
    
    $profilePath = '/profile';

    Route::get('/', [MovieController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [MovieController::class, 'index'])->name('dashboard.redirect');

    Route::get('/movies/random', [MovieController::class, 'random'])->name('movies.random');

    Route::get('/lang/{locale}', function ($locale) {
        if (in_array($locale, ['de', 'en'])) {
            session(['locale' => $locale]);
        }
        return back();
    })->name('lang.switch');

    Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
    Route::get('/movies/{movie}/details', [MovieController::class, 'details'])->name('movies.details');
    Route::get('/movies/{movie}/boxset', [MovieController::class, 'boxset'])->name('movies.boxset');

    Route::get('/actors', [\App\Http\Controllers\ActorController::class, 'index'])->name('actors.index');
    Route::get('/actors/{actor}', [\App\Http\Controllers\ActorController::class, 'show'])->name('actors.show');
    Route::get('/actors/{actor}/details', [\App\Http\Controllers\ActorController::class, 'details'])->name('actors.details');
    Route::get('/trailers', [\App\Http\Controllers\TrailerController::class, 'index'])->name('movies.trailers');
    Route::get('/impressum', [\App\Http\Controllers\ImpressumController::class, 'index'])->name('impressum');

    Route::get('/statistics', [\App\Http\Controllers\StatsController::class, 'index'])->name('statistics');
    Route::post('/theme/save', [\App\Http\Controllers\ThemeController::class, 'save'])->name('theme.save');

    // Cadmin Impersonation
    Route::get('/impersonate/{token}', [\App\Http\Controllers\ImpersonateController::class, 'login'])->name('impersonate.login');
    Route::get('/impersonate-exit', [\App\Http\Controllers\ImpersonateController::class, 'exit'])->name('impersonate.exit');

    Route::middleware('auth')->group(function () use ($profilePath) {
        Route::get($profilePath, [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch($profilePath, [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.settings.update');
        Route::post('/profile/layout', [ProfileController::class, 'toggleLayout'])->name('profile.layout.toggle');
        Route::delete($profilePath, [ProfileController::class, 'destroy'])->name('profile.destroy');

        // 2FA Routes
        Route::post('/two-factor-authentication', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
        Route::post('/two-factor-confirmation', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
        Route::delete('/two-factor-authentication', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
        Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
        Route::post('/two-factor-challenge', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
        Route::post('/two-factor-recovery-codes', [TwoFactorController::class, 'regenerateCodes'])->name('two-factor.recovery-codes');

        Route::post('/movies/{movie}/watched', [\App\Http\Controllers\MovieWatchedController::class, 'toggle'])->name('movies.watched.toggle');
        Route::post('/movies/{movie}/wishlist', [\App\Http\Controllers\MovieWishlistController::class, 'toggle'])->name('movies.wishlist.toggle');
        Route::post('/movies/{movie}/rate', [\App\Http\Controllers\MovieRatingController::class, 'store'])->name('movies.rate');

        // Lists
        Route::get('/lists', [\App\Http\Controllers\ListController::class, 'index'])->name('lists.index');
        Route::post('/lists', [\App\Http\Controllers\ListController::class, 'store'])->name('lists.store');
        Route::get('/lists/{list}', [\App\Http\Controllers\ListController::class, 'show'])->name('lists.show');
        Route::patch('/lists/{list}', [\App\Http\Controllers\ListController::class, 'update'])->name('lists.update');
        Route::delete('/lists/{list}', [\App\Http\Controllers\ListController::class, 'destroy'])->name('lists.destroy');
        Route::post('/lists/{list}/movies', [\App\Http\Controllers\ListController::class, 'addMovie'])->name('lists.add-movie');
        Route::delete('/lists/{list}/movies/{movie}', [\App\Http\Controllers\ListController::class, 'removeMovie'])->name('lists.remove-movie');
        Route::post('/lists/{list}/import-tmdb', [\App\Http\Controllers\ListController::class, 'importFromTmdb'])->name('lists.import-tmdb');

        // Admin Area
        Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('dashboard');
            Route::get('movies/export', [\App\Http\Controllers\Admin\MovieController::class, 'export'])->name('movies.export');
            Route::get('movies/duplicates', [\App\Http\Controllers\Admin\MovieController::class, 'duplicates'])->name('movies.duplicates');
            Route::post('movies/batch', [\App\Http\Controllers\Admin\MovieController::class, 'batchAction'])->name('movies.batch');
            Route::get('movies/sync-logs', [\App\Http\Controllers\Admin\TrailerSyncController::class, 'index'])->name('movies.sync-logs');
            Route::get('movies/sync-logs/{run}', [\App\Http\Controllers\Admin\TrailerSyncController::class, 'show'])->name('movies.sync-logs.show');
            Route::post('movies/smart-trailer', [\App\Http\Controllers\Admin\MovieController::class, 'smartTrailerSync'])->name('movies.smart-trailer');
            Route::resource('movies', \App\Http\Controllers\Admin\MovieController::class);
            Route::resource('actors', \App\Http\Controllers\Admin\ActorController::class);
            Route::get('actors-search', [\App\Http\Controllers\Admin\ActorController::class, 'search'])->name('actors.search');

            Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
            Route::post('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
            Route::post('settings/test-mail', [\App\Http\Controllers\Admin\SettingController::class, 'testMail'])->name('settings.test-mail');

            // TMDb Import
            Route::get('tmdb', [\App\Http\Controllers\Admin\TmdbImportController::class, 'index'])->name('tmdb.index');
            Route::get('tmdb/search', [\App\Http\Controllers\Admin\TmdbImportController::class, 'search'])->name('tmdb.search');
            Route::get('tmdb/details', [\App\Http\Controllers\Admin\TmdbImportController::class, 'details'])->name('tmdb.details');
            Route::post('tmdb/import', [\App\Http\Controllers\Admin\TmdbImportController::class, 'import'])->name('tmdb.import');
            Route::get('tmdb/update-list', [\App\Http\Controllers\Admin\TmdbImportController::class, 'getMoviesForUpdate'])->name('tmdb.update-list');
            Route::post('tmdb/bulk-update', [\App\Http\Controllers\Admin\TmdbImportController::class, 'bulkUpdate'])->name('tmdb.bulk-update');
            Route::get('tmdb/unlinked-list', [\App\Http\Controllers\Admin\TmdbImportController::class, 'getUnlinkedMovies'])->name('tmdb.unlinked-list');
            Route::post('tmdb/auto-link', [\App\Http\Controllers\Admin\TmdbImportController::class, 'autoLinkMovie'])->name('tmdb.auto-link');

            // XML Import
            Route::get('import', [\App\Http\Controllers\Admin\XmlImportController::class, 'index'])->name('import.index');
            Route::post('import', [\App\Http\Controllers\Admin\XmlImportController::class, 'import'])->name('import.post');
            // Backup Import (Dedicated Page)
            Route::get('backup-import', [\App\Http\Controllers\Admin\BackupImportController::class, 'index'])->name('import.backup.index');
            Route::post('import/backup', [\App\Http\Controllers\Admin\BackupImportController::class, 'import'])->name('import.backup.upload');
            Route::post('import/backup-local', [\App\Http\Controllers\Admin\BackupImportController::class, 'importLocal'])->name('import.backup.local');
            Route::post('import/backup/chunk', [\App\Http\Controllers\Admin\BackupImportController::class, 'uploadChunk'])->name('import.backup.chunk');
            Route::delete('import/backup/{filename}', [\App\Http\Controllers\Admin\BackupImportController::class, 'destroy'])->name('import.backup.destroy');
            Route::delete('import/{filename}', [\App\Http\Controllers\Admin\XmlImportController::class, 'destroy'])->name('import.destroy');

            // Users
            Route::resource('users', \App\Http\Controllers\Admin\UserController::class);



            // Bot
            Route::get('bot', [\App\Http\Controllers\Admin\BotController::class, 'index'])->name('bot.index');
            Route::post('bot/start', [\App\Http\Controllers\Admin\BotController::class, 'start'])->name('bot.start');
            Route::post('bot/process', [\App\Http\Controllers\Admin\BotController::class, 'process'])->name('bot.process');
            Route::post('bot/cancel', [\App\Http\Controllers\Admin\BotController::class, 'cancel'])->name('bot.cancel');
            Route::get('bot/status', [\App\Http\Controllers\Admin\BotController::class, 'status'])->name('bot.status');
            Route::get('bot/{botRun}/logs', [\App\Http\Controllers\Admin\BotController::class, 'logs'])->name('bot.logs');

            // Migration v1 -> v2
            Route::get('migration', [\App\Http\Controllers\Admin\MigrationController::class, 'index'])->name('migration.index');
            Route::post('migration/run', [\App\Http\Controllers\Admin\MigrationController::class, 'run'])->name('migration.run');

            // Statistics
            Route::get('stats', [\App\Http\Controllers\Admin\StatsController::class, 'index'])->name('stats.index');
        });
    });

    // Signatur-Banner
    Route::get('/signature', [\App\Http\Controllers\SignatureController::class, 'show'])->name('signature');

    // OAuth2 Authorization (needs session/web context)
    Route::get('/oauth/authorize',  [\App\Http\Controllers\Api\OAuthController::class, 'authorize']);
    Route::post('/oauth/authorize', [\App\Http\Controllers\Api\OAuthController::class, 'approveAuthorize'])->middleware('auth');
    Route::post('/oauth/token',     [\App\Http\Controllers\Api\OAuthController::class, 'token']);
    Route::get('/oauth/userinfo',   [\App\Http\Controllers\Api\OAuthController::class, 'userinfo'])->middleware('auth:sanctum');

    // Auth Routes for Tenants
    require __DIR__.'/auth.php';
});
