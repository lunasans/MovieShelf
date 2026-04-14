<?php

namespace App\Tenancy;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class S3TenancyBootstrapper implements TenancyBootstrapper
{
    /** @var Application */
    protected $app;

    /** @var array */
    protected $originalConfig = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function bootstrap(Tenant $tenant)
    {
        // Store original config to restore it during revert()
        $this->originalConfig['s3_root'] = Config::get('filesystems.disks.s3.root');
        $this->originalConfig['s3_url'] = Config::get('filesystems.disks.s3.url');
        $this->originalConfig['r2_root'] = Config::get('filesystems.disks.r2.root');
        $this->originalConfig['r2_url'] = Config::get('filesystems.disks.r2.url');

        $tenantId = $tenant->getTenantKey();
        $baseMediaUrl = env('S3_URL', 'https://medien.movieshelf.info');

        // Set the root (folder prefix) inside the bucket
        Config::set('filesystems.disks.s3.root', $tenantId);
        Config::set('filesystems.disks.r2.root', $tenantId);

        // Update the public URL to include the tenant folder
        Config::set('filesystems.disks.s3.url', $baseMediaUrl . '/' . $tenantId);
        Config::set('filesystems.disks.r2.url', $baseMediaUrl . '/' . $tenantId);
    }

    public function revert()
    {
        Config::set('filesystems.disks.s3.root', $this->originalConfig['s3_root']);
        Config::set('filesystems.disks.s3.url', $this->originalConfig['s3_url']);
        Config::set('filesystems.disks.r2.root', $this->originalConfig['r2_root']);
        Config::set('filesystems.disks.r2.url', $this->originalConfig['r2_url']);
    }
}
