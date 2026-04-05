<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            // Only global-safe middleware here
        ]);

        $middleware->validateCsrfTokens(except: [
            'login',
        ]);

        $middleware->alias([
            'tenancy' => \Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain::class,
            'prevent-central-access' => \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            'tenant.activated' => \App\Http\Middleware\CheckTenantActivation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
