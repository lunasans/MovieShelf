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
    // Zeigt dem eingeloggten User die Berechtigungsseite
    public function authorize(Request $request)
    {
        $request->validate([
            'client_id'     => 'required|string',
            'redirect_uri'  => 'required|url',
            'response_type' => 'required|in:code',
            'state'         => 'required|string',
        ]);

        $client = OAuthClient::where('client_id', $request->client_id)
            ->where('is_active', true)
            ->firstOrFail();

        if (! $this->redirectUriMatches($client->redirect_uri, $request->redirect_uri)) {
            return response()->json(['error' => 'redirect_uri stimmt nicht überein'], 400);
        }

        if (! Auth::check()) {
            return redirect()->route('login', [
                'intended' => $request->fullUrl(),
            ]);
        }

        return view('oauth.authorize', [
            'client'       => $client,
            'redirect_uri' => $request->redirect_uri,
            'state'        => $request->state,
        ]);
    }

    // POST /oauth/authorize
    // User genehmigt oder lehnt ab
    public function approveAuthorize(Request $request)
    {
        $request->validate([
            'client_id'    => 'required|string',
            'redirect_uri' => 'required|url',
            'state'        => 'required|string',
            'approved'     => 'required|boolean',
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
            'code'         => $code,
            'user_id'      => Auth::id(),
            'client_id'    => $client->client_id,
            'redirect_uri' => $redirectUri,
            'expires_at'   => now()->addMinutes(5),
        ]);

        return redirect($redirectUri . '?' . http_build_query([
            'code'  => $code,
            'state' => $state,
        ]));
    }

    // POST /oauth/token
    // Tauscht Authorization Code gegen Sanctum-Token
    public function token(Request $request)
    {
        $request->validate([
            'grant_type'    => 'required|in:authorization_code',
            'code'          => 'required|string',
            'redirect_uri'  => 'required|url',
            'client_id'     => 'required|string',
            'client_secret' => 'required|string',
        ]);

        $client = OAuthClient::where('client_id', $request->client_id)
            ->where('client_secret', $request->client_secret)
            ->where('is_active', true)
            ->first();

        if (! $client) {
            return response()->json(['error' => 'invalid_client'], 401);
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

        $authCode->update(['used' => true]);

        $user  = $authCode->user;
        $token = $user->createToken('filmdb')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }

    private function redirectUriMatches(string $registered, string $provided): bool
    {
        // Exakter Match
        if ($registered === $provided) return true;

        // Custom-Protocol (z.B. movieshelf://) – Schema + Host müssen übereinstimmen
        $r = parse_url($registered);
        $p = parse_url($provided);

        return ($r['scheme'] ?? '') === ($p['scheme'] ?? '')
            && ($r['host']   ?? '') === ($p['host']   ?? '')
            && ($r['path']   ?? '/') === ($p['path']  ?? '/');
    }

    // GET /oauth/userinfo  (Authorization: Bearer <token>)
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
}
