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
        $centralDisk = \Illuminate\Support\Facades\Storage::disk('central');
        $s3Disk = \Illuminate\Support\Facades\Storage::disk('s3');

        if (str_starts_with($this->profile_path, 'http')) {
            $url = $this->profile_path;
        } elseif (str_starts_with($this->profile_path, '/')) {
            $url = 'https://image.tmdb.org/t/p/w185'.$this->profile_path;
        } elseif (str_contains($this->profile_path, '/') && str_contains($this->profile_path, '.')) {
            // Assume S3 if UPLOAD_DISK is s3
            if (env('UPLOAD_DISK') === 's3') {
                return $s3Disk->url($this->profile_path);
            }

            if ($disk->exists($this->profile_path)) {
                $url = '/media/'.$this->profile_path;
            } elseif ($centralDisk->exists($this->profile_path)) {
                $url = '/media/'.$this->profile_path;
            }
        } else {
            // Check structured paths
            // If we are on S3, we assume it's in the actors/ folder or just try it
            if (env('UPLOAD_DISK') === 's3') {
                return $s3Disk->url('actors/'.$this->profile_path);
            }

            if ($disk->exists('actors/'.$this->profile_path)) {
                $url = '/media/actors/'.$this->profile_path;
            } elseif ($centralDisk->exists('actors/'.$this->profile_path)) {
                $url = '/media/actors/'.$this->profile_path;
            } elseif ($disk->exists($this->profile_path)) {
                $url = '/media/'.$this->profile_path;
            } elseif ($centralDisk->exists($this->profile_path)) {
                $url = '/media/'.$this->profile_path;
            }
        }

        return $url;
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
