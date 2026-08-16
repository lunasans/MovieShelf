<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Heartbeat: Bei jedem schedule:run (per Cron jede Minute) wird ein Zeitstempel gespeichert.
// Das Admin-Panel erkennt daran, ob der System-Cron überhaupt läuft.
Schedule::call(function () {
    \App\Models\Setting::set('scheduler_last_run_at', now()->toDateTimeString(), 'system');
})->everyMinute()->name('scheduler-heartbeat');

Schedule::command('movies:smart-trailer')->dailyAt('03:00');

// Follower per Mail informieren, wenn eine bereits vorhandene Staffel neue
// Episoden hat (importiert nichts - keine neuen Staffeln, keine DB-Änderung)
Schedule::command('series:sync')->dailyAt('04:00');

// Gesperrte Filme endgueltig entfernen. Monatlich reicht: die Frist selbst
// (Vorgabe 90 Tage) bestimmt, wann geloescht wird — nicht der Takt.
Schedule::command('movies:purge')
    ->monthlyOn(1, '01:30')
    ->withoutOverlapping()
    ->runInBackground();
