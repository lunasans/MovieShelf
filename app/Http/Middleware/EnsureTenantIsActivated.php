<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsActivated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant() && !tenant('activated_at')) {
            abort(403, 'Dieses MovieShelf wurde noch nicht aktiviert. Bitte prüfe deine E-Mails und klicke auf den Aktivierungslink.');
        }

        return $next($request);
    }
}
