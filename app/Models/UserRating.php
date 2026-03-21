<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRating extends Model
{
    protected $table = 'user_ratings';

    protected $fillable = ['user_id', 'movie_id', 'rating', 'comment'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
