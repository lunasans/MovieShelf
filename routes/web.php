<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [MovieController::class, 'index'])
    ->name('dashboard');

Route::get('/movies/random', [MovieController::class, 'random'])
    ->name('movies.random');

Route::get('/movies/{movie}', [MovieController::class, 'show'])
    ->name('movies.show');

Route::get('/movies/{movie}/details', [MovieController::class, 'details'])
    ->name('movies.details');

Route::get('/actors', [\App\Http\Controllers\ActorController::class, 'index'])->name('actors.index');
Route::get('/actors/{actor}', [\App\Http\Controllers\ActorController::class, 'show'])->name('actors.show');
Route::get('/actors/{actor}/details', [\App\Http\Controllers\ActorController::class, 'details'])->name('actors.details');
Route::get('/trailers', [\App\Http\Controllers\TrailerController::class, 'index'])->name('movies.trailers');
Route::get('/impressum', [\App\Http\Controllers\ImpressumController::class, 'index'])->name('impressum');

Route::get('/statistics', [\App\Http\Controllers\StatsController::class, 'index'])->name('statistics');
Route::post('/theme/save', [\App\Http\Controllers\ThemeController::class, 'save'])->name('theme.save');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 2FA Routes
    Route::post('/two-factor-authentication', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/two-factor-confirmation', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('/two-factor-authentication', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verify'])->name('two-factor.verify');

    Route::post('/movies/{movie}/watched', [\App\Http\Controllers\MovieWatchedController::class, 'toggle'])->name('movies.watched.toggle');

    // Admin Area
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('dashboard');
        Route::resource('movies', \App\Http\Controllers\Admin\MovieController::class);
        Route::resource('actors', \App\Http\Controllers\Admin\ActorController::class);
        
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
        Route::delete('import/{filename}', [\App\Http\Controllers\Admin\XmlImportController::class, 'destroy'])->name('import.destroy');

        // Users
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

        // System Update
        Route::get('update', [\App\Http\Controllers\Admin\SystemUpdateController::class, 'index'])->name('update.index');
        Route::post('update/check', [\App\Http\Controllers\Admin\SystemUpdateController::class, 'check'])->name('update.check');
        Route::post('update/run', [\App\Http\Controllers\Admin\SystemUpdateController::class, 'update'])->name('update.run');

        // Migration v1 -> v2
        Route::get('migration', [\App\Http\Controllers\Admin\MigrationController::class, 'index'])->name('migration.index');
        Route::post('migration/run', [\App\Http\Controllers\Admin\MigrationController::class, 'run'])->name('migration.run');

        // Statistics
        Route::get('stats', [\App\Http\Controllers\Admin\StatsController::class, 'index'])->name('stats.index');
    });
});
// Signatur-Banner
Route::get('/signature', [\App\Http\Controllers\SignatureController::class, 'show'])->name('signature');
require __DIR__.'/auth.php';
