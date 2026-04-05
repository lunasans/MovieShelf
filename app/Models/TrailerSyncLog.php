<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrailerSyncLog extends Model
{
    protected $fillable = [
        'run_id',
        'movie_id',
        'movie_title',
        'status',          // found, not_found, error
        'message',
    ];

    public function run()
    {
        return $this->belongsTo(TrailerSyncRun::class, 'run_id');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id');
    }
}
