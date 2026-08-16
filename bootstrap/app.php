<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Siehe config/trustedproxy.php – '*' wuerde gefaelschte X-Forwarded-For-Header
        // und damit das Umgehen aller IP-Rate-Limits erlauben. Die Config ist an
        // dieser Stelle des Bootstraps noch nicht geladen, daher direkt einbinden.
        $middleware->trustProxies(
            at: (require __DIR__.'/../config/trustedproxy.php')['proxies']
        );
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\TwoFactorMiddleware::class,
            \App\Http\Middleware\VisitorCounterMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'oauth/token',
        ]);

        // Eigene CSRF-Pruefung: identisch zu Laravels, protokolliert eine
        // Ablehnung aber mit Kontext (sonst ist ein 419 nicht untersuchbar).
        $middleware->web(replace: [
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class => \App\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $middleware->redirectTo(
            guests: '/login',
            users: '/dashboard'
        );

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

/*
|--------------------------------------------------------------------------
| .env ausserhalb des App-Roots (optional)
|--------------------------------------------------------------------------
| Reihenfolge:
|   1. Echte Umgebungsvariable LARAVEL_ENV_PATH (Verzeichnis der .env)
|   2. Sonst: eine Ebene UEBER dem App-Root, falls dort eine .env liegt
| Wird nichts gefunden, bleibt der Standard (.env im App-Root) – lokal
| aendert sich also nichts. Greift fuer Web UND CLI/Cron gleichermassen,
| da es im Bootstrap vor dem Laden der Umgebungsvariablen ausgewertet wird.
*/
$envDir = getenv('LARAVEL_ENV_PATH') ?: null;

if (! $envDir) {
    $parentDir = dirname($app->basePath());
    if (is_file($parentDir . DIRECTORY_SEPARATOR . '.env')) {
        $envDir = $parentDir;
    }
}

if ($envDir && is_dir($envDir)) {
    $app->useEnvironmentPath($envDir);
}

return $app;
