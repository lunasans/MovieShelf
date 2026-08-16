<?php

namespace App\Services\Translation\Drivers;

use App\Services\Translation\TranslationException;
use App\Services\Translation\TranslatorDriver;
use Illuminate\Support\Facades\Http;

/**
 * LibreTranslate — selbst hostbar, dadurch verlassen die Texte den eigenen
 * Server nicht. Endpunkt ist Pflicht, der API-Key je nach Instanz optional.
 */
class LibreTranslateDriver implements TranslatorDriver
{
    public function __construct(
        protected string $endpoint,
        protected string $apiKey = '',
    ) {
    }

    public function translate(string $text, string $target, ?string $source, string $format): string
    {
        if (trim($this->endpoint) === '') {
            throw new TranslationException('Für LibreTranslate fehlt die Server-Adresse.');
        }

        $response = Http::timeout(30)->asJson()->post(rtrim($this->endpoint, '/') . '/translate', array_filter([
            'q' => $text,
            'source' => $source ?: 'auto',
            'target' => strtolower($target),
            'format' => $format === 'html' ? 'html' : 'text',
            'api_key' => $this->apiKey !== '' ? $this->apiKey : null,
        ], fn ($v) => $v !== null));

        if ($response->failed()) {
            $detail = $response->json('error') ?? mb_substr($response->body(), 0, 300);
            throw new TranslationException("LibreTranslate-Fehler {$response->status()}: {$detail}");
        }

        $translated = $response->json('translatedText');

        if (! is_string($translated)) {
            throw new TranslationException('LibreTranslate lieferte keine Übersetzung zurück.');
        }

        return $translated;
    }
}
