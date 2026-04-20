<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieList extends Model
{
    protected $table = 'lists';

    protected $fillable = ['user_id', 'name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'list_movies', 'list_id', 'movie_id')
            ->withPivot('added_at')
            ->orderBy('movies.title');
    }
}
