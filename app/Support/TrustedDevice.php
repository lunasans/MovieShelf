<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

/**
 * "Vertrautes Geraet" fuer die Zwei-Faktor-Anmeldung.
 *
 * Ohne das hier ist "Angemeldet bleiben" fuer 2FA-Konten wirkungslos: Das
 * Recaller-Cookie meldet den Nutzer zwar an, TwoFactorMiddleware findet aber
 * kein 'two_factor_verified' in der frischen Session und schickt ihn zur
 * OTP-Abfrage.
 *
 * Nach bestandener 2FA setzen wir daher zusaetzlich dieses Cookie – aber nur,
 * wenn beim Login "Angemeldet bleiben" angehakt war. Es ist an Nutzer-ID UND
 * 2FA-Secret gebunden: Wird 2FA deaktiviert oder das Secret neu erzeugt, sind
 * automatisch alle vertrauten Geraete entwertet.
 *
 * Das Cookie wird von Laravels EncryptCookies-Middleware verschluesselt; der
 * HMAC schuetzt zusaetzlich gegen Manipulation, falls es je unverschluesselt
 * irgendwo landet.
 */
class TrustedDevice
{
    public const COOKIE = 'two_factor_trusted';

    /** Standard-Gueltigkeit in Tagen, wenn im Admin nichts hinterlegt ist. */
    private const DEFAULT_DAYS = 30;

    public static function days(): int
    {
        $days = (int) Setting::get('two_factor_trusted_days', (string) self::DEFAULT_DAYS);

        // 0 schaltet die Funktion ab, nach oben auf ein Jahr begrenzen.
        return max(0, min($days, 365));
    }

    public static function enabled(): bool
    {
        return self::days() > 0;
    }

    /**
     * Hat der Nutzer beim Login "Angemeldet bleiben" gewaehlt? Erkennbar am
     * Recaller-Cookie, das Laravel dann gesetzt hat.
     */
    public static function userWantsToBeRemembered(Request $request): bool
    {
        return $request->cookies->has(Auth::guard('web')->getRecallerName());
    }

    /** Cookie fuer dieses Geraet ausstellen (queue = geht mit der Antwort raus). */
    public static function remember(User $user): void
    {
        if (! self::enabled()) {
            return;
        }

        Cookie::queue(Cookie::make(
            self::COOKIE,
            self::payload($user),
            self::days() * 24 * 60
        ));
    }

    /** Gilt dieses Geraet als vertraut? */
    public static function matches(Request $request, User $user): bool
    {
        if (! self::enabled()) {
            return false;
        }

        $value = $request->cookie(self::COOKIE);

        if (! is_string($value) || $value === '') {
            return false;
        }

        return hash_equals(self::payload($user), $value);
    }

    public static function forget(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE));
    }

    /**
     * user_id + HMAC ueber ID und 2FA-Secret. Das Secret im HMAC sorgt dafuer,
     * dass ein Secret-Wechsel alle ausgestellten Cookies ungueltig macht.
     */
    private static function payload(User $user): string
    {
        return $user->id . '|' . hash_hmac(
            'sha256',
            $user->id . '|' . (string) $user->two_factor_secret,
            (string) config('app.key')
        );
    }
}
