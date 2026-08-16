<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OAuthClient;
use App\Models\OAuthAuthCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    // GET /oauth/authorize
    public function authorize(Request $request)
    {
        $request->validate([
            'client_id'              => 'required|string',
            'redirect_uri'           => 'required|string',
            'response_type'          => 'required|in:code',
            'state'                  => 'required|string',
            'code_challenge'         => 'nullable|string',
            'code_challenge_method'  => 'nullable|in:S256',
        ]);

        $client = OAuthClient::where('client_id', $request->client_id)
            ->where('is_active', true)
            ->firstOrFail();

        if (! $this->redirectUriMatches($client->redirect_uri, $request->redirect_uri)) {
            return response()->json(['error' => 'redirect_uri stimmt nicht überein'], 400);
        }

        // Public Clients (ohne Secret) müssen PKCE verwenden
        if ($client->is_public && ! $request->code_challenge) {
            return response()->json(['error' => 'code_challenge ist für diesen Client erforderlich'], 400);
        }

        if (! Auth::check()) {
            session(['oauth_pending' => $request->only([
                'client_id', 'redirect_uri', 'response_type', 'state',
                'code_challenge', 'code_challenge_method',
            ])]);
            return redirect()->route('login');
        }

        return view('oauth.authorize', [
            'client'                => $client,
            'redirect_uri'          => $request->redirect_uri,
            'state'                 => $request->state,
            'code_challenge'        => $request->code_challenge,
            'code_challenge_method' => $request->code_challenge_method ?? 'S256',
        ]);
    }

    // POST /oauth/authorize
    public function approveAuthorize(Request $request)
    {
        $request->validate([
            'client_id'              => 'required|string',
            'redirect_uri'           => 'required|string',
            'state'                  => 'required|string',
            'approved'               => 'required|boolean',
            'code_challenge'         => 'nullable|string',
            'code_challenge_method'  => 'nullable|in:S256',
        ]);

        $redirectUri = $request->redirect_uri;
        $state       = $request->state;

        $client = OAuthClient::where('client_id', $request->client_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Die redirect_uri muss zum Client gehoeren – sonst wuerde der Auth-Code
        // (bzw. bei Ablehnung ein Open Redirect) an eine fremde URL zugestellt.
        if (! $this->redirectUriMatches($client->redirect_uri, $redirectUri)) {
            return response()->json(['error' => 'redirect_uri stimmt nicht überein'], 400);
        }

        if (! $request->boolean('approved')) {
            return $this->handOverToApp($redirectUri, [
                'error' => 'access_denied',
                'state' => $state,
            ]);
        }

        if ($client->is_public && ! $request->code_challenge) {
            return response()->json(['error' => 'code_challenge ist für diesen Client erforderlich'], 400);
        }

        $code = Str::random(40);

        OAuthAuthCode::create([
            'code'                  => $code,
            'user_id'               => Auth::id(),
            'client_id'             => $client->client_id,
            'redirect_uri'          => $redirectUri,
            'expires_at'            => now()->addMinutes(5),
            'code_challenge'        => $request->code_challenge,
            'code_challenge_method' => $request->code_challenge_method,
        ]);

        return $this->handOverToApp($redirectUri, [
            'code'  => $code,
            'state' => $state,
        ]);
    }

    /**
     * Uebergabe an die aufrufende Anwendung.
     *
     * Web-Clients bekommen wie bisher eine Weiterleitung. Apps mit eigenem
     * URL-Schema (movieshelf://) nicht: Chrome fuehrt eine Server-Weiterleitung
     * in ein fremdes Schema je nach Fassung nicht aus - die Seite bleibt dann
     * stehen, ohne Fehler und ohne dass die App je aufgerufen wird. Belegt am
     * 10.08.2026: POST /oauth/authorize -> 302, aber kein Intent auf dem Geraet.
     *
     * Deshalb fuer solche Schemata eine Zwischenseite, die per JavaScript
     * navigiert und zusaetzlich einen anklickbaren Link anbietet.
     */
    private function handOverToApp(string $redirectUri, array $params)
    {
        $callbackUrl = $redirectUri . '?' . http_build_query($params);

        $scheme = strtolower((string) parse_url($redirectUri, PHP_URL_SCHEME));
        if (in_array($scheme, ['http', 'https'], true)) {
            return redirect($callbackUrl);
        }

        return view('oauth.callback', [
            'callbackUrl' => $callbackUrl,
            'denied'      => isset($params['error']),
        ]);
    }

    // POST /oauth/token
    public function token(Request $request)
    {
        $request->validate([
            'grant_type'    => 'required|in:authorization_code',
            'code'          => 'required|string',
            'redirect_uri'  => 'required|string',
            'client_id'     => 'required|string',
            'client_secret' => 'nullable|string',
            'code_verifier' => 'nullable|string',
        ]);

        $client = OAuthClient::where('client_id', $request->client_id)
            ->where('is_active', true)
            ->first();

        if (! $client) {
            return response()->json(['error' => 'invalid_client'], 401);
        }

        // Public clients brauchen kein Secret, vertrauliche Clients müssen es prüfen
        if (! $client->is_public) {
            if (! $request->client_secret || ! $client->client_secret
                || ! hash_equals($client->client_secret, (string) $request->client_secret)) {
                return response()->json(['error' => 'invalid_client'], 401);
            }
        }

        $authCode = OAuthAuthCode::where('code', $request->code)
            ->where('client_id', $request->client_id)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($authCode && ! $this->redirectUriMatches($authCode->redirect_uri, $request->redirect_uri)) {
            $authCode = null;
        }

        if (! $authCode) {
            return response()->json(['error' => 'invalid_grant'], 400);
        }

        // Public Clients: PKCE ist Pflicht (Schutz vor Auth-Code-Interception)
        if ($client->is_public && ! $authCode->code_challenge) {
            return response()->json(['error' => 'invalid_grant'], 400);
        }

        // PKCE-Verifier prüfen, wenn ein Challenge gespeichert wurde
        if ($authCode->code_challenge) {
            if (! $request->code_verifier) {
                return response()->json(['error' => 'code_verifier fehlt'], 400);
            }
            if (! $this->verifyPkce($request->code_verifier, $authCode->code_challenge, $authCode->code_challenge_method)) {
                return response()->json(['error' => 'invalid_grant'], 400);
            }
        }

        $authCode->update(['used' => true]);

        // User aus der aktiven Tenant-DB laden (nicht über die central-Relation)
        $user  = \App\Models\User::find($authCode->user_id);
        if (! $user) {
            return response()->json(['error' => 'user_not_found'], 400);
        }
        $token = $user->createToken('filmdb')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }

    // GET /oauth/userinfo
    public function userinfo(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id'       => $user->id,
            'name'     => $user->name,
            'username' => $user->username,
            'email'    => $user->email,
        ]);
    }

    private function verifyPkce(string $verifier, string $storedChallenge, ?string $method): bool
    {
        $method = $method ?? 'S256';

        if ($method === 'S256') {
            $computed = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
            return hash_equals($storedChallenge, $computed);
        }

        return false;
    }

    private function redirectUriMatches(string $registered, string $provided): bool
    {
        if ($registered === $provided) return true;

        $r = parse_url($registered);
        $p = parse_url($provided);

        // RFC 8252 §7.3: Bei Loopback-Redirects darf der Port variieren,
        // bei allen anderen Hosts muss er exakt übereinstimmen.
        $isLoopback = in_array($r['host'] ?? '', ['127.0.0.1', 'localhost', '::1'], true);

        return ($r['scheme'] ?? '') === ($p['scheme'] ?? '')
            && ($r['host']   ?? '') === ($p['host']   ?? '')
            && ($isLoopback || ($r['port'] ?? null) === ($p['port'] ?? null))
            && ($r['path']   ?? '/') === ($p['path']  ?? '/');
    }
}
