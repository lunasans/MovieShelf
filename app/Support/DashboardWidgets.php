<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Registry der Kacheln des Admin-Dashboards.
 *
 * Jede Kachel ist ein Blade-Partial unter admin/dashboard/widgets/<key>.blade.php.
 * Position und Groesse im 12-Spalten-Raster kommen aus dem Tenant-Setting
 * dashboard_layout (pro Regal, im Dashboard selbst per Drag & Drop gepflegt);
 * fehlt ein Eintrag, greift der Default aus dieser Registry.
 */
class DashboardWidgets
{
    public const SETTING_KEY = 'dashboard_layout';

    /** Spaltenzahl des Rasters – muss zum gs-column-Wert im Blade passen. */
    public const COLUMNS = 12;

    /**
     * Standard-Anordnung: entspricht dem Layout vor der Umstellung.
     *
     * @return array<string, array{label: string, icon: string, x: int, y: int, w: int, h: int}>
     */
    public static function all(): array
    {
        return [
            // 'bare' = eigene Kachelreihe ohne die gemeinsame Glas-Huelle
            'kpis' => [
                'label' => 'Kennzahlen', 'icon' => 'bi-speedometer2', 'bare' => true,
                'x' => 0, 'y' => 0, 'w' => 12, 'h' => 3,
            ],
            'quick-actions' => [
                'label' => 'Schnellzugriff', 'icon' => 'bi-lightning', 'bare' => true,
                'x' => 0, 'y' => 3, 'w' => 12, 'h' => 2,
            ],
            'genres' => [
                'label' => 'Genre-Verteilung', 'icon' => 'bi-tags-fill',
                'x' => 0, 'y' => 5, 'w' => 8, 'h' => 6,
            ],
            'data-quality' => [
                'label' => 'Datenqualität', 'icon' => 'bi-shield-check',
                'x' => 8, 'y' => 5, 'w' => 4, 'h' => 6,
            ],
            'collection-types' => [
                'label' => 'Kollektion', 'icon' => 'bi-collection',
                'x' => 0, 'y' => 11, 'w' => 4, 'h' => 6,
            ],
            'top-actors' => [
                'label' => 'Top Schauspieler', 'icon' => 'bi-star-fill',
                'x' => 4, 'y' => 11, 'w' => 4, 'h' => 6,
            ],
            'activity' => [
                'label' => 'Aktivität', 'icon' => 'bi-lightning-charge-fill',
                'x' => 8, 'y' => 11, 'w' => 4, 'h' => 7,
            ],
            'latest-movies' => [
                'label' => 'Zuletzt hinzugefügt', 'icon' => 'bi-clock',
                'x' => 0, 'y' => 17, 'w' => 8, 'h' => 6,
            ],
        ];
    }

    /**
     * Anordnung des aktuellen Regals: Defaults, ueberschrieben vom gespeicherten
     * Layout. Unbekannte Keys aus dem Setting werden ignoriert (z.B. nach dem
     * Entfernen einer Kachel in einem Update), neue Kacheln erscheinen mit ihrem
     * Default.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function layout(): array
    {
        $saved = json_decode((string) Setting::get(self::SETTING_KEY, ''), true);
        $saved = is_array($saved) ? $saved : [];

        $layout = [];

        foreach (self::all() as $key => $widget) {
            $entry = $saved[$key] ?? [];

            $layout[$key] = [
                'label'   => $widget['label'],
                'icon'    => $widget['icon'],
                'bare'    => $widget['bare'] ?? false,
                'x'       => (int) ($entry['x'] ?? $widget['x']),
                'y'       => (int) ($entry['y'] ?? $widget['y']),
                'w'       => max(1, min(self::COLUMNS, (int) ($entry['w'] ?? $widget['w']))),
                'h'       => max(1, (int) ($entry['h'] ?? $widget['h'])),
                'visible' => (bool) ($entry['visible'] ?? true),
            ];
        }

        return $layout;
    }

    /**
     * Speichert die vom Dashboard gemeldete Anordnung. Es werden nur bekannte
     * Kacheln uebernommen.
     */
    public static function save(array $incoming): void
    {
        $known = self::all();
        $layout = [];

        foreach ($incoming as $key => $entry) {
            if (! isset($known[$key]) || ! is_array($entry)) {
                continue;
            }

            $layout[$key] = [
                'x'       => max(0, (int) ($entry['x'] ?? 0)),
                'y'       => max(0, (int) ($entry['y'] ?? 0)),
                'w'       => max(1, min(self::COLUMNS, (int) ($entry['w'] ?? 1))),
                'h'       => max(1, (int) ($entry['h'] ?? 1)),
                'visible' => (bool) ($entry['visible'] ?? true),
            ];
        }

        Setting::set(self::SETTING_KEY, json_encode($layout), 'dashboard');
    }

    public static function reset(): void
    {
        Setting::set(self::SETTING_KEY, '', 'dashboard');
    }
}
