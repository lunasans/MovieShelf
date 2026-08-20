<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        // Polymorphe Listen-Items: kurze, stabile Typ-Namen statt voller Klassennamen,
        // damit `list_items.item_type` 'movie'/'wishlist' enthält (siehe Listen-Pivot).
        // enforceMorphMap verlangt einen Eintrag für JEDES polymorph genutzte Model —
        // auch User wegen Sanctums tokenable (personal_access_tokens); ohne diesen
        // Eintrag wirft createToken() beim API-Login eine ClassMorphViolationException.
        \Illuminate\Database\Eloquent\Relations\Relation::enforceMorphMap([
            'movie'    => \App\Models\Movie::class,
            'external' => \App\Models\ExternalMovie::class,
            'user'     => \App\Models\User::class,
            // Uebersetzbare Inhalte (content_translations.translatable_type).
            // Die Cloud fuehrt hier zusaetzlich faq/page/screenshot/desktop_release —
            // die Models gibt es in der Standalone-Variante nicht.
            'mail_template' => \App\Models\EmailTemplate::class,
        ]);

        // Nicht an APP_ENV koppeln: eine Produktivinstanz, die per HTTP
        // erreichbar ist (Docker, LAN, Reverse-Proxy ohne TLS), bekaeme sonst
        // https-URLs fuer CSS, JS und Bilder - das HTML laedt, jedes Asset
        // scheitert am TLS-Handshake. Massgeblich ist die konfigurierte
        // Adresse. Hinter einem TLS-Proxy leitet Laravel das Schema ohnehin
        // aus X-Forwarded-Proto ab, siehe TRUSTED_PROXIES.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Share Footer Statistics
        \Illuminate\Support\Facades\View::composer('components.footer', function ($view) {
            // Cache counts for 10 minutes to save resources
            $counts = \Illuminate\Support\Facades\Cache::remember('footer_counts_v2', now()->addMinutes(10), function () {
                return [
                    'films' => \App\Models\Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')->moviesOnly()->count(),
                    'series' => \App\Models\Movie::where('is_deleted', false)->whereDoesntHave('boxsetChildren')->seriesOnly()->count(),
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
                'total_series' => $counts['series'] ?? 0,
                'total_actors' => $counts['actors'],
                'total_genres' => $counts['genres'],
                'daily_visits' => \App\Models\Counter::where('page', "daily:{$today}")->value('visits') ?? 0,
                'total_visits' => \App\Models\Counter::where('page', 'all')->value('visits') ?? 0,
            ]);
        });

        // Share global announcement with all app views
        View::composer('layouts.app', function ($view) {
            $announcement = ['active' => false, 'text' => '', 'type' => 'info'];
            $file = storage_path('app/announcement.json');
            if (file_exists($file)) {
                $decoded = json_decode(file_get_contents($file), true);
                if (is_array($decoded)) {
                    $announcement = $decoded;
                }
            }
            $view->with('globalAnnouncement', $announcement);
        });

        // Onboarding-Checkliste im Regal – nur fuer eingeloggte Admins,
        // Schritte werden live aus dem echten Zustand der Sammlung berechnet.
        View::composer('tenant.dashboard', function ($view) {
            $onboarding = null;

            if (auth()->check() && auth()->user()->is_admin) {
                $tmdbSet = trim((string) \App\Models\Setting::get('tmdb_api_key', '')) !== '';
                $collectionCount = \App\Models\Movie::where('in_collection', true)->count();

                $steps = [
                    [
                        'label' => 'Eigenen TMDb-Key hinterlegen',
                        'desc'  => 'Damit Filme automatisch mit Cover, Beschreibung und Cast importiert werden.',
                        'done'  => $tmdbSet,
                        'url'   => route('admin.settings.index'),
                        'cta'   => 'Zu den Einstellungen',
                        'icon'  => 'bi-key-fill',
                    ],
                    [
                        'label' => 'Ersten Film hinzufügen',
                        'desc'  => 'Suche einen Titel über TMDb und importiere ihn in dein Regal.',
                        'done'  => $collectionCount >= 1,
                        'url'   => route('admin.tmdb.index'),
                        'cta'   => 'Film importieren',
                        'icon'  => 'bi-film',
                    ],
                    [
                        'label' => 'Regal füllen (5 Filme)',
                        'desc'  => 'Bring dein Regal mit ein paar Titeln zum Leben.',
                        'done'  => $collectionCount >= 5,
                        'url'   => route('admin.tmdb.index'),
                        'cta'   => 'Mehr importieren',
                        'icon'  => 'bi-collection-fill',
                    ],
                ];

                $doneCount = collect($steps)->where('done', true)->count();
                $total = count($steps);

                $onboarding = [
                    'steps'     => $steps,
                    'done'      => $doneCount,
                    'total'     => $total,
                    'percent'   => (int) round($doneCount * 100 / $total),
                    'complete'  => $doneCount === $total,
                    'dismissed' => \App\Models\Setting::get('onboarding_dismissed', '0') === '1',
                ];
            }

            $view->with('onboarding', $onboarding);
        });
    }
}
