<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'bot_run_id',
        'actor_id',
        'status',
        'message',
    ];

    public function run()
    {
        return $this->belongsTo(BotRun::class, 'bot_run_id');
    }

    public function actor()
    {
        return $this->belongsTo(Actor::class);
    }
}
