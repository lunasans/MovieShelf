<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_not_configured_returns_error()
    {
        $result = (new TranslationService())->translate('Hello');

        $this->assertArrayHasKey('error', $result);
        $this->assertFalse(TranslationService::isConfigured());
    }

    public function test_translates_text_with_target_from_tmdb_language()
    {
        Setting::updateOrCreate(['key' => 'libretranslate_url'], ['value' => 'https://translate.example.com']);
        Setting::updateOrCreate(['key' => 'tmdb_language'], ['value' => 'de-DE']);

        Http::fake([
            'translate.example.com/translate' => Http::response(['translatedText' => 'Hallo Welt'], 200),
        ]);

        $result = (new TranslationService())->translate('Hello World');

        $this->assertEquals('Hallo Welt', $result['text']);
        $this->assertEquals('de', $result['target']);
        $this->assertTrue(TranslationService::isConfigured());
        Http::assertSent(function ($request) {
            return $request['target'] === 'de'
                && $request['source'] === 'auto'
                && $request['q'] === 'Hello World'
                && ! isset($request['api_key']);
        });
    }

    public function test_sends_api_key_when_configured()
    {
        Setting::updateOrCreate(['key' => 'libretranslate_url'], ['value' => 'https://translate.example.com']);
        Setting::updateOrCreate(['key' => 'libretranslate_api_key'], ['value' => 'secret-key']);

        Http::fake([
            'translate.example.com/translate' => Http::response(['translatedText' => 'Hallo'], 200),
        ]);

        (new TranslationService())->translate('Hello');

        Http::assertSent(fn ($request) => $request['api_key'] === 'secret-key');
    }

    public function test_http_error_returns_error()
    {
        Setting::updateOrCreate(['key' => 'libretranslate_url'], ['value' => 'https://translate.example.com']);

        Http::fake([
            'translate.example.com/translate' => Http::response(['error' => 'Invalid API key'], 403),
        ]);

        $result = (new TranslationService())->translate('Hello');

        $this->assertStringContainsString('Invalid API key', $result['error']);
    }
}
