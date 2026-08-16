<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Setzt Security-Response-Header (CSP & Co.) auf allen Web-Antworten.
 * Die Werte kommen aus config/security.php und sind per .env übersteuerbar.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (config('security.headers', []) as $name => $value) {
            if ($value !== null && $value !== '' && ! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        $hsts = config('security.hsts');
        if ($hsts && $request->secure() && ! $response->headers->has('Strict-Transport-Security')) {
            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        return $response;
    }
}
