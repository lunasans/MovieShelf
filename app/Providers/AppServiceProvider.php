<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // TOTAL SAFETY WRAPPER: During boot, we MUST NOT crash because of database issues.
        // This is necessary to allow 'php artisan migrate' to run even if the DB is missing or invalid.
        try {
            $this->bootDatabaseSettings();
        } catch (\Throwable $e) {
            // Silently ignore all DB errors during boot
        }

        try {
            $this->bootViewComposers();
        } catch (\Throwable $e) {
            // Silently ignore all view errors during boot
        }
    }

    /**
     * Boot database-specific settings with extreme safety.
     */
    protected function bootDatabaseSettings(): void
    {
        $connection = config('database.default');
        $centralDbPath = config("database.connections.{$connection}.database");
        
        // Failsafe: If no path or SQLite file missing/empty, we skip everything
        if (!$centralDbPath || (config("database.connections.{$connection}.driver") === 'sqlite' && 
            $centralDbPath !== ':memory:' && 
            (!file_exists($centralDbPath) || filesize($centralDbPath) === 0))) {
            return;
        }

        // Secondary safety: Wrap the schema check itself
        try {
            $hasSettings = \Illuminate\Support\Facades\Schema::connection('central')->hasTable('settings');
        } catch (\Throwable $e) {
            return;
        }

        if (class_exists(\App\Models\Setting::class) && $hasSettings) {
            \Illuminate\Support\Facades\Config::set([
                'mail.mailers.smtp.host' => \App\Models\Setting::on('central')->get('mail_host', config('mail.mailers.smtp.host')),
                'mail.mailers.smtp.port' => \App\Models\Setting::on('central')->get('mail_port', config('mail.mailers.smtp.port')),
                'mail.mailers.smtp.username' => \App\Models\Setting::on('central')->get('mail_username', config('mail.mailers.smtp.username')),
                'mail.mailers.smtp.password' => \App\Models\Setting::on('central')->get('mail_password', config('mail.mailers.smtp.password')),
                'mail.mailers.smtp.encryption' => \App\Models\Setting::on('central')->get('mail_encryption', config('mail.mailers.smtp.encryption')),
                'mail.from.address' => \App\Models\Setting::on('central')->get('mail_from_address', config('mail.from.address')),
                'mail.from.name' => \App\Models\Setting::on('central')->get('mail_from_name', config('mail.from.name')),
            ]);
        }
    }

    /**
     * Boot view composers with safety.
     */
    protected function bootViewComposers(): void
    {
        view()->composer(['components.footer', 'layouts.admin', 'layouts.app', 'dashboard'], function ($view) {
            // Only execute tenant-specific logic if tenancy is initialized and NOT a central domain
            if (!function_exists('tenancy') || !tenancy()->initialized || in_array(request()->getHost(), config('tenancy.central_domains'))) {
                return;
            }

            // Retrieve counters (incrementing is now handled by VisitorCounterMiddleware)
            $totalCounter = \App\Models\Counter::firstOrCreate(['page' => 'all']);
            $today = now()->format('Y-m-d');
            $dailyCounter = \App\Models\Counter::firstOrCreate(['page' => "daily:$today"]);

            $stats = Cache::remember('footer_stats', 300, function () use ($totalCounter, $dailyCounter) {
                return [
                    'total_films' => \App\Models\Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')->count(),
                    'total_actors' => \App\Models\Actor::count(),
                    'total_genres' => \App\Models\Movie::where('is_deleted', false)
                        ->whereDoesntHave('boxsetChildren')
                        ->whereNotNull('genre')
                        ->get(['genre'])
                        ->flatMap(fn($movie) => array_map('trim', explode(',', $movie->genre)))
                        ->filter()
                        ->unique()
                        ->count(),
                    'total_visits' => $totalCounter->visits ?? 0,
                    'daily_visits' => $dailyCounter->visits ?? 0,
                ];
            });

            // Fallback for safety during cache transitions
            $stats['total_visits'] = $stats['total_visits'] ?? ($totalCounter->visits ?? 0);
            $stats['daily_visits'] = $stats['daily_visits'] ?? ($dailyCounter->visits ?? 0);

            $view->with('footerStats', $stats);
        });
    }
}
