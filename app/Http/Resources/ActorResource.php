<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActorResource extends JsonResource
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
            'name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'image_url' => $this->profile_url,
            'bio' => $this->bio,
            'birthday' => $this->birthday,
            'deathday' => $this->deathday,
            'place_of_birth' => $this->place_of_birth,
            'tmdb_id' => $this->tmdb_id,
            'imdb_id' => $this->imdb_id,
            'role' => $this->whenPivotLoaded('film_actor', function () {
                return $this->pivot->role;
            }),
            'is_main_role' => $this->whenPivotLoaded('film_actor', function () {
                return (bool) $this->pivot->is_main_role;
            }),
            'movies' => MovieResource::collection($this->whenLoaded('movies')),
        ];
    }
}
