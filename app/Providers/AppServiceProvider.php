<?php

namespace App\Providers;

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
        view()->composer(['components.footer', 'layouts.admin', 'layouts.app', 'dashboard'], function ($view) {
            // Retrieve counters (incrementing is now handled by VisitorCounterMiddleware)
            $totalCounter = \App\Models\Counter::firstOrCreate(['page' => 'all']);
            $today = now()->format('Y-m-d');
            $dailyCounter = \App\Models\Counter::firstOrCreate(['page' => "daily:$today"]);

            $stats = \Illuminate\Support\Facades\Cache::remember('footer_stats', 300, function () use ($totalCounter, $dailyCounter) {
                return [
                    'total_films' => \App\Models\Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')->count(),
                    'total_actors' => \App\Models\Actor::count(),
                    'total_genres' => \App\Models\Movie::where('is_deleted', false)->distinct('genre')->count('genre'),
                    'total_visits' => $totalCounter->visits,
                    'daily_visits' => $dailyCounter->visits,
                ];
            });
            
            // Fallback for safety during cache transitions
            $stats['total_visits'] = $stats['total_visits'] ?? $totalCounter->visits;
            $stats['daily_visits'] = $stats['daily_visits'] ?? $dailyCounter->visits;
            
            $view->with('footerStats', $stats);
        });
    }
}
