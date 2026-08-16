<?php

namespace App\Models;

use App\Models\Concerns\ResolvesMediaUrls;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Movie extends Model
{
    use HasFactory;
    use ResolvesMediaUrls;

    protected $fillable = [
        'id',
        'collection_no',
        'title',
        'year',
        'rating',
        'genre',
        'cover_id',
        'backdrop_id',
        'collection_type',
        'runtime',
        'rating_age',
        'overview',
        'director',
        'trailer_url',
        'boxset_parent',
        'user_id',
        'is_deleted',
        'deleted_at',
        'view_count',
        'tag',
        'tmdb_id',
        'tmdb_type',
        'tmdb_json',
        'in_collection',
        'edition',
        'region_code',
        'disc_location',
        'purchase_date',
        'purchase_price',
        'condition',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'cover_url',
        'backdrop_url',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
        'year' => 'integer',
        'runtime' => 'integer',
        'view_count' => 'integer',
        'tmdb_json' => 'array',
        'in_collection' => 'boolean',
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
    ];

    /**
     * Nur Filme. Diskriminator ist collection_type ('Film' | 'Serie') —
     * tmdb_type ist reine TMDb-Metainfo und fehlt z. B. bei Serien, die
     * über den Desktop-Sync angelegt wurden.
     */
    public function scopeMoviesOnly($query)
    {
        return $query->where(function ($w) {
            $w->where('collection_type', '!=', 'Serie')->orWhereNull('collection_type');
        });
    }

    /** Nur Serien (collection_type = 'Serie'). */
    public function scopeSeriesOnly($query)
    {
        return $query->where('collection_type', 'Serie');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parentBoxset()
    {
        return $this->belongsTo(Movie::class, 'boxset_parent');
    }

    public function boxsetChildren()
    {
        return $this->hasMany(Movie::class, 'boxset_parent')
            ->where('in_collection', true)
            ->where('is_deleted', false)
            ->orderBy('year')
            ->orderBy('title');
    }

    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'film_actor', 'film_id', 'actor_id')
            ->withPivot(['role', 'is_main_role', 'sort_order']);
    }

    public function seasons()
    {
        return $this->hasMany(Season::class);
    }

    public function watchedByUsers()
    {
        return $this->belongsToMany(User::class, 'movie_user_watched');
    }

    /**
     * Die abgegebenen Sternbewertungen zu diesem Titel.
     *
     * Wird im Export auf den angemeldeten Nutzer gefiltert eager geladen —
     * ohne diese Relation liesse sich seine eigene Bewertung nur mit einer
     * Abfrage je Film holen.
     */
    public function userRatings()
    {
        return $this->hasMany(UserRating::class);
    }

    /**
     * Gilt dieser Titel für den Benutzer als gesehen?
     *
     * Bei einem Boxset wird der Stand aus den enthaltenen Filmen abgeleitet:
     * ein Boxset schaut niemand, man schaut die Filme darin. Ohne diese
     * Ableitung steht eine Sammlung für immer als ungesehen da, auch wenn
     * längst jeder Teil geschaut ist — die Hülle bekommt nie eine eigene
     * Markierung, weil es an ihr nichts zu schauen gibt.
     *
     * Abgeleitet wird streng: erst wenn wirklich jeder Teil gesehen ist. Ein
     * halb geschautes Boxset als "gesehen" auszuweisen wäre die unangenehmere
     * Unwahrheit.
     *
     * Der eigene Pivot-Eintrag der Hülle bleibt dabei außen vor. Er stünde
     * sonst als zweite Wahrheit neben dieser Ableitung, und man sähe der
     * Anzeige nicht mehr an, welche gerade gilt.
     */
    public function isWatchedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $children = $this->relationLoaded('boxsetChildren')
            ? $this->boxsetChildren
            : $this->boxsetChildren()->get();

        if ($children->isEmpty()) {
            return $this->watchedByUsers()->where('users.id', $user->id)->exists();
        }

        $gesehen = DB::table('movie_user_watched')
            ->where('user_id', $user->id)
            ->whereIn('movie_id', $children->pluck('id'))
            ->count();

        return $gesehen === $children->count();
    }

    /**
     * "Gesehen" für einen Benutzer setzen und den Abgleich davon in Kenntnis
     * setzen.
     *
     * Das `touch()` ist der eigentliche Punkt dieser Methode. Die Markierung
     * lebt in `movie_user_watched`; ein reines `attach()`/`detach()` lässt
     * `movies.updated_at` unberührt. Der Delta-Export filtert aber genau
     * darauf — ein geänderter Gesehen-Stand fiel deshalb aus jedem
     * Delta-Abgleich heraus und erreichte Desktop- und Android-App nie. Nur
     * ein Voll-Abgleich brachte ihn mit.
     *
     * Bei einem Boxset wirkt das Setzen auf die enthaltenen Filme: sein Stand
     * wird aus ihnen abgeleitet (siehe [isWatchedBy]), ihn selbst zu markieren
     * bliebe wirkungslos.
     *
     * @return bool der Stand, der danach gilt.
     */
    public function setWatchedFor(User $user, bool $watched): bool
    {
        $children = $this->boxsetChildren()->get();
        $targets = $children->isNotEmpty() ? $children : collect([$this]);

        $watched
            ? $user->watchedMovies()->syncWithoutDetaching($targets->pluck('id'))
            : $user->watchedMovies()->detach($targets->pluck('id'));

        // Ohne diesen Anstoß bliebe die Änderung für den Abgleich unsichtbar.
        self::whereIn('id', $targets->pluck('id'))->update(['updated_at' => now()]);

        return $watched;
    }

    public function lists()
    {
        return $this->morphToMany(MovieList::class, 'item', 'list_items', 'item_id', 'list_id')
            ->withPivot('added_at');
    }

    public function getCoverUrlAttribute()
    {
        $url = $this->resolveImageUrl($this->cover_id, 'cover');

        if (! $url && $this->boxsetChildren->count() > 0) {
            $firstChild = $this->boxsetChildren->first();

            return $firstChild ? $firstChild->cover_url : null;
        }

        return $url;
    }

    public function getBackdropUrlAttribute()
    {
        $url = $this->resolveImageUrl($this->backdrop_id, 'backdrop');

        // Fallback for v1.5: check 'b' version of cover
        if (! $url && $this->cover_id && ! str_contains($this->cover_id, '/') && ! str_starts_with($this->cover_id, 'http')) {
            $path = 'covers/'.$this->cover_id.'b.jpg';
            if (Storage::disk('public')->exists($path)) {
                $url = Storage::disk('public')->url($path);
            }
        }

        // Fallback for boxsets
        if (! $url && $this->boxsetChildren->count() > 0) {
            $firstChild = $this->boxsetChildren->first();

            return $firstChild ? $firstChild->backdrop_url : null;
        }

        return $url;
    }

    /**
     * Resolve image URL from ID and type.
     */
    protected function resolveImageUrl($id, $type)
    {
        if (! $id) {
            return null;
        }

        $url = null;
        $disk = Storage::disk('public');

        // Check for absolute URLs
        if (str_starts_with($id, 'http')) {
            return $id;
        }

        // 1. Check if the ID itself exists locally as a file.
        // This handles cases like 'tmdb_xyz.jpg' or 'cover/tmdb_xyz.jpg' stored locally.
        if ($disk->exists($id)) {
            return '/media/' . $id;
        }

        // Rohe TMDb-Referenzen (tmdb_… oder /pfad) werden NICHT direkt von
        // image.tmdb.org geladen (kein Hotlink). Ist das Bild nicht lokal
        // vorhanden, gibt es einen Platzhalter (null).
        if (str_starts_with($id, 'tmdb_') || str_starts_with($id, '/')) {
            return null;
        }

        // 4. Modern approach: ID is a path (e.g. covers/abc.jpg)
        if (str_contains($id, '/') && str_contains($id, '.')) {
            if ($disk->exists($id)) {
                return '/media/' . $id;
            }
        }

        // 5. Legacy: Use the structured legacy path with fallback extensions
        if (($legacyUrl = $this->resolveLegacyStorageUrl($id, $type)) !== null) {
            return $legacyUrl;
        }

        return null;
    }

    protected function resolveLegacyStorageUrl($id, $type)
    {
        $disk = Storage::disk('public');

        // Check both singular and plural versions for flexibility (e.g. cover vs covers)
        $folders = ($type === 'cover') ? ['covers', 'cover'] : ['backdrops', 'backdrop'];
        $suffix = ($type === 'cover') ? 'f' : '';

        foreach ($folders as $folder) {
            // Try standard extension first
            $path = "$folder/$id$suffix.jpg";

            if ($disk->exists($path)) {
                return '/media/' . $path;
            }

            // Fallback extensions
            $extensions = ['.JPG', '.jpeg', '.JPEG', '.png', '.PNG', '.webp'];
            foreach ($extensions as $ext) {
                $fallbackPath = "$folder/$id$suffix$ext";
                if ($disk->exists($fallbackPath)) {
                    return '/media/' . $fallbackPath;
                }
            }

            // Try without suffix as a last resort
            if ($suffix !== '' && $disk->exists("$folder/$id.jpg")) {
                return '/media/' . "$folder/$id.jpg";
            }
        }

        return null;
    }
}
