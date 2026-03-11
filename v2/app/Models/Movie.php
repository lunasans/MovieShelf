<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'year',
        'rating',
        'genre',
        'cover_id',
        'backdrop_id',
        'collection_type',
        'runtime',
        'rating_age',
        'overview',
        'director',
        'trailer_url',
        'boxset_parent',
        'user_id',
        'is_deleted',
        'view_count',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'year' => 'integer',
        'runtime' => 'integer',
        'view_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parentBoxset()
    {
        return $this->belongsTo(Movie::class, 'boxset_parent');
    }

    public function boxsetChildren()
    {
        return $this->hasMany(Movie::class, 'boxset_parent');
    }

    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'film_actor', 'film_id', 'actor_id')
                    ->withPivot(['role', 'is_main_role', 'sort_order']);
    }

    public function seasons()
    {
        return $this->hasMany(Season::class);
    }

    public function watchedByUsers()
    {
        return $this->belongsToMany(User::class, 'movie_user_watched');
    }
}
