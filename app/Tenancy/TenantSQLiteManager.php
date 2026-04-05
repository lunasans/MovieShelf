<?php

namespace App\Tenancy;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\TenantDatabaseManagers\SQLiteDatabaseManager;

class TenantSQLiteManager extends SQLiteDatabaseManager
{
    /**
     * Resolve the database path for a tenant.
     *
     * @param string $databaseName
     * @return string
     */
    protected function getTenantDatabasePath(string $databaseName): string
    {
        // $databaseName matches the pattern prefix + tenant_id + suffix
        // We want to extract the tenant_id to find the correct storage folder.
        $tenantId = str_replace(['tenant', '.sqlite'], '', $databaseName);
        
        $path = storage_path("tenant{$tenantId}");

        if (! file_exists($path)) {
            mkdir($path, 0777, true);
        }

        return $path . DIRECTORY_SEPARATOR . 'database.sqlite';
    }

    public function databaseExists(string $name): bool
    {
        return file_exists($this->getTenantDatabasePath($name));
    }

    public function makeConnectionConfig(array $baseConfig, string $databaseName): array
    {
        $baseConfig['database'] = $this->getTenantDatabasePath($databaseName);

        return $baseConfig;
    }

    public function deleteDatabase(TenantWithDatabase $tenant): bool
    {
        $path = $this->getTenantDatabasePath($tenant->database()->getName());

        return file_exists($path) ? unlink($path) : true;
    }
}
