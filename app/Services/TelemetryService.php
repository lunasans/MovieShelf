<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Actor;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TelemetryService
{
    /**
     * Collect anonymous statistics about the current installation.
     */
    public function collect(): array
    {
        return [
            'uuid' => $this->getInstallationId(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'app_version' => config('app.version', 'unknown'),
            'movie_count' => Movie::count(),
            'actor_count' => Actor::count(),
            'user_count' => User::count(),
            'os' => PHP_OS,
            'db_driver' => config('database.default'),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get or generate a unique anonymous installation ID.
     */
    protected function getInstallationId(): string
    {
        $id = Setting::get('telemetry_id');

        if (!$id) {
            $id = (string) Str::uuid();
            Setting::set('telemetry_id', $id, 'system');
        }

        return $id;
    }

    /**
     * Send telemetry data to the configured endpoint.
     */
    public function send(): bool
    {
        $enabled = Setting::get('telemetry_enabled', '1') === '1';
        $envEnabled = config('services.telemetry.enabled', env('TELEMETRY_ENABLED', false));

        if (!$enabled || !$envEnabled) {
            return false;
        }

        $endpoint = config('services.telemetry.endpoint', env('TELEMETRY_ENDPOINT'));

        if (!$endpoint) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders(['Accept' => 'application/json'])
                ->post($endpoint, $this->collect());

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Telemetry failed: ' . $e->getMessage());
            return false;
        }
    }
}
