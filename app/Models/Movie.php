<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
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
        'tmdb_id',
        'tmdb_type',
        'tmdb_json',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'year' => 'integer',
        'runtime' => 'integer',
        'view_count' => 'integer',
        'tmdb_json' => 'array',
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

    public function getCoverUrlAttribute()
    {
        // 1. Try parent's cover if exists
        if ($this->cover_id) {
            $path = (str_contains($this->cover_id, '/') || str_contains($this->cover_id, '.'))
                ? $this->cover_id
                : 'covers/' . $this->cover_id . 'f.jpg';

            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            }
        }

        // 2. Fallback for boxsets: use the first child's cover
        if ($this->boxsetChildren->count() > 0) {
            $firstChild = $this->boxsetChildren->first();
            if ($firstChild) {
                return $firstChild->cover_url;
            }
        }

        return null;
    }

    public function getBackdropUrlAttribute()
    {
        // 1. Specific backdrop_id (TMDb)
        if ($this->backdrop_id) {
            $path = (str_contains($this->backdrop_id, '/') || str_contains($this->backdrop_id, '.'))
                ? $this->backdrop_id
                : 'backdrops/' . $this->backdrop_id . '.jpg';

            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            }
        }

        // 2. Fallback for v1.5 movies: check 'b' version of the cover
        if ($this->cover_id && !str_contains($this->cover_id, '/') && !str_contains($this->cover_id, '.')) {
            $path = 'covers/' . $this->cover_id . 'b.jpg';
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            }
        }

        // 3. Fallback for boxsets: use the first child's backdrop/cover
        if ($this->boxsetChildren->count() > 0) {
            $firstChild = $this->boxsetChildren->first();
            if ($firstChild) {
                return $firstChild->backdrop_url;
            }
        }

        return null;
    }
}
