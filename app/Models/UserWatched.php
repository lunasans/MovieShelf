<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWatched extends Model
{
    protected $table = 'user_watched';
    protected $fillable = ['user_id', 'movie_id', 'watched_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
