<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $table = 'episodes';

    use HasFactory;

    protected $fillable = [
        'season_id',
        'episode_number',
        'title',
        'overview',
        'runtime',
        'air_date',
    ];

    protected $casts = [
        'air_date' => 'date',
        'runtime' => 'integer',
    ];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function watchedByUsers()
    {
        return $this->belongsToMany(User::class, 'episode_user_watched')->withPivot('watched_at');
    }
}
