<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExternalMovieResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_type' => 'external',
            'title' => $this->title,
            'year' => $this->year,
            'rating' => $this->rating,
            'rating_age' => (int) $this->rating_age,
            'genre' => $this->genre,
            'overview' => $this->overview,
            'runtime' => $this->runtime,
            'director' => $this->director,
            'cover_url' => $this->cover_url,
            'backdrop_url' => $this->backdrop_url,
            'trailer_url' => $this->trailer_url,
            'collection_type' => $this->collection_type,
            'tmdb_id' => $this->tmdb_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
