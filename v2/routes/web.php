<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [MovieController::class, 'index'])
    ->name('dashboard');

Route::get('/movies/{movie}/details', [MovieController::class, 'details'])
    ->name('movies.details');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/statistics', [\App\Http\Controllers\StatsController::class, 'index'])->name('statistics');

    // Admin Area
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('dashboard');
        Route::resource('movies', \App\Http\Controllers\Admin\MovieController::class);
        Route::resource('actors', \App\Http\Controllers\Admin\ActorController::class);
        
        Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

        // TMDb Import
        Route::get('tmdb', [\App\Http\Controllers\Admin\TmdbImportController::class, 'index'])->name('tmdb.index');
        Route::get('tmdb/search', [\App\Http\Controllers\Admin\TmdbImportController::class, 'search'])->name('tmdb.search');
        Route::post('tmdb/import', [\App\Http\Controllers\Admin\TmdbImportController::class, 'import'])->name('tmdb.import');
    });

    Route::post('/theme/save', [\App\Http\Controllers\ThemeController::class, 'save'])->name('theme.save');
});

require __DIR__.'/auth.php';
