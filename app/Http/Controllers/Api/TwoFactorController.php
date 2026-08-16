<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class TwoFactorController extends Controller
{
    /**
     * 2FA initiieren: Secret erzeugen (falls noch keins da) und Secret + otpauth-URL
     * zur Einrichtung in einer Authenticator-App zurückgeben. Noch nicht aktiv,
     * bis confirm() mit einem gültigen Code aufgerufen wurde.
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_confirmed_at) {
            return response()->json(['message' => '2FA ist bereits aktiviert.'], 422);
        }

        if (! $user->two_factor_secret) {
            $user->update(['two_factor_secret' => Google2FA::generateSecretKey()]);
        }

        $otpauth = Google2FA::getQRCodeUrl(
            config('app.name', 'MovieShelf'),
            $user->email,
            $user->two_factor_secret
        );

        return response()->json([
            'secret'      => $user->two_factor_secret,
            'otpauth_url' => $otpauth,
        ]);
    }

    /**
     * 2FA bestätigen/aktivieren: Code aus der Authenticator-App prüfen.
     */
    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();

        if (! $user->two_factor_secret) {
            return response()->json(['message' => '2FA wurde nicht initiiert.'], 422);
        }

        if (Google2FA::verifyKey($user->two_factor_secret, $request->code)) {
            $codes = $this->generateRecoveryCodes();

            $user->update([
                'two_factor_confirmed_at'   => now(),
                'two_factor_recovery_codes' => json_encode($codes),
            ]);

            return response()->json([
                'confirmed'      => true,
                'recovery_codes' => collect($codes)->pluck('code')->values(),
            ]);
        }

        return response()->json(['message' => 'Ungültiger Code.'], 422);
    }

    /**
     * 2FA deaktivieren.
     */
    public function disable(Request $request)
    {
        $request->user()->update([
            'two_factor_secret'         => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
        ]);

        return response()->json(['disabled' => true]);
    }

    private function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => ['code' => Str::random(10), 'used' => false])
            ->toArray();
    }
}
