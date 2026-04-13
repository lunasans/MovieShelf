<?php

namespace App\Console\Commands;

use App\Mail\TenantDeletionWarning;
use App\Mail\TenantInactivityWarning;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class WarnInactiveTenants extends Command
{
    protected $signature = 'app:warn-inactive-tenants
                            {--days=30        : Minimum days without login to trigger the warning}
                            {--max-days=      : Optional upper bound (exclusive) in days — tenants inactive for fewer than this many days}
                            {--deletion-warning : Send the deletion warning email instead of the general inactivity email}
                            {--delete-after=60 : Days after which deletion occurs (used to calculate daysUntilDeletion in the email)}';

    protected $description = 'Send inactivity (or deletion) warning emails to tenants that have not logged in for X days';

    public function handle(): void
    {
        $minDays = (int) $this->option('days');
        $maxDays = $this->option('max-days') !== null ? (int) $this->option('max-days') : null;
        $isDeletionWarning = (bool) $this->option('deletion-warning');
        $deleteAfter = (int) $this->option('delete-after');

        $minCutoff = now()->subDays($minDays);

        $query = Tenant::whereNotNull('activated_at')
            ->where(function ($q) use ($minCutoff) {
                $q->whereNull('last_login_at')
                  ->orWhere('last_login_at', '<', $minCutoff);
            });

        // Apply upper bound: exclude tenants that have already been inactive too long
        if ($maxDays !== null) {
            $maxCutoff = now()->subDays($maxDays);
            $query->where(function ($q) use ($maxCutoff) {
                $q->whereNotNull('last_login_at')
                  ->where('last_login_at', '>=', $maxCutoff);
            });
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->info("No tenants found in the {$minDays}" . ($maxDays ? "–{$maxDays}" : '+') . " day range.");
            return;
        }

        foreach ($tenants as $tenant) {
            if (empty($tenant->email)) {
                $this->warn("Skipping tenant {$tenant->id}: no email address.");
                continue;
            }

            $inactiveDays = $tenant->last_login_at
                ? (int) $tenant->last_login_at->diffInDays(now())
                : (int) $tenant->activated_at->diffInDays(now());

            try {
                if ($isDeletionWarning) {
                    $daysUntilDeletion = max(0, $deleteAfter - $inactiveDays);
                    Mail::to($tenant->email)->send(new TenantDeletionWarning($tenant, $inactiveDays, $daysUntilDeletion));
                } else {
                    Mail::to($tenant->email)->send(new TenantInactivityWarning($tenant, $inactiveDays));
                }
                $this->info("Email sent to tenant {$tenant->id} ({$tenant->email}), inactive {$inactiveDays} days.");
            } catch (\Exception $e) {
                $this->error("Failed for tenant {$tenant->id}: {$e->getMessage()}");
            }
        }

        $this->info('Done.');
    }
}
