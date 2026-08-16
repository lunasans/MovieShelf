<?php

namespace App\Services\Translation;

/**
 * Schuetzt Template-Platzhalter vor der Maschinenuebersetzung.
 *
 * Mail-Vorlagen enthalten {{ $seriesTitle }}, {!! !!} und
 * <x-mail::button>. Wuerde der Uebersetzungsdienst daran auch nur die
 * Leerzeichen verschieben, greifen die Regexes in ManagesEmailTemplates nicht
 * mehr — aus einem Button wird stiller Klartext, aus einer URL eine leere
 * Zeichenkette.
 *
 * Die Platzhalter werden deshalb vor der Uebersetzung durch Marker ersetzt und
 * danach zurueckgetauscht. Der Marker ist bewusst ein reines
 * Grossbuchstaben-Token ohne Satzzeichen: Uebersetzungsmodelle reichen
 * unbekannte Woerter meist unveraendert durch, waehrend Klammern als
 * Satzzeichen umsortiert oder reduziert werden ("[[MS0]]" wurde von
 * LibreTranslate zu "[MS0]").
 */
class PlaceholderGuard
{
    /**
     * Reihenfolge zaehlt: die Button-Komponente zuerst, damit die darin
     * enthaltenen {{ }} nicht einzeln maskiert werden.
     */
    public const PATTERNS = [
        '/<x-mail::[a-z-]+.*?<\/x-mail::[a-z-]+>/s',
        '/<x-mail::[a-z-]+[^>]*\/?>/s',
        '/\{!!.*?!!\}/s',
        '/\{\{.*?\}\}/s',
        '/@[a-z]+(\([^)]*\))?/i',
    ];

    /** @var list<string> */
    protected array $stash = [];

    public function mask(string $text): string
    {
        $this->stash = [];

        foreach (self::PATTERNS as $pattern) {
            $text = preg_replace_callback($pattern, function ($m) {
                $this->stash[] = $m[0];

                return $this->marker(count($this->stash) - 1);
            }, $text) ?? $text;
        }

        return $text;
    }

    /**
     * @throws TranslationException wenn ein Marker verloren ging
     */
    public function unmask(string $text): string
    {
        foreach ($this->stash as $index => $original) {
            // Tolerant: Modelle fuegen gelegentlich Leerzeichen ein oder
            // aendern die Gross-/Kleinschreibung. Der Lookahead verhindert,
            // dass Marker 1 den Marker 10 anschneidet.
            $pattern = '/MS\s*PLATZHALTER\s*' . $index . '(?![0-9])/i';

            if (! preg_match($pattern, $text)) {
                throw new TranslationException(
                    'Die Übersetzung hat einen Platzhalter verloren (' . $this->shorten($original) . ').'
                );
            }

            $text = preg_replace($pattern, $this->quote($original), $text, 1) ?? $text;
        }

        return $text;
    }

    /**
     * Zerlegt den Text in Text- und Platzhalter-Abschnitte.
     *
     * Damit lassen sich die Textteile einzeln uebersetzen, waehrend die
     * Platzhalter den Uebersetzer nie erreichen — der Rueckfallweg, wenn ein
     * Marker die Uebersetzung nicht ueberlebt.
     *
     * @return list<array{type: 'text'|'placeholder', value: string}>
     */
    public function split(string $text): array
    {
        $guard = new self();
        $masked = $guard->mask($text);

        $parts = preg_split(
            '/(MSPLATZHALTER[0-9]+)/',
            $masked,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY,
        ) ?: [];

        $out = [];

        foreach ($parts as $part) {
            if (preg_match('/^MSPLATZHALTER([0-9]+)$/', $part, $m)) {
                $out[] = ['type' => 'placeholder', 'value' => $guard->stash[(int) $m[1]]];
            } else {
                $out[] = ['type' => 'text', 'value' => $part];
            }
        }

        return $out;
    }

    public function placeholderCount(): int
    {
        return count($this->stash);
    }

    protected function marker(int $index): string
    {
        return "MSPLATZHALTER{$index}";
    }

    /**
     * preg_replace interpretiert $1 und \1 im Ersetzungstext — in einem
     * Platzhalter wie {{ $seriesTitle }} muss das literal bleiben.
     */
    protected function quote(string $replacement): string
    {
        return str_replace(['\\', '$'], ['\\\\', '\\$'], $replacement);
    }

    protected function shorten(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);

        return mb_strlen($value) > 40 ? mb_substr($value, 0, 40) . '…' : $value;
    }
}
