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
            'rating_age' => (int) $this->rating_age,
            'genre' => $this->genre,
            'tag' => $this->tag,
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
            'is_boxset' => $this->boxset_children_count > 0 || ($this->relationLoaded('boxsetChildren') && $this->boxsetChildren->count() > 0),
            'boxset_parent_id' => $this->boxset_parent,
            'boxset_children' => MovieResource::collection($this->whenLoaded('boxsetChildren')),
            'tmdb_id' => $this->tmdb_id,
            'collection_type' => $this->collection_type,
            'actors_names' => $this->whenLoaded('actors', fn () => $this->actors->pluck('name')->join(', ')),
            'is_deleted' => $this->is_deleted,
            'in_collection' => $this->in_collection ?? true,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
