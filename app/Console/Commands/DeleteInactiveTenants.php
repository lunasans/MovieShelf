<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class DeleteInactiveTenants extends Command
{
    protected $signature = 'app:delete-inactive-tenants
                            {--days=60 : Number of days without login before deleting the tenant}';

    protected $description = 'Delete tenants that have not logged in for X days';

    public function handle(): void
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $tenants = Tenant::whereNotNull('activated_at')
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_login_at')
                  ->orWhere('last_login_at', '<', $cutoff);
            })
            ->get();

        if ($tenants->isEmpty()) {
            $this->info("No inactive tenants found (threshold: {$days} days).");
            return;
        }

        foreach ($tenants as $tenant) {
            $inactiveDays = $tenant->last_login_at
                ? (int) $tenant->last_login_at->diffInDays(now())
                : (int) $tenant->activated_at->diffInDays(now());

            $this->info("Deleting inactive tenant: {$tenant->id} (inactive {$inactiveDays} days)");
            $tenant->delete();
        }

        $this->info('Done.');
    }
}
