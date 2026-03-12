<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Movie;
use App\Models\Actor;
use App\Models\Setting;
use App\Models\Counter;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Season;
use App\Models\Episode;
use App\Models\UserRating;
use App\Models\UserWishlist;
use App\Models\UserBackupCode;

use App\Services\MigrationService;

class MigrateV1 extends Command
{
    protected $signature = 'app:migrate-v1 {--fresh : Truncate all tables before migration}';
    protected $description = 'Migrate data from v1.5 MySQL database to v2.0 SQLite database';

    public function handle(MigrationService $migrationService)
    {
        $this->info('Starting migration from v1.5 to v2.0...');

        try {
            $migrationService->migrate($this->option('fresh'), function ($message) {
                $this->line($message);
            });

            $this->info('Migration completed successfully!');
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
