<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendTelemetryJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SendTelemetryJob)->weekly();
Schedule::command('movies:smart-trailer')->dailyAt('03:00');
Schedule::command('app:cleanup-unactivated-tenants')->daily();
// Tag 30–49: allgemeine Inaktivitäts-Warnung
Schedule::command('app:warn-inactive-tenants --days=30 --max-days=50')->weekly();
// Tag 50–59: Lösch-Warnung (noch 10 Tage)
Schedule::command('app:warn-inactive-tenants --days=50 --max-days=60 --deletion-warning --delete-after=60')->weekly();
// Tag 60+: Tenant wird gelöscht
Schedule::command('app:delete-inactive-tenants --days=60')->daily();
