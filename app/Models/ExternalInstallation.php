<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalInstallation extends Model
{
    protected $fillable = [
        'uuid',
        'php_version',
        'laravel_version',
        'app_version',
        'movie_count',
        'actor_count',
        'user_count',
        'os',
        'db_driver',
        'last_seen_at',
        'extra_data',
    ];

    protected $casts = [
        'extra_data' => 'array',
        'last_seen_at' => 'datetime',
    ];
}
