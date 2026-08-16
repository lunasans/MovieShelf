<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\Concerns\ResolvesAuthView;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\TrustedDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use ResolvesAuthView;

    /**
     * Display the login view.
     *
     * routes/auth.php haengt an beiden Domains. Auf der Central-Domain zeigen wir
     * die Variante im Landingpage-Design, auf den Regalen den App-Look, der zum
     * Bereich hinter dem Login passt.
     */
    public function create(Request $request): View
    {
        return view($this->authView($request, 'login'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // After OAuth login: redirect back to authorize endpoint with stored params
        if ($request->session()->has('oauth_pending')) {
            $params = $request->session()->pull('oauth_pending');
            return redirect('/oauth/authorize?' . http_build_query($params));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Beim bewussten Abmelden gilt das Geraet nicht mehr als vertraut –
        // sonst kaeme der naechste Nutzer an diesem Rechner ohne 2FA hinein.
        TrustedDevice::forget();

        return redirect('/');
    }
}
