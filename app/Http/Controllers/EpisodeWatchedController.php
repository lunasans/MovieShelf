<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Season;
use Illuminate\Support\Facades\Auth;

class EpisodeWatchedController extends Controller
{
    /**
     * Gesehen-Status einer einzelnen Episode für den angemeldeten Nutzer umschalten.
     */
    public function toggle(Episode $episode)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if ($user->watchedEpisodes()->where('episode_id', $episode->id)->exists()) {
            $user->watchedEpisodes()->detach($episode->id);
            $watched = false;
        } else {
            $user->watchedEpisodes()->attach($episode->id, ['watched_at' => now()]);
            $watched = true;
        }

        $this->beruehreFilm($episode);

        return response()->json([
            'watched' => $watched,
            'episode_id' => $episode->id,
        ]);
    }

    /**
     * Ganze Staffel als gesehen / ungesehen markieren.
     * Body: { watched: true|false }
     */
    public function toggleSeason(Season $season)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $episodeIds = $season->episodes()->pluck('id');
        $markWatched = request()->boolean('watched');

        if ($markWatched) {
            // Nur fehlende Zuordnungen ergänzen (kein Duplikat), watched_at setzen
            $payload = $episodeIds->mapWithKeys(fn ($id) => [$id => ['watched_at' => now()]])->all();
            $user->watchedEpisodes()->syncWithoutDetaching($payload);
        } else {
            $user->watchedEpisodes()->detach($episodeIds->all());
        }

        $season->movie?->touch();

        return response()->json([
            'watched' => $markWatched,
            'season_id' => $season->id,
            'episode_ids' => $episodeIds->values(),
        ]);
    }

    /**
     * Die Serie als geaendert markieren, zu der die Folge gehoert.
     *
     * Gleicher Grund wie bei Movie::setWatchedFor(): der Folgenstand lebt in
     * `episode_user_watched`, ein attach()/detach() dort laesst
     * `movies.updated_at` unberueht. Der Delta-Export filtert aber genau
     * darauf — ohne diesen Griff faellt ein geaenderter Folgenstand aus jedem
     * Delta-Abgleich heraus und erreicht die Clients nur beim Voll-Abgleich.
     */
    private function beruehreFilm(Episode $episode): void
    {
        $episode->loadMissing('season.movie');
        $episode->season?->movie?->touch();
    }
}
