<?php

namespace App\Models;

use App\Models\Concerns\ResolvesMediaUrls;
use Illuminate\Database\Eloquent\Model;

/**
 * Film, der in einer Liste referenziert ist, aber NICHT in der Sammlung liegt
 * (z. B. per TMDb in eine Liste aufgenommen, ohne ihn zu besitzen). Eigener
 * ID-Raum, damit Sammlungsfilme (movies) lückenlose Nummern behalten.
 */
class ExternalMovie extends Model
{
    use ResolvesMediaUrls;

    protected $table = 'external_movies';

    protected $fillable = [
        'user_id',
        'title',
        'year',
        'genre',
        'director',
        'runtime',
        'rating',
        'rating_age',
        'overview',
        'collection_type',
        'cover_id',
        'backdrop_id',
        'trailer_url',
        'tmdb_id',
        'tmdb_type',
    ];

    protected $appends = [
        'cover_url',
        'backdrop_url',
    ];

    protected $casts = [
        'year' => 'integer',
        'runtime' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lists()
    {
        return $this->morphToMany(MovieList::class, 'item', 'list_items', 'item_id', 'list_id')
            ->withPivot('added_at');
    }

    public function getCoverUrlAttribute()
    {
        return $this->resolveImageUrl($this->cover_id, 'cover');
    }

    public function getBackdropUrlAttribute()
    {
        return $this->resolveImageUrl($this->backdrop_id, 'backdrop');
    }
}
