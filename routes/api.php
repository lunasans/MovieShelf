<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/info', [\App\Http\Controllers\Api\InfoController::class, 'index']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/login/2fa', [AuthController::class, 'login2fa'])->middleware('throttle:5,1');

// OAuth2 Token & Userinfo (no session needed)
Route::post('/oauth/token',   [\App\Http\Controllers\Api\OAuthController::class, 'token']);
Route::get('/oauth/userinfo', [\App\Http\Controllers\Api\OAuthController::class, 'userinfo'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/user', [AuthController::class, 'update']);

    // 2FA-Verwaltung
    Route::post('/user/2fa/enable',  [\App\Http\Controllers\Api\TwoFactorController::class, 'enable']);
    Route::post('/user/2fa/confirm', [\App\Http\Controllers\Api\TwoFactorController::class, 'confirm']);
    Route::post('/user/2fa/disable', [\App\Http\Controllers\Api\TwoFactorController::class, 'disable']);

    Route::get('/movies', [MovieController::class, 'index']);
    Route::get('/movies/{movie}', [MovieController::class, 'show']);
    Route::get('/search', [MovieController::class, 'search']);
    Route::post('/movies/{movie}/watched', [MovieController::class, 'toggleWatched']);
    Route::post('/movies/{movie}/wishlist', [MovieController::class, 'toggleWishlist']);

    // Bewertung und Folgenstand schreiben — vom Desktop ab 1.0.0 genutzt.
    // Beim Ausbau fuer den Desktop ist nur die Leserichtung entstanden (beides
    // liegt im Export); die Endpunkte zum Schreiben gab es bislang allein im
    // Web-Zweig mit Session-Anmeldung, weshalb der Abgleich an
    // "The route api/movies/{id}/rate could not be found" scheiterte.
    //
    // Dieselben Controller wie im Web-Zweig: sie arbeiten ueber Auth::user(),
    // was unter auth:sanctum genauso greift, und liefern bereits das JSON, das
    // der Desktop erwartet. MovieRatingController nimmt ausdruecklich die 0 als
    // Loeschweg entgegen, EpisodeWatchedController schaltet um — der Desktop
    // liest die Antwort und schaltet notfalls ein zweites Mal.
    Route::post('/movies/{movie}/rate', [\App\Http\Controllers\MovieRatingController::class, 'store']);
    Route::post('/episodes/{episode}/watched', [\App\Http\Controllers\EpisodeWatchedController::class, 'toggle']);

    Route::get('/actors', [\App\Http\Controllers\Api\ActorController::class, 'index']);
    Route::get('/actors/search', [\App\Http\Controllers\Api\ActorController::class, 'search']);
    Route::get('/actors/{actor}', [\App\Http\Controllers\Api\ActorController::class, 'show']);

    Route::get('/tags', [\App\Http\Controllers\Api\TagController::class, 'index']);
    Route::get('/stats', [\App\Http\Controllers\Api\StatsController::class, 'index']);

    // TMDb Integration
    Route::prefix('tmdb')->group(function () {
        Route::get('/search', [\App\Http\Controllers\Api\TmdbController::class, 'search']);
        Route::get('/details', [\App\Http\Controllers\Api\TmdbController::class, 'details']);
        Route::get('/season', [\App\Http\Controllers\Api\TmdbController::class, 'season']);
        Route::post('/import', [\App\Http\Controllers\Api\TmdbController::class, 'import']);
        Route::post('/import-seasons', [\App\Http\Controllers\Api\TmdbController::class, 'importSeasons']);
        Route::post('/remove-seasons', [\App\Http\Controllers\Api\TmdbController::class, 'removeSeasons']);
    });

    // Lists
    Route::get('/lists', [\App\Http\Controllers\Api\ListController::class, 'index']);
    Route::get('/lists/{list}', [\App\Http\Controllers\Api\ListController::class, 'show']);
    Route::post('/lists', [\App\Http\Controllers\Api\ListController::class, 'store']);
    Route::put('/lists/{list}', [\App\Http\Controllers\Api\ListController::class, 'update']);
    Route::delete('/lists/{list}', [\App\Http\Controllers\Api\ListController::class, 'destroy']);

    // Externe Filme (in Listen referenziert, nicht in der Sammlung)
    Route::post('/external-movies', [\App\Http\Controllers\Api\ExternalMovieController::class, 'store']);

    // Admin-only endpoints
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::post('/movies', [\App\Http\Controllers\Api\AdminMovieController::class, 'store']);
        Route::put('/movies/{movie}', [\App\Http\Controllers\Api\AdminMovieController::class, 'update']);
        Route::delete('/movies/{movie}', [\App\Http\Controllers\Api\AdminMovieController::class, 'destroy']);
        Route::post('/movies/{movie}/cover', [\App\Http\Controllers\Api\AdminMovieController::class, 'uploadCover']);
        Route::post('/movies/{movie}/backdrop', [\App\Http\Controllers\Api\AdminMovieController::class, 'uploadBackdrop']);
        Route::post('/movies/{movie}/fetch-trailer', [\App\Http\Controllers\Api\AdminMovieController::class, 'fetchTrailer']);
        Route::get('/export', [\App\Http\Controllers\Api\AdminMovieController::class, 'export']);
    });
});
