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
            'redirect_uri'           => 'required|url',
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

        if (! Auth::check()) {
            return redirect()->route('login', ['intended' => $request->fullUrl()]);
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
            'redirect_uri'           => 'required|url',
            'state'                  => 'required|string',
            'approved'               => 'required|boolean',
            'code_challenge'         => 'nullable|string',
            'code_challenge_method'  => 'nullable|in:S256',
        ]);

        $redirectUri = $request->redirect_uri;
        $state       = $request->state;

        if (! $request->boolean('approved')) {
            return redirect($redirectUri . '?' . http_build_query([
                'error' => 'access_denied',
                'state' => $state,
            ]));
        }

        $client = OAuthClient::where('client_id', $request->client_id)
            ->where('is_active', true)
            ->firstOrFail();

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

        return redirect($redirectUri . '?' . http_build_query([
            'code'  => $code,
            'state' => $state,
        ]));
    }

    // POST /oauth/token
    public function token(Request $request)
    {
        $request->validate([
            'grant_type'    => 'required|in:authorization_code',
            'code'          => 'required|string',
            'redirect_uri'  => 'required|url',
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
            if (! $request->client_secret || $client->client_secret !== $request->client_secret) {
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

        $user  = $authCode->user;
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

        return ($r['scheme'] ?? '') === ($p['scheme'] ?? '')
            && ($r['host']   ?? '') === ($p['host']   ?? '')
            && ($r['path']   ?? '/') === ($p['path']  ?? '/');
    }
}
