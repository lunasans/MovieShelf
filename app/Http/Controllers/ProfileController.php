<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\TenantDeletionRequest;
use Illuminate\View\View;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $qrCodeSvg = null;
        $secret = null;

        if ($user->two_factor_secret && ! $user->two_factor_confirmed_at) {
            $secret = $user->two_factor_secret;
            $qrCodeSvg = $this->generateQrCodeSvg($user->email, $secret);
        }

        return view('profile.edit', [
            'user' => $user,
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $secret,
        ]);
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

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($request->has('language')) {
            session(['locale' => $request->language]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's application settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'language' => ['required', 'string', 'in:en,de'],
            'layout' => ['required', 'string', 'in:classic,streaming'],
        ]);

        $request->user()->update($validated);

        session(['locale' => $validated['language']]);

        return Redirect::route('profile.edit')->with('status', 'settings-updated');
    }

    /**
     * Toggle the user's layout preference via AJAX.
     */
    public function toggleLayout(Request $request)
    {
        $validated = $request->validate([
            'layout' => ['required', 'string', 'in:classic,streaming'],
        ]);

        $request->user()->update(['layout' => $validated['layout']]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $tenant = tenant();

        if (! $tenant) {
            // Fallback for non-tenant context (should not happen here)
            $user->delete();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return Redirect::to('/');
        }

        // Generate a signed URL for the central domain
        // This link is valid for 1 hour. We force the central root URL to ensure the link points to the main domain.
        URL::forceRootUrl(config('app.url'));
        $deletionUrl = URL::signedRoute('central.tenant.forget', ['tenant' => $tenant->id], now()->addHour());
        URL::forceRootUrl(null);

        // Send the deletion request email
        Mail::to($user->email)->send(new TenantDeletionRequest($tenant->id, $deletionUrl));

        return Redirect::route('profile.edit')->with('status', 'deletion-email-sent');
    }
}
