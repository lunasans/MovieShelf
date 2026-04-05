<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrailerSyncRun extends Model
{
    protected $fillable = [
        'status',          // running, success, error
        'total_movies',
        'updated_movies',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(TrailerSyncLog::class, 'run_id');
    }
}
