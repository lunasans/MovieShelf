<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCentralAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Erst prüfen wir, ob wir uns auf einer zentralen Domain befinden
        $centralDomains = config('tenancy.central_domains', []);
        if (! in_array($request->getHost(), $centralDomains)) {
            abort(404); // So tun als gäbe es die Seite auf Subdomains nicht
        }

        if (! auth()->check()) {
            abort(403, 'Zugriff verweigert.');
        }

        $adminEmails = array_filter(
            array_map('trim', explode(',', config('app.central_admin_emails', '')))
        );

        if (empty($adminEmails) || ! in_array(auth()->user()->email, $adminEmails)) {
            abort(403, 'Zugriff verweigert.');
        }

        return $next($request);
    }
}
