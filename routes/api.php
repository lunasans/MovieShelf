<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/info', [\App\Http\Controllers\Api\InfoController::class, 'index']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/login/2fa', [AuthController::class, 'login2fa'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/user', [AuthController::class, 'update']);

    Route::get('/movies', [MovieController::class, 'index']);
    Route::get('/movies/{movie}', [MovieController::class, 'show']);
    Route::get('/search', [MovieController::class, 'search']);
    Route::post('/movies/{movie}/watched', [MovieController::class, 'toggleWatched']);

    Route::get('/actors', [\App\Http\Controllers\Api\ActorController::class, 'index']);
    Route::get('/actors/search', [\App\Http\Controllers\Api\ActorController::class, 'search']);
    Route::get('/actors/{actor}', [\App\Http\Controllers\Api\ActorController::class, 'show']);

    Route::get('/stats', [\App\Http\Controllers\Api\StatsController::class, 'index']);

    // TMDb Integration
    Route::prefix('tmdb')->group(function () {
        Route::get('/search', [\App\Http\Controllers\Api\TmdbController::class, 'search']);
        Route::get('/details', [\App\Http\Controllers\Api\TmdbController::class, 'details']);
        Route::post('/import', [\App\Http\Controllers\Api\TmdbController::class, 'import']);
    });

    // Admin-only endpoints
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::post('/movies', [\App\Http\Controllers\Api\AdminMovieController::class, 'store']);
        Route::put('/movies/{movie}', [\App\Http\Controllers\Api\AdminMovieController::class, 'update']);
        Route::delete('/movies/{movie}', [\App\Http\Controllers\Api\AdminMovieController::class, 'destroy']);
        Route::post('/movies/{movie}/cover', [\App\Http\Controllers\Api\AdminMovieController::class, 'uploadCover']);
        Route::post('/movies/{movie}/backdrop', [\App\Http\Controllers\Api\AdminMovieController::class, 'uploadBackdrop']);
        Route::get('/export', [\App\Http\Controllers\Api\AdminMovieController::class, 'export']);
    });
});
