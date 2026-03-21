<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        $url = $this->resolveImageUrl($this->cover_id, 'cover');

        if (! $url && $this->boxsetChildren->count() > 0) {
            $firstChild = $this->boxsetChildren->first();

            return $firstChild ? $firstChild->cover_url : null;
        }

        return $url;
    }

    public function getBackdropUrlAttribute()
    {
        $url = $this->resolveImageUrl($this->backdrop_id, 'backdrop');

        // Fallback for v1.5: check 'b' version of cover
        if (! $url && $this->cover_id && ! str_contains($this->cover_id, '/') && ! str_starts_with($this->cover_id, 'http')) {
            $path = 'covers/'.$this->cover_id.'b.jpg';
            if (Storage::disk('public')->exists($path)) {
                $url = Storage::disk('public')->url($path);
            }
        }

        // Fallback for boxsets
        if (! $url && $this->boxsetChildren->count() > 0) {
            $firstChild = $this->boxsetChildren->first();

            return $firstChild ? $firstChild->backdrop_url : null;
        }

        return $url;
    }

    /**
     * Resolve image URL from ID and type.
     */
    protected function resolveImageUrl($id, $type)
    {
        $url = null;

        if ($id) {
            if (str_starts_with($id, 'http')) {
                // Direct URL
                $url = $id;
            } elseif (str_starts_with($id, '/')) {
                // TMDb Path
                $base = $type === 'cover' ? 'https://image.tmdb.org/t/p/w500' : 'https://image.tmdb.org/t/p/w1280';
                $url = $base.$id;
            } elseif (str_contains($id, '.') && Storage::disk('public')->exists($id)) {
                // Local file with extension
                $url = Storage::disk('public')->url($id);
            } else {
                // Legacy format
                $folder = $type === 'cover' ? 'covers' : 'backdrops';
                $suffix = $type === 'cover' ? 'f' : '';
                $path = "$folder/$id$suffix.jpg";

                if (Storage::disk('public')->exists($path)) {
                    $url = Storage::disk('public')->url($path);
                }
            }
        }

        return $url;
    }
}
