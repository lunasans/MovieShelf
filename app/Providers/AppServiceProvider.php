<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\LandingPage;

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
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Share Footer Statistics
        \Illuminate\Support\Facades\View::composer('components.footer', function ($view) {
            if (function_exists('tenancy') && tenancy()->initialized) {
                $tenantId = tenancy()->tenant->id;
                
                // Cache counts for 10 minutes to save resources
                $counts = \Illuminate\Support\Facades\Cache::remember("footer_counts_{$tenantId}", now()->addMinutes(10), function () {
                    return [
                        'films' => \App\Models\Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')->count(),
                        'actors' => \App\Models\Actor::count(),
                        'genres' => \App\Models\Movie::where('is_deleted', false)
                            ->whereNotNull('genre')
                            ->where('genre', '!=', '')
                            ->pluck('genre')
                            ->flatMap(fn($g) => explode(',', (string)$g))
                            ->map(fn($g) => trim($g))
                            ->filter()
                            ->unique()
                            ->count(),
                    ];
                });

                $today = now()->format('Y-m-d');
                $view->with('footerStats', [
                    'total_films' => $counts['films'],
                    'total_actors' => $counts['actors'],
                    'total_genres' => $counts['genres'],
                    'daily_visits' => \App\Models\Counter::where('page', "daily:{$today}")->value('visits') ?? 0,
                    'total_visits' => \App\Models\Counter::where('page', 'all')->value('visits') ?? 0,
                ]);
            }
        });

        // Share Dynamic Pages with Central Layout
        View::composer('layouts.central', function ($view) {
            // Only fetch if we are on the central domain
            if (!(function_exists('tenancy') && tenancy()->initialized)) {
                $footerPages = Cache::remember('central_footer_pages', now()->addHours(1), function () {
                    return LandingPage::where('is_active', true)
                        ->where('show_in_nav', true)
                        ->orderBy('sort_order')
                        ->get();
                });
                $view->with('footerPages', $footerPages);
            }
        });
    }
}
