<?php

namespace App\Models\Concerns;

use App\Models\ContentTranslation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Macht einzelne Textspalten eines Models mehrsprachig.
 *
 * Das Model listet die Felder in $translatable. Der Wert in der eigenen Spalte
 * ist die Basissprache (siehe config('app.base_content_locale')); abweichende
 * Sprachen liegen in content_translations. Beim Lesen liefert das Model
 * automatisch die Fassung der aktiven Locale und faellt auf die Basisspalte
 * zurueck, wenn keine Uebersetzung gepflegt ist.
 *
 * Im Cadmin muss die Basissprache unveraendert bearbeitbar bleiben — dafuer
 * gibt es translationFor() bzw. Eloquents getRawOriginal().
 */
trait HasTranslations
{
    public function translations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }

    public function translatableFields(): array
    {
        return $this->translatable ?? [];
    }

    public static function baseLocale(): string
    {
        return config('app.base_content_locale', 'de');
    }

    /**
     * Uebersetzter Wert oder null, wenn fuer diese Sprache nichts gepflegt ist.
     */
    public function translationFor(string $field, string $locale): ?string
    {
        if ($locale === static::baseLocale()) {
            return $this->getRawOriginal($field);
        }

        // Ein noch nicht gespeichertes Model hat keine Uebersetzungen; ohne
        // diese Abfrage wuerde das Anlege-Formular je Feld eine Query mit
        // translatable_id = null absetzen.
        if (! $this->exists) {
            return null;
        }

        $match = $this->translations
            ->firstWhere(fn ($t) => $t->locale === $locale && $t->field === $field);

        $value = $match?->value;

        return ($value === null || $value === '') ? null : $value;
    }

    /**
     * Uebersetzung setzen. Ein leerer Wert loescht den Eintrag, damit wieder
     * der Basistext greift statt einer leeren Sektion auf der Seite.
     */
    public function setTranslation(string $field, string $locale, ?string $value): void
    {
        if (! in_array($field, $this->translatableFields(), true)) {
            return;
        }

        if ($locale === static::baseLocale()) {
            $this->{$field} = $value;
            $this->save();

            return;
        }

        if ($value === null || trim($value) === '') {
            $this->translations()->where('locale', $locale)->where('field', $field)->delete();
        } else {
            $this->translations()->updateOrCreate(
                ['locale' => $locale, 'field' => $field],
                ['value' => $value],
            );
        }

        $this->unsetRelation('translations');
    }

    /**
     * Alle uebergebenen Sprachen auf einmal speichern.
     * Erwartet ['en' => ['question' => '...', 'answer' => '...'], ...]
     */
    public function saveTranslations(array $byLocale): void
    {
        foreach ($byLocale as $locale => $fields) {
            if (! array_key_exists($locale, config('app.supported_locales', []))) {
                continue;
            }

            foreach ((array) $fields as $field => $value) {
                $this->setTranslation($field, $locale, $value);
            }
        }
    }

    /**
     * Liest uebersetzte Felder transparent in der aktiven Locale.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (! in_array($key, $this->translatableFields(), true)) {
            return $value;
        }

        $locale = app()->getLocale();

        if ($locale === static::baseLocale()) {
            return $value;
        }

        // relationLoaded pruefen, damit ein Zugriff auf einem frisch
        // erstellten Model keine Query pro Feld ausloest.
        if (! $this->relationLoaded('translations') && ! $this->exists) {
            return $value;
        }

        return $this->translationFor($key, $locale) ?? $value;
    }
}
