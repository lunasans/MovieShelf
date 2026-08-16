<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

/**
 * Wie Laravels CSRF-Pruefung, protokolliert eine Ablehnung aber nachvollziehbar.
 *
 * Ohne das ist ein 419 nicht zu untersuchen: die Fehlerseite nennt keinen Grund
 * und Laravel fuehrt die TokenMismatchException intern in der Nicht-Melden-
 * Liste, sie taucht also auch im Log nicht auf. Geloggt werden nur Metadaten –
 * gekuerzte Kennungen und Laengen, nie ein gueltiges Token.
 */
class VerifyCsrfToken extends ValidateCsrfToken
{
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            $sessionToken = $request->hasSession() ? (string) $request->session()->token() : null;
            $sent = (string) ($request->input('_token') ?: $request->header('X-CSRF-TOKEN'));
            $cookie = config('session.cookie');

            Log::warning('CSRF abgelehnt', [
                'pfad'            => $request->path(),
                'cookie_name'     => $cookie,
                'cookie_gesendet' => $request->cookies->has($cookie),
                'cookie_namen'    => implode(', ', array_keys($request->cookies->all())),
                'treiber'         => config('session.driver'),
                'session_pfad'    => config('session.files'),
                'session_id'      => $request->hasSession() ? substr($request->session()->getId(), 0, 8).'…' : null,
                'content_type'    => $request->header('Content-Type'),
                'body_laenge'     => strlen($request->getContent()),
                'body_felder'     => implode(', ', array_keys((array) $request->json()->all() ?: $request->all())),
                'header_da'       => $request->hasHeader('X-CSRF-TOKEN') ? 'ja' : 'nein',
                'token_gesendet'  => $sent === '' ? 'LEER' : substr($sent, 0, 8).'… ('.strlen($sent).' Zeichen)',
                'token_session'   => $sessionToken === null || $sessionToken === ''
                    ? 'LEER'
                    : substr($sessionToken, 0, 8).'… ('.strlen($sessionToken).' Zeichen)',
            ]);

            throw $e;
        }
    }
}
