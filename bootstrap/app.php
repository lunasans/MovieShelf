<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        // api: registered per-tenant in TenancyServiceProvider
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            // Only global-safe middleware here
        ]);

        $middleware->validateCsrfTokens(except: []);

        $middleware->redirectTo(
            guests: '/login',
            users: '/admin'
        );

        $middleware->alias([
            'tenancy' => \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            'prevent-central-access' => \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            'tenant.activated' => \App\Http\Middleware\CheckTenantActivation::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'central.admin' => \App\Http\Middleware\EnsureCentralAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException $e, $request) {
            return redirect()->away(config('app.url'))->with('error', 'Dieses MovieShelf existiert leider nicht mehr.');
        });
        $exceptions->render(function (\Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedByPathException $e, $request) {
            return redirect()->away(config('app.url'))->with('error', 'Dieses MovieShelf existiert leider nicht mehr.');
        });
    })->create();
