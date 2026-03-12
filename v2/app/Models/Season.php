<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'season_number',
        'title',
        'overview',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }
}
