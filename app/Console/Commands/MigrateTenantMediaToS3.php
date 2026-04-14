<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Concerns\HasATenantsOption;

class MigrateTenantMediaToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate-to-s3 {tenant? : The ID of the tenant to migrate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate local media files to Cloudflare R2/S3 for one or all tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant');

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if (! $tenant) {
                $this->error("Tenant with ID {$tenantId} not found.");
                return 1;
            }
            $this->migrateTenant($tenant);
        } else {
            $this->info("Migrating media for ALL tenants...");
            Tenant::all()->each(fn ($tenant) => $this->migrateTenant($tenant));
        }

        $this->info('Migration completed!');
        return 0;
    }

    protected function migrateTenant(Tenant $tenant)
    {
        $this->info("Processing Tenant: {$tenant->id}");

        tenancy()->initialize($tenant);

        $localDisk = Storage::disk('public');
        $s3Disk = Storage::disk('s3');

        $folders = ['covers', 'backdrops', 'actors'];

        foreach ($folders as $folder) {
            if (! $localDisk->exists($folder)) {
                continue;
            }

            $files = $localDisk->allFiles($folder);
            $this->info("  - Found " . count($files) . " files in '{$folder}'");

            foreach ($files as $file) {
                if ($s3Disk->exists($file)) {
                    $this->line("    - Skipping {$file} (already on S3)");
                    continue;
                }

                $this->line("    - Uploading {$file}...");
                $stream = $localDisk->readStream($file);
                $s3Disk->put($file, $stream);
                
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }

        tenancy()->end();
    }
}
