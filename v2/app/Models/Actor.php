<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    use HasFactory;

    protected $fillable = [
        'tmdb_id',
        'first_name',
        'last_name',
        'profile_path',
        'birth_year',
        'birthday',
        'deathday',
        'place_of_birth',
        'bio',
    ];

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'film_actor', 'actor_id', 'film_id')
                    ->withPivot(['role', 'is_main_role', 'sort_order']);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
