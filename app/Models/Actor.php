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
        'slug',
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
        if (! $this->profile_path) {
            return null;
        }

        $url = null;
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        if (str_starts_with($this->profile_path, 'http')) {
            $url = $this->profile_path;
        } elseif (str_starts_with($this->profile_path, '/')) {
            $url = 'https://image.tmdb.org/t/p/w185'.$this->profile_path;
        } elseif (str_contains($this->profile_path, '.') && $disk->exists($this->profile_path)) {
            $url = $disk->url($this->profile_path);
        } elseif ($disk->exists('actors/'.$this->profile_path)) {
            $url = $disk->url('actors/'.$this->profile_path);
        }

        return $url;
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
