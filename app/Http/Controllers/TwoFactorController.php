<?php

namespace App\Http\Controllers;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function enable(Request $request)
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
            $user->update([
                'two_factor_confirmed_at' => now(),
            ]);

            return back()->with('status', 'two-factor-confirmed');
        }

        return back()->withErrors(['code' => __('The provided two-factor authentication code was invalid.')]);
    }

    public function disable(Request $request)
    {
        Auth::user()->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return back()->with('status', 'two-factor-disabled');
    }

    public function challenge()
    {
        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = Auth::user();

        if (Google2FA::verifyKey($user->two_factor_secret, $request->code)) {
            $request->session()->put('two_factor_verified', true);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['code' => __('The provided two-factor authentication code was invalid.')]);
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
