<?php

namespace App\Http\Middleware;

use App\Models\Counter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Zaehlt echte Seitenaufrufe - einen pro IP und Tag.
 *
 * Frueher wurde jeder GET gezaehlt, der nicht nach Asset aussah und dessen
 * User-Agent keinen von zwoelf bekannten Bot-Namen enthielt. Scanner geben
 * sich aber als Browser aus: An einem Tag entstanden so 3720 "Besucher" aus
 * 6640 Aufrufen von /dashboard durch 1101 verschiedene IP-Adressen.
 *
 * Statt zu erraten, wer kein Besucher ist, wird jetzt geprueft, was einen
 * Besucher ausmacht: eine vom Browser als Seitennavigation angeforderte
 * HTML-Antwort. Das schliesst Hintergrundabfragen per fetch(), JSON-Antworten,
 * Bilder, Weiterleitungen und Fehlerseiten von sich aus aus - ohne Pfadlisten,
 * die bei jeder neuen Route nachgepflegt werden muessten.
 */
class VisitorCounterMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldCount($request, $response)) {
            $this->count($request);
        }

        return $response;
    }

    /**
     * Nur echte, im Browser aufgerufene Seiten zaehlen.
     */
    protected function shouldCount(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        // Nur ausgelieferte Seiten. Weiterleitungen (302 auf /login) und
        // Fehlerseiten sind kein Seitenbesuch.
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        // Bilder, Downloads und JSON tragen text/html nicht - damit fallen
        // der Bild-Proxy, /oauth/userinfo und die Polling-Endpunkte des
        // Admin-Panels weg, ohne sie einzeln aufzaehlen zu muessen.
        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains(strtolower($contentType), 'text/html')) {
            return false;
        }

        return $this->isBrowserNavigation($request);
    }

    /**
     * Hat ein Browser diese Adresse als Seite aufgerufen?
     *
     * Sec-Fetch-Dest schickt jeder Browser seit 2020 unaufgefordert mit und
     * setzt es bei einer Seitennavigation auf "document". Ein per fetch()
     * nachgeladener Inhalt meldet "empty", ein Bild "image". Anders als
     * X-Requested-With - das nur jQuery setzt, fetch() aber nicht - erkennt
     * das auch modernes JavaScript zuverlaessig.
     *
     * Skripte und Scanner setzen den Header gar nicht. Sein Fehlen ist damit
     * das belastbarste Signal, das ohne Pflegeaufwand zu haben ist: Ein
     * gefaelschter User-Agent ist billig, eine vollstaendige Browser-
     * Signatur nicht.
     */
    protected function isBrowserNavigation(Request $request): bool
    {
        $dest = strtolower((string) $request->header('Sec-Fetch-Dest', ''));

        if ($dest !== '') {
            return $dest === 'document';
        }

        // Ohne den Header bleibt nur der User-Agent. Betrifft sehr alte
        // Browser - und praktisch jeden Scanner.
        $userAgent = (string) $request->header('User-Agent', '');

        if ($userAgent === '' || ! str_starts_with($userAgent, 'Mozilla/')) {
            return false;
        }

        return ! preg_match(
            '/bot|crawler|spider|slurp|scan|curl|wget|python|java|go-http|okhttp|headless|lighthouse|preview|monitor/i',
            $userAgent
        );
    }

    /**
     * Einen Besuch verbuchen, sofern die IP heute noch nicht gezaehlt wurde.
     */
    protected function count(Request $request): void
    {
        $today = now()->toDateString();
        $ipHash = hash('sha256', (string) $request->ip());

        if (! $this->claimFirstVisitToday($today, $ipHash)) {
            return;
        }

        foreach (['all', "daily:$today"] as $page) {
            $counter = Counter::firstOrCreate(['page' => $page]);
            $counter->increment('visits');
            $counter->forceFill(['last_visit' => now()])->save();
        }
    }

    /**
     * Traegt die IP fuer heute ein und meldet, ob das der erste Besuch war.
     *
     * Der Unique-Index entscheidet, nicht eine vorherige Abfrage: Zwei
     * gleichzeitige Anfragen derselben IP koennen so nicht beide als neuer
     * Besucher durchgehen.
     */
    protected function claimFirstVisitToday(string $today, string $ipHash): bool
    {
        try {
            $isFirstVisit = DB::table('visitor_hits')->insertOrIgnore([
                'visit_date' => $today,
                'ip_hash'    => $ipHash,
                'created_at' => now(),
            ]) === 1;

            $this->pruneOldHits($today);

            return $isFirstVisit;
        } catch (\Throwable $e) {
            // Lieber gar nicht zaehlen als jeden Aufruf zaehlen: Ohne die
            // Tabelle waere die Eindeutigkeitspruefung wirkungslos, und
            // genau dieser stille Ausfall hat die Zahlen ruiniert.
            report($e);

            return false;
        }
    }

    /**
     * Alte Eintraege entfernen - nur die des laufenden Tages werden gebraucht.
     *
     * Wie Laravels Session-Aufraeumen selten und beilaeufig statt per Cron:
     * Die Tabelle waechst um hoechstens eine Zeile pro Besucher und Tag, ein
     * eigener geplanter Befehl waere dafuer unverhaeltnismaessig. Die 30 Tage
     * Vorlauf lassen Raum, den Verlauf bei Bedarf nachzurechnen.
     */
    protected function pruneOldHits(string $today): void
    {
        if (random_int(1, 500) !== 1) {
            return;
        }

        DB::table('visitor_hits')
            ->where('visit_date', '<', Carbon::parse($today)->subDays(30)->toDateString())
            ->delete();
    }
}
