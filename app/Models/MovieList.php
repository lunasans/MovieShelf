<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieList extends Model
{
    protected $table = 'lists';

    protected $fillable = ['user_id', 'name', 'share_token'];

    public function isShared(): bool
    {
        return ! is_null($this->share_token);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Sammlungsfilme in dieser Liste (polymorpher Pivot list_items, item_type='movie'). */
    public function movies()
    {
        return $this->morphedByMany(Movie::class, 'item', 'list_items', 'list_id', 'item_id')
            ->withPivot('added_at')
            ->orderBy('movies.title');
    }

    /** Externe Filme (nicht in der Sammlung) in dieser Liste (item_type='external'). */
    public function externalMovies()
    {
        return $this->morphedByMany(ExternalMovie::class, 'item', 'list_items', 'list_id', 'item_id')
            ->withPivot('added_at')
            ->orderBy('external_movies.title');
    }

    /**
     * Gemischte Liste beider Item-Typen, gemeinsam nach added_at sortiert.
     * Jedes Element trägt `item_type` ('movie'|'external') zur Unterscheidung.
     */
    public function allItems()
    {
        $movies   = $this->movies()->get()->each(fn ($m) => $m->item_type = 'movie');
        $external = $this->externalMovies()->get()->each(fn ($e) => $e->item_type = 'external');

        return $movies->concat($external)
            ->sortBy(fn ($i) => $i->pivot->added_at)
            ->values();
    }
}
