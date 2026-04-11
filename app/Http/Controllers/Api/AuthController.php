<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/login',
        summary: 'Benutzer-Login',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'test@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                    new OA\Property(property: 'device_name', type: 'string', example: 'mobile_app')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Erfolgreich eingeloggt oder 2FA-Herausforderung',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'token', type: 'string'),
                                new OA\Property(property: 'user', type: 'object'),
                                new OA\Property(property: 'version', type: 'string')
                            ]
                        ),
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'requires_2fa', type: 'boolean'),
                                new OA\Property(property: 'user_id', type: 'integer'),
                                new OA\Property(property: 'device_name', type: 'string')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validierungsfehler')
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // Check if 2FA is active for this user
        if ($user->two_factor_confirmed_at) {
            $challengeToken = Str::random(40);
            Cache::put('2fa_challenge_'.$challengeToken, $user->id, now()->addMinutes(5));

            return response()->json([
                'requires_2fa' => true,
                '2fa_token' => $challengeToken,
                'device_name' => $request->device_name,
            ]);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'two_factor_enabled' => !! $user->two_factor_confirmed_at,
            ],
            'version' => config('app.version'),
        ]);
    }

    #[OA\Post(
        path: '/login/2fa',
        summary: '2FA-Verifizierung',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: '2fa_token', type: 'string', example: 'abc123...'),
                    new OA\Property(property: 'device_name', type: 'string', example: 'mobile_app'),
                    new OA\Property(property: 'code', type: 'string', example: '123456')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '2FA erfolgreich, Token ausgestellt',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string'),
                        new OA\Property(property: 'user', type: 'object'),
                        new OA\Property(property: 'version', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Ungültiger Code')
        ]
    )]
    public function login2fa(Request $request)
    {
        $request->validate([
            '2fa_token' => 'required|string',
            'device_name' => 'required',
            'code' => 'required|string',
        ]);

        $userId = Cache::pull('2fa_challenge_'.$request->input('2fa_token'));

        if (!$userId) {
            throw ValidationException::withMessages([
                '2fa_token' => ['Ungültige oder abgelaufene 2FA-Sitzung. Bitte erneut einloggen.'],
            ]);
        }

        $user = User::findOrFail($userId);

        if (!$user->two_factor_confirmed_at) {
            return response()->json(['message' => '2FA is not enabled for this user.'], 422);
        }

        if (Google2FA::verifyKey($user->two_factor_secret, $request->code)) {
            $token = $user->createToken($request->device_name)->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'two_factor_enabled' => true,
                ],
                'version' => config('app.version'),
            ]);
        }

        throw ValidationException::withMessages([
            'code' => [__('The provided two-factor authentication code was invalid.')],
        ]);
    }

    #[OA\Put(
        path: '/user',
        summary: 'Benutzerprofil aktualisieren',
        tags: ['User'],
        security: [['apiAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Neuer Name'),
                    new OA\Property(property: 'email', type: 'string', example: 'neu@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'neuespasswort123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'neuespasswort123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil erfolgreich aktualisiert',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'user', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Nicht autorisiert')
        ]
    )]
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'two_factor_enabled' => !! $user->two_factor_confirmed_at,
            ],
        ]);
    }

    #[OA\Post(
        path: '/logout',
        summary: 'Benutzer-Logout',
        tags: ['Auth'],
        security: [['apiAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Erfolgreich ausgeloggt',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            )
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
