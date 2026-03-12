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
            // Increment total visits (using 'all' as the page key for site-wide counter)
            $counter = \App\Models\Counter::firstOrCreate(['page' => 'all']);
            $counter->increment('visits');
            $counter->last_visit = now();
            $counter->save();

            $stats = \Illuminate\Support\Facades\Cache::remember('footer_stats', 300, function () use ($counter) {
                return [
                    'total_films' => \App\Models\Movie::where('is_deleted', false)->count(),
                    'total_actors' => \App\Models\Actor::count(),
                    'total_genres' => \App\Models\Movie::where('is_deleted', false)->distinct('genre')->count('genre'),
                    'total_visits' => $counter->visits,
                ];
            });
            
            $view->with('footerStats', $stats);
        });
    }
}
