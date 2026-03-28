<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'year' => $this->year,
            'rating' => $this->rating,
            'genre' => $this->genre,
            'overview' => $this->overview,
            'runtime' => $this->runtime,
            'director' => $this->director,
            'cover_url' => $this->cover_url,
            'backdrop_url' => $this->backdrop_url,
            'trailer_url' => $this->trailer_url,
            'view_count' => $this->view_count,
            'is_watched' => $this->whenLoaded('watchedByUsers', function () {
                return $this->watchedByUsers->contains(auth()->id());
            }),
            'actors' => ActorResource::collection($this->whenLoaded('actors')),
            'tmdb_id' => $this->tmdb_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
