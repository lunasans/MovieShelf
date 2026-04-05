<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantActivation
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run this if we are on a tenant domain
        if (function_exists('tenancy') && tenancy()->initialized) {
            $tenant = tenancy()->tenant;

            // If not activated and not trying to activate, logout or on central domain
            if (!$tenant->activated_at) {
                // Allow logout so users aren't stuck
                if ($request->is('logout') || $request->is('activate/*')) {
                    return $next($request);
                }

                // Redirect to central landing page with message
                $centralUrl = config('app.url');
                return redirect()->away($centralUrl)->with('error', 'Dein MovieShelf ist noch nicht aktiviert. Bitte klicke auf den Link in der E-Mail, die wir dir geschickt haben.');
            }
        }

        return $next($request);
    }
}
