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
            'item_type' => 'movie',
            'collection_no' => $this->collection_no,
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
            'edition' => $this->edition,
            'region_code' => $this->region_code,
            'disc_location' => $this->disc_location,
            'purchase_date' => optional($this->purchase_date)->format('Y-m-d'),
            'purchase_price' => $this->purchase_price !== null ? (float) $this->purchase_price : null,
            'condition' => $this->condition,
            'view_count' => $this->view_count,
            // Bei einem Boxset aus den Teilen abgeleitet (siehe Movie::isWatchedBy).
            // Die Ableitung braucht die Kinder; sind sie nicht geladen, bleibt es
            // beim eigenen Stand der Zeile — der Export lädt sie nicht mit, und
            // eine Abfrage je Film wäre dort zu teuer.
            'is_watched' => $this->whenLoaded('watchedByUsers', function () {
                if ($this->relationLoaded('boxsetChildren') && $this->boxsetChildren->isNotEmpty()) {
                    return $this->isWatchedBy(auth()->user());
                }

                return $this->watchedByUsers->contains(auth()->id());
            }),
            // Die eigene Sternbewertung (1-5) des angemeldeten Nutzers, nicht der
            // Durchschnitt: Clients spiegeln damit ihren eigenen Stand. Die
            // Relation wird im Export bereits auf den Nutzer gefiltert geladen.
            'user_rating' => $this->whenLoaded(
                'userRatings',
                fn() => $this->userRatings->first()?->rating
            ),
            'actors' => ActorResource::collection($this->whenLoaded('actors')),
            'seasons' => $this->when(
                $this->relationLoaded('seasons'),
                fn() => $this->seasons->sortBy('season_number')->map(fn($s) => [
                    'id'            => $s->id,
                    'season_number' => $s->season_number,
                    'title'         => $s->title,
                    'overview'      => $s->overview,
                    'episodes'      => $s->relationLoaded('episodes')
                        ? $s->episodes->sortBy('episode_number')->map(fn($e) => [
                            'id'             => $e->id,
                            'episode_number' => $e->episode_number,
                            'title'          => $e->title,
                            'overview'       => $e->overview,
                            // Gesehen-Stand des angemeldeten Nutzers. Nur wenn die
                            // Relation geladen ist — sonst waere es eine Abfrage je
                            // Folge, und eine Serie hat schnell hundert davon.
                            'is_watched'     => $e->relationLoaded('watchedByUsers')
                                ? $e->watchedByUsers->isNotEmpty()
                                : null,
                        ])->values()
                        : [],
                ])->values()
            ),
            'is_boxset' => $this->boxset_children_count > 0 || ($this->relationLoaded('boxsetChildren') && $this->boxsetChildren->count() > 0),
            'boxset_parent_id' => $this->boxset_parent,
            'boxset_children' => MovieResource::collection($this->whenLoaded('boxsetChildren')),
            'tmdb_id' => $this->tmdb_id,
            'collection_type' => $this->collection_type,
            'actors_names' => $this->whenLoaded('actors', fn () => $this->actors->pluck('name')->join(', ')),
            'is_deleted' => $this->is_deleted,
            'in_collection' => $this->in_collection ?? true,
            'is_wishlisted' => $this->whenNotNull($this->is_wishlisted ?? null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
