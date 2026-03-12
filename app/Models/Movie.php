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

    public function getCoverUrlAttribute()
    {
        if (!$this->cover_id) {
            // Fallback for boxsets: use the first child's cover
            if ($this->boxsetChildren->count() > 0) {
                $firstChild = $this->boxsetChildren->first();
                if ($firstChild && $firstChild->cover_id) {
                    return $firstChild->cover_url;
                }
            }
            return null;
        }
        
        // If it already has a path/ext (TMDb new style), return it via public disk
        if (str_contains($this->cover_id, '/') || str_contains($this->cover_id, '.')) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->cover_id);
        }

        // Otherwise assume it's a v1.5 ID and append folder, suffix and extension
        return \Illuminate\Support\Facades\Storage::disk('public')->url('covers/' . $this->cover_id . 'f.jpg');
    }

    public function getBackdropUrlAttribute()
    {
        // If we have a specific backdrop_id (TMDb), use it
        if ($this->backdrop_id) {
            if (str_contains($this->backdrop_id, '/') || str_contains($this->backdrop_id, '.')) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($this->backdrop_id);
            }
            return \Illuminate\Support\Facades\Storage::disk('public')->url('backdrops/' . $this->backdrop_id . '.jpg');
        }

        // Fallback 1: If it's a v1.5 movie, try the 'b' version of the cover
        if ($this->cover_id && !str_contains($this->cover_id, '/') && !str_contains($this->cover_id, '.')) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url('covers/' . $this->cover_id . 'b.jpg');
        }

        // Fallback 2: For boxsets, use the first child's backdrop/cover
        if ($this->boxsetChildren->count() > 0) {
            $firstChild = $this->boxsetChildren->first();
            if ($firstChild) {
                return $firstChild->backdrop_url;
            }
        }

        return null;
    }
}
