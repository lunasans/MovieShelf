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
        view()->composer(['components.footer', 'layouts.admin', 'layouts.app'], function ($view) {
            $stats = \Illuminate\Support\Facades\Cache::remember('footer_stats', 3600, function () {
                return [
                    'total_films' => \App\Models\Movie::where('is_deleted', false)->count(),
                    'total_actors' => \App\Models\Actor::count(),
                    'total_genres' => \App\Models\Movie::where('is_deleted', false)->distinct('genre')->count('genre'),
                    'total_visits' => 12450, // Placeholder or fetch from settings/logs
                ];
            });
            
            $view->with('footerStats', $stats);
        });
    }
}
