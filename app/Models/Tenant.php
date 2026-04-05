<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Disable auto-incrementing as we use strings (subdomains) as IDs.
     */
    public $incrementing = false;

    /**
     * Set the primary key type to string.
     */
    protected $keyType = 'string';

    /**
     * Get the tenant's database name.
     * For SQLite, this will be the file name.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'email',
            'activation_token',
            'activated_at',
        ];
    }

    /**
     * Specify the database driver for every tenant.
     */
    public function getDatabaseDriver(): string
    {
        return 'sqlite';
    }
}
