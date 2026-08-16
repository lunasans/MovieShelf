<?php

namespace App\Console\Commands;

use App\Services\Translation\TranslationException;
use App\Services\Translation\TranslatorService;
use Illuminate\Console\Command;

/**
 * Fuellt fehlende Schluessel in lang/<locale>.json aus den englischen
 * Quell-Keys. Vorhandene Uebersetzungen bleiben unangetastet — der Befehl
 * ergaenzt nur, was noch fehlt.
 */
class TranslateLangFile extends Command
{
    protected $signature = 'lang:translate
                            {locale : Zielsprache, z.B. de}
                            {--dry-run : Nur anzeigen, nichts schreiben}
                            {--limit=0 : Hoechstens so viele Schluessel uebersetzen (0 = alle)}';

    protected $description = 'Übersetzt fehlende Schlüssel einer Sprachdatei über den eingestellten Übersetzungsdienst';

    public function handle(TranslatorService $translator): int
    {
        $locale = $this->argument('locale');

        if (! array_key_exists($locale, config('app.supported_locales', []))) {
            $this->error("Die Sprache '{$locale}' steht nicht in config/app.php unter supported_locales.");

            return self::FAILURE;
        }

        if (! $translator->isConfigured()) {
            $this->error('Es ist kein Übersetzungsdienst konfiguriert (Cadmin → Einstellungen → Übersetzung).');

            return self::FAILURE;
        }

        $targetPath = lang_path("{$locale}.json");
        $sourcePath = lang_path('en.json');

        if (! file_exists($sourcePath)) {
            $this->error('lang/en.json fehlt — daraus werden die Schlüssel gelesen.');

            return self::FAILURE;
        }

        $source = json_decode(file_get_contents($sourcePath), true) ?: [];
        $target = file_exists($targetPath)
            ? (json_decode(file_get_contents($targetPath), true) ?: [])
            : [];

        $missing = array_keys(array_diff_key($source, $target));

        if ($missing === []) {
            $this->info("lang/{$locale}.json ist vollständig — nichts zu tun.");

            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $missing = array_slice($missing, 0, $limit);
        }

        $this->info(count($missing) . " fehlende Schlüssel werden übersetzt (en → {$locale}).");
        $bar = $this->output->createProgressBar(count($missing));
        $bar->start();

        $failed = [];

        foreach ($missing as $key) {
            // Die Schluessel sind der englische Quelltext; HTML-Fragmente
            // (z.B. mit <span>) muessen als HTML uebersetzt werden, damit die
            // Tags erhalten bleiben.
            $format = str_contains($key, '<') ? 'html' : 'text';

            try {
                $target[$key] = $translator->translate($key, $locale, 'en', $format);
            } catch (TranslationException $e) {
                $failed[$key] = $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($failed !== []) {
            $this->warn(count($failed) . ' Schlüssel konnten nicht übersetzt werden:');
            foreach (array_slice($failed, 0, 5, true) as $key => $message) {
                $this->line("  · {$key} — {$message}");
            }
        }

        if ($this->option('dry-run')) {
            $this->comment('Trockenlauf — es wurde nichts geschrieben.');

            return self::SUCCESS;
        }

        ksort($target);
        file_put_contents(
            $targetPath,
            json_encode($target, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n",
        );

        $this->info("lang/{$locale}.json aktualisiert (" . count($target) . ' Einträge).');
        $this->comment('Maschinelle Übersetzungen bitte gegenlesen, bevor sie live gehen.');

        return self::SUCCESS;
    }
}
