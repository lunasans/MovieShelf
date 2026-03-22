<?php

namespace App\Console\Commands;

use App\Services\MigrationService;
use Illuminate\Console\Command;

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
            $this->error('Migration failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
