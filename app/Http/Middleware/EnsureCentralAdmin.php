<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCentralAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check()) {
            abort(403, 'Zugriff verweigert.');
        }

        $adminEmails = array_filter(
            array_map('trim', explode(',', env('CENTRAL_ADMIN_EMAILS', '')))
        );

        if (empty($adminEmails) || ! in_array(auth()->user()->email, $adminEmails)) {
            abort(403, 'Zugriff verweigert.');
        }

        return $next($request);
    }
}
