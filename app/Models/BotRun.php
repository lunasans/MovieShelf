<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'total_actors',
        'processed_actors',
        'last_actor_id',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(BotLog::class);
    }
}
