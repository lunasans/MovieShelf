<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Übersetzungen via LibreTranslate (selbst gehostet oder öffentliche Instanz).
 * URL und optionaler API-Key werden im Admin-Panel konfiguriert.
 */
class TranslationService
{
    public static function isConfigured(): bool
    {
        return trim(Setting::get('libretranslate_url', '')) !== '';
    }

    /**
     * Übersetzt Text (HTML erlaubt). Ziel-Sprache default: die TMDb-Metadaten-Sprache.
     *
     * @return array{text?: string, target?: string, error?: string}
     */
    public function translate(string $text, ?string $target = null, string $source = 'auto'): array
    {
        $baseUrl = rtrim(trim(Setting::get('libretranslate_url', '')), '/');
        if ($baseUrl === '') {
            return ['error' => 'LibreTranslate ist nicht konfiguriert.'];
        }

        $target = $target ?: substr(Setting::get('tmdb_language', 'de-DE') ?: 'de-DE', 0, 2);

        $payload = [
            'q' => $text,
            'source' => $source,
            'target' => $target,
            'format' => 'html',
        ];

        $apiKey = trim(Setting::get('libretranslate_api_key', ''));
        if ($apiKey !== '') {
            $payload['api_key'] = $apiKey;
        }

        try {
            $response = Http::timeout(60)->withOptions([
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])->asJson()->post($baseUrl.'/translate', $payload);

            if (! $response->successful()) {
                $message = $response->json('error') ?: 'HTTP '.$response->status();

                return ['error' => 'Übersetzung fehlgeschlagen: '.$message];
            }

            $translated = $response->json('translatedText');
            if (! is_string($translated) || trim($translated) === '') {
                return ['error' => 'Übersetzung fehlgeschlagen: leere Antwort.'];
            }

            return ['text' => $translated, 'target' => $target];
        } catch (\Exception $e) {
            Log::error('LibreTranslate Error: '.$e->getMessage());

            return ['error' => 'Verbindung zu LibreTranslate fehlgeschlagen.'];
        }
    }
}
