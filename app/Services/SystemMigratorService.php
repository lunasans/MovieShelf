<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Counter;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemMigratorService
{
    protected $callback;
    protected $connection;

    public function __construct($callback, $connection)
    {
        $this->callback = $callback;
        $this->connection = $connection;
    }

    protected function log($message)
    {
        if ($this->callback) {
            call_user_func($this->callback, $message);
        }
    }

    protected function tableExists($tableName)
    {
        return Schema::connection($this->connection)->hasTable($tableName);
    }

    public function migrateSettings()
    {
        if (! $this->tableExists('settings')) {
            $this->log('Tabelle "settings" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Einstellungen...');
        $oldSettings = DB::connection($this->connection)->table('settings')->get();
        foreach ($oldSettings as $oldSetting) {
            try {
                Setting::updateOrCreate(
                    ['key' => $oldSetting->key],
                    [
                        'value' => $oldSetting->value,
                        'group' => property_exists($oldSetting, 'group') ? $oldSetting->group : 'general',
                    ]
                );
            } catch (\Exception $e) {
                $this->log("Fehler beim Migrieren von Einstellung {$oldSetting->key}: ".$e->getMessage());
            }
        }
    }

    public function migrateCounter()
    {
        if (! $this->tableExists('counter')) {
            $this->log('Tabelle "counter" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Counter...');
        try {
            $oldCounter = DB::connection($this->connection)->table('counter')->first();
            if ($oldCounter) {
                $this->migrateTotalVisits($oldCounter);
                $this->migrateDailyVisits($oldCounter);
            }
        } catch (\Exception $e) {
            $this->log('Fehler beim Migrieren des Counters: '.$e->getMessage());
        }
    }

    protected function migrateTotalVisits($oldCounter)
    {
        $lastVisit = property_exists($oldCounter, 'last_visit_date') ? $oldCounter->last_visit_date : ($oldCounter->last_visit ?? null);
        Counter::updateOrCreate(['page' => 'all'], [
            'visits' => $oldCounter->visits,
            'last_visit' => $lastVisit,
            'created_at' => $oldCounter->created_at,
            'updated_at' => $oldCounter->updated_at,
        ]);
    }

    protected function migrateDailyVisits($oldCounter)
    {
        if (property_exists($oldCounter, 'daily_visits') && property_exists($oldCounter, 'last_visit_date') && $oldCounter->last_visit_date) {
            $date = $oldCounter->last_visit_date;
            Counter::updateOrCreate(['page' => "daily:$date"], [
                'visits' => $oldCounter->daily_visits,
                'last_visit' => $date.' 23:59:59',
                'created_at' => $oldCounter->updated_at,
                'updated_at' => $oldCounter->updated_at,
            ]);
        }
    }

    public function migrateLogs()
    {
        $this->migrateActivityLogs();
        $this->migrateAuditLogs();
    }

    protected function migrateActivityLogs()
    {
        if (! $this->tableExists('activity_log')) {
            $this->log('Tabelle "activity_log" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Activity Logs...');
        $total = DB::connection($this->connection)->table('activity_log')->count();
        $count = 0;
        DB::connection($this->connection)->table('activity_log')->orderBy('id')->chunk(500, function ($oldLogs) use (&$count, $total) {
            foreach ($oldLogs as $log) {
                try {
                    ActivityLog::updateOrCreate(['id' => $log->id], [
                        'user_id' => $log->user_id,
                        'action' => $log->action,
                        'details' => $log->details,
                        'ip_address' => $log->ip_address,
                        'user_agent' => $log->user_agent,
                        'created_at' => $log->created_at,
                    ]);
                } catch (\Exception $e) {}
                $count++;
            }
            if ($count % 1000 == 0) $this->log("Fortschritt: {$count}/{$total} Activity-Logs migriert.");
        });
    }

    protected function migrateAuditLogs()
    {
        if (! $this->tableExists('audit_log')) {
            $this->log('Tabelle "audit_log" nicht gefunden, Überspringe...');
            return;
        }

        $this->log('Migriere Audit Logs...');
        $totalAudit = DB::connection($this->connection)->table('audit_log')->count();
        $countAudit = 0;
        DB::connection($this->connection)->table('audit_log')->orderBy('id')->chunk(500, function ($oldAudit) use (&$countAudit, $totalAudit) {
            foreach ($oldAudit as $log) {
                try {
                    AuditLog::updateOrCreate(['id' => $log->id], [
                        'user_id' => $log->user_id,
                        'action' => $log->action,
                        'ip_address' => $log->ip_address,
                        'user_agent' => $log->user_agent,
                        'created_at' => $log->created_at,
                    ]);
                } catch (\Exception $e) {}
                $countAudit++;
            }
            if ($countAudit % 1000 == 0) $this->log("Fortschritt: {$countAudit}/{$totalAudit} Audit-Logs migriert.");
        });
    }
}
