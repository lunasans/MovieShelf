<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWishlist extends Model
{
    protected $table = 'user_wishlist';

    protected $fillable = ['user_id', 'movie_id', 'added_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
