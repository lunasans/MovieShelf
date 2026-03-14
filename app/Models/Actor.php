<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    use HasFactory;

    protected $fillable = [
        'tmdb_id',
        'imdb_id',
        'first_name',
        'last_name',
        'profile_path',
        'birth_year',
        'birthday',
        'deathday',
        'place_of_birth',
        'homepage',
        'bio',
        'view_count',
    ];

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'film_actor', 'actor_id', 'film_id')
                    ->withPivot(['role', 'is_main_role', 'sort_order']);
    }

    public function getProfileUrlAttribute()
    {
        if (!$this->profile_path) {
            return null;
        }

        // Direct URL
        if (str_starts_with($this->profile_path, 'http')) {
            return $this->profile_path;
        }

        // TMDb Path
        if (str_starts_with($this->profile_path, '/')) {
            return 'https://image.tmdb.org/t/p/w185' . $this->profile_path;
        }

        // Local file
        if (str_contains($this->profile_path, '.')) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->profile_path)) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($this->profile_path);
            }
        }

        // Default local path
        $path = 'actors/' . $this->profile_path;
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        }

        return null;
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
