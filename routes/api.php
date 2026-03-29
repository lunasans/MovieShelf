<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/info', [\App\Http\Controllers\Api\InfoController::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/2fa', [AuthController::class, 'login2fa']);

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

    // TMDb Integration
    Route::prefix('tmdb')->group(function () {
        Route::get('/search', [\App\Http\Controllers\Api\TmdbController::class, 'search']);
        Route::get('/details', [\App\Http\Controllers\Api\TmdbController::class, 'details']);
        Route::post('/import', [\App\Http\Controllers\Api\TmdbController::class, 'import']);
    });
});
