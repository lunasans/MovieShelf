<?php

namespace App\Http\Controllers;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class TwoFactorController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $qrCodeSvg = null;
        $secret = null;

        if ($user->two_factor_secret && ! $user->two_factor_confirmed_at) {
            $secret = $user->two_factor_secret;
            $qrCodeSvg = $this->generateQrCodeSvg($user->email, $secret);
        }

        return view('profile.partials.two-factor-management', [
            'user' => $user,
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $secret,
        ]);
    }

    public function enable()
    {
        $user = Auth::user();

        if (! $user->two_factor_secret) {
            $user->update([
                'two_factor_secret' => Google2FA::generateSecretKey(),
            ]);
        }

        return back()->with('status', 'two-factor-enabled-step-1');
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = Auth::user();

        if (Google2FA::verifyKey($user->two_factor_secret, $request->code)) {
            $recoveryCodes = $this->generateRecoveryCodes();

            $user->update([
                'two_factor_confirmed_at' => now(),
                'two_factor_recovery_codes' => json_encode($recoveryCodes),
            ]);

            return back()
                ->with('status', 'two-factor-confirmed')
                ->with('recoveryCodes', collect($recoveryCodes)->pluck('code')->toArray());
        }

        return back()->withErrors(['code' => __('The provided two-factor authentication code was invalid.')]);
    }

    public function disable()
    {
        try {
            Auth::user()->update([
                'two_factor_secret' => null,
                'two_factor_confirmed_at' => null,
                'two_factor_recovery_codes' => null,
            ]);

            return back()->with('status', 'two-factor-disabled');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('2FA Disable Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function challenge()
    {
        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $user = Auth::user();

        // Check if a recovery code was submitted
        $recoveryCode = $request->input('recovery_code');
        if ($recoveryCode) {
            $recoveryCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true);
            $normalizedInput = strtoupper(str_replace('-', '', trim($recoveryCode)));

            foreach ($recoveryCodes as $index => $entry) {
                // Support both old flat format and new object format
                $code = is_array($entry) ? $entry['code'] : $entry;
                $isUsed = is_array($entry) ? !empty($entry['used_at']) : false;

                if ($isUsed) continue;

                $normalizedStored = strtoupper(str_replace('-', '', $code));
                if (hash_equals($normalizedStored, $normalizedInput)) {
                    // Mark as used with timestamp
                    $recoveryCodes[$index] = ['code' => $code, 'used_at' => now()->toIso8601String()];
                    $user->forceFill([
                        'two_factor_recovery_codes' => json_encode($recoveryCodes),
                    ])->save();

                    $request->session()->put('two_factor_verified', true);
                    return redirect()->intended(route('dashboard'));
                }
            }

            return back()->withErrors(['recovery_code' => __('Der Backup-Code ist ungültig.')]);
        }

        // OTP verification
        $request->validate([
            'code' => 'required|string',
        ]);

        if (Google2FA::verifyKey($user->two_factor_secret, $request->code)) {
            $request->session()->put('two_factor_verified', true);
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['code' => __('Der eingegebene Code ist ungültig.')]);
    }

    public function regenerateCodes()
    {
        $user = Auth::user();

        if (! $user->hasTwoFactorEnabled()) {
            return back()->withErrors(['2fa' => __('2FA ist nicht aktiviert.')]);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ]);

        return back()
            ->with('status', 'recovery-codes-regenerated')
            ->with('recoveryCodes', collect($recoveryCodes)->pluck('code')->toArray());
    }

    private function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = [
                'code' => strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(2)),
                'used_at' => null,
            ];
        }
        return $codes;
    }

    private function generateQrCodeSvg($email, $secret)
    {
        $g2faUrl = Google2FA::getQRCodeUrl(
            config('app.name'),
            $email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);

        return $writer->writeString($g2faUrl);
    }
}
