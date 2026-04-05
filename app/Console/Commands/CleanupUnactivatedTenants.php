<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupUnactivatedTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-unactivated-tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes tenants that have not been activated within 10 days of registration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiryDate = now()->subDays(10);

        $tenants = \App\Models\Tenant::whereNull('activated_at')
            ->where('created_at', '<', $expiryDate)
            ->get();

        if ($tenants->isEmpty()) {
            $this->info('No unactivated tenants found for cleanup.');
            return;
        }

        foreach ($tenants as $tenant) {
            $this->info("Deleting unactivated tenant: {$tenant->id} (Created at: {$tenant->created_at})");
            
            // This will delete the tenant record, the associated domains, and the database file
            $tenant->delete();
        }

        $this->info('Cleanup completed.');
    }
}
