<?php

namespace App\Services\Translation;

use App\Models\Setting;
use App\Services\Translation\Drivers\LibreTranslateDriver;

/**
 * Baut den im Cadmin eingestellten Uebersetzungsdienst und reicht Texte durch.
 * Aktuell ist das LibreTranslate — selbst hostbar, dadurch verlassen die Texte
 * den eigenen Server nicht. Weitere Anbieter waeren ein zusaetzlicher Treiber
 * hinter TranslatorDriver.
 */
class TranslatorService
{
    public function isConfigured(): bool
    {
        try {
            return $this->driver() !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Uebersetzt einen Text. Template-Platzhalter ({{ $var }}, {!! !!},
     * <x-mail::…>) werden dabei maskiert und danach unveraendert wieder
     * eingesetzt; geht dabei einer verloren, wird abgebrochen statt
     * beschaedigten Text zurueckzugeben.
     *
     * @throws TranslationException
     */
    public function translate(
        string $text,
        string $target,
        ?string $source = null,
        string $format = 'text',
        bool $protectPlaceholders = true,
    ): string {
        if (trim($text) === '') {
            return '';
        }

        $driver = $this->driver();

        if (! $driver) {
            throw new TranslationException('Es ist kein Übersetzungsdienst konfiguriert.');
        }

        if (! $protectPlaceholders) {
            return $driver->translate($text, $target, $source, $format);
        }

        $guard = new PlaceholderGuard();
        $masked = $guard->mask($text);

        if ($guard->placeholderCount() === 0) {
            return $driver->translate($masked, $target, $source, $format);
        }

        try {
            return $guard->unmask($driver->translate($masked, $target, $source, $format));
        } catch (TranslationException $e) {
            // Der Marker hat die Uebersetzung nicht ueberlebt. Statt hier
            // aufzugeben, werden die Textabschnitte einzeln uebersetzt — so
            // erreichen die Platzhalter den Dienst gar nicht erst und koennen
            // nicht verloren gehen. Der Satzbau leidet, weil dem Modell der
            // Zusammenhang ueber die Platzhalter hinweg fehlt.
            $this->degraded = true;

            return $this->translateInSegments($guard, $text, $target, $source, $format);
        }
    }

    /**
     * Wurde zuletzt abschnittsweise uebersetzt (schlechtere Satzstellung)?
     */
    public function wasDegraded(): bool
    {
        return $this->degraded;
    }

    protected bool $degraded = false;

    /**
     * @throws TranslationException
     */
    protected function translateInSegments(
        PlaceholderGuard $guard,
        string $text,
        string $target,
        ?string $source,
        string $format,
    ): string {
        $driver = $this->driver();
        $out = '';

        foreach ($guard->split($text) as $part) {
            if ($part['type'] === 'placeholder' || trim($part['value']) === '') {
                $out .= $part['value'];

                continue;
            }

            // Fuehrende und schliessende Leerzeichen erhalten: der Dienst
            // schneidet sie ab, und ohne sie kleben Wort und Platzhalter
            // zusammen.
            preg_match('/^(\s*)(.*?)(\s*)$/s', $part['value'], $m);
            $out .= $m[1] . $driver->translate($m[2], $target, $source, $format) . $m[3];
        }

        return $out;
    }

    protected function driver(): ?TranslatorDriver
    {
        if (Setting::get('translator_active', '0') !== '1') {
            return null;
        }

        $endpoint = trim((string) Setting::get('translator_endpoint', ''));

        if ($endpoint === '') {
            return null;
        }

        return new LibreTranslateDriver($endpoint, (string) Setting::get('translator_api_key', ''));
    }
}
