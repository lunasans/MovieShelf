<?php

namespace App\Http\Middleware;

use App\Support\TrustedDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasTwoFactorEnabled() &&
            ! $request->session()->has('two_factor_verified') &&
            ! $request->is('two-factor-challenge', 'logout')) {

            // Geraet wurde nach einer erfolgreichen 2FA-Eingabe als vertraut
            // markiert ("Angemeldet bleiben") – dann keine erneute OTP-Abfrage.
            if (TrustedDevice::matches($request, $user)) {
                $request->session()->put('two_factor_verified', true);

                return $next($request);
            }

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
