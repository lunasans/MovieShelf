<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Selbst-Registrierung – nur fuer den allerersten Account.
 *
 * Die Cloud kennt /register gar nicht: dort entstehen Regale ueber /claim und
 * weitere Nutzer legt der Regal-Admin an. Standalone gibt es weder das eine
 * noch einen Installer – ohne diesen Weg kaeme man nach einer frischen
 * Installation an keinen einzigen Account.
 *
 * Offen bleibt die Route deshalb nur, solange die Instanz leer ist. Der erste
 * Account wird Admin, danach schliesst sich die Registrierung von selbst und
 * weitere Nutzer kommen ueber /admin/users dazu. So ist ein oeffentlich
 * erreichbares Regal nicht dauerhaft fuer Fremde offen.
 */
class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        abort_if(self::isClosed(), 404);

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Erneut pruefen statt auf das Formular zu vertrauen: zwischen Aufruf
        // und Absenden kann der erste Account bereits entstanden sein.
        abort_if(self::isClosed(), 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // Der erste Account muss administrieren koennen – sonst gaebe es
            // niemanden, der weitere Nutzer oder die Einstellungen anlegt.
            'is_admin' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Ist die Registrierung geschlossen? Sobald ein Nutzer existiert: ja.
     */
    public static function isClosed(): bool
    {
        return User::exists();
    }
}
