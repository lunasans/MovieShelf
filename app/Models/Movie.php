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
        return $this->hasMany(Movie::class, 'boxset_parent')->orderBy('year')->orderBy('title');
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
            } elseif (Storage::disk('central')->exists($path)) {
                $url = Storage::disk('central')->url($path);
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
        if (! $id) {
            return null;
        }

        $url = null;
        $disk = Storage::disk('public');
        $centralDisk = Storage::disk('central');

        // Check for absolute URLs
        if (str_starts_with($id, 'http')) {
            return $id;
        } 
        
        // 1. Check if the ID itself exists locally as a file (Tenant or Central)
        // This handles cases like 'tmdb_xyz.jpg' or 'cover/tmdb_xyz.jpg' stored locally.
        if ($disk->exists($id)) {
            return '/storage/' . $id;
        }
        if ($centralDisk->exists($id)) {
            return '/storage/' . $id;
        }

        // 2. Check for TMDb logic (Remote fallback)
        if (str_starts_with($id, 'tmdb_')) {
            return 'https://image.tmdb.org/t/p/' . ($type === 'cover' ? 'w500' : 'original') . '/' . substr($id, 5);
        }

        // 3. Direct TMDb paths (start with /)
        if (str_starts_with($id, '/')) {
            return $this->resolveTmdbUrl($id, $type);
        } 

        // 4. Modern approach: ID is a path (e.g. covers/abc.jpg)
        if (str_contains($id, '/') && str_contains($id, '.')) {
            if ($disk->exists($id)) {
                return '/storage/' . $id;
            } elseif ($centralDisk->exists($id)) {
                return '/storage/' . $id;
            }
        } 

        // 5. Legacy: Use the structured legacy path with fallback extensions
        if (($legacyUrl = $this->resolveLegacyStorageUrl($id, $type)) !== null) {
            return $legacyUrl;
        }

        return null;
    }

    protected function resolveTmdbUrl($id, $type)
    {
        $base = $type === 'cover' ? 'https://image.tmdb.org/t/p/w500' : 'https://image.tmdb.org/t/p/w1280';

        return $base.$id;
    }

    protected function resolveLegacyStorageUrl($id, $type)
    {
        $disk = Storage::disk('public');
        $centralDisk = Storage::disk('central');
        
        // Check both singular and plural versions for flexibility (e.g. cover vs covers)
        $folders = ($type === 'cover') ? ['covers', 'cover'] : ['backdrops', 'backdrop'];
        $suffix = ($type === 'cover') ? 'f' : '';

        foreach ($folders as $folder) {
            // Try standard extension first
            $path = "$folder/$id$suffix.jpg";
            if ($disk->exists($path)) {
                return '/storage/' . $path;
            }
            if ($centralDisk->exists($path)) {
                return '/storage/' . $path;
            }

            // Fallback extensions
            $extensions = ['.JPG', '.jpeg', '.JPEG', '.png', '.PNG', '.webp'];
            foreach ($extensions as $ext) {
                $fallbackPath = "$folder/$id$suffix$ext";
                if ($disk->exists($fallbackPath)) {
                    return '/storage/' . $fallbackPath;
                }
                if ($centralDisk->exists($fallbackPath)) {
                    return '/storage/' . $fallbackPath;
                }
            }

            // Try without suffix as a last resort
            if ($suffix !== '') {
                if ($disk->exists("$folder/$id.jpg")) {
                    return '/storage/' . "$folder/$id.jpg";
                }
                if ($centralDisk->exists("$folder/$id.jpg")) {
                    return '/storage/' . "$folder/$id.jpg";
                }
            }
        }

        return null;
    }
}
