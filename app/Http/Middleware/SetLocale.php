<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Reihenfolge: explizite Wahl (Session) > Benutzereinstellung >
     * Accept-Language des Browsers > konfigurierter Default > config/app.php.
     *
     * Laeuft sowohl auf Tenant- als auch auf Central-Routes. Auf Central gibt
     * es keinen angemeldeten Benutzer, dort entscheidet Session bzw. Browser.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $this->resolve($request);

        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    protected function resolve(Request $request): ?string
    {
        if (Session::has('locale') && $this->supported(Session::get('locale'))) {
            return Session::get('locale');
        }

        if (auth()->check() && $this->supported(auth()->user()->language)) {
            return auth()->user()->language;
        }

        if ($fromBrowser = $this->fromAcceptLanguage($request)) {
            return $fromBrowser;
        }

        return $this->defaultLocale();
    }

    /**
     * Erste vom Browser bevorzugte Sprache, die wir auch anbieten.
     * "de-AT,de;q=0.9,en;q=0.8" -> "de"
     */
    protected function fromAcceptLanguage(Request $request): ?string
    {
        foreach ($request->getLanguages() as $language) {
            $base = strtolower(substr(str_replace('_', '-', $language), 0, 2));

            if ($this->supported($base)) {
                return $base;
            }
        }

        return null;
    }

    protected function defaultLocale(): ?string
    {
        try {
            $default = \App\Models\Setting::get('default_locale');
        } catch (\Throwable $e) {
            // Waehrend Migrationen/ohne Settings-Tabelle: still auf config zurueckfallen.
            return null;
        }

        return $this->supported($default) ? $default : null;
    }

    protected function supported(?string $locale): bool
    {
        $supported = array_keys(config('app.supported_locales', ['en' => 'English', 'de' => 'Deutsch']));

        return $locale !== null && in_array($locale, $supported, true);
    }
}
