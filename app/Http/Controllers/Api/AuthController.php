<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class AuthController extends Controller
{
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
            return response()->json([
                'requires_2fa' => true,
                'user_id' => $user->id,
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

    public function login2fa(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'device_name' => 'required',
            'code' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);

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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
