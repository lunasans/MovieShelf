<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Polymorpher Listen-Pivot: eine Liste kann Sammlungsfilme (movie) ODER
        // Wunschfilme (wishlist) enthalten.
        if (! Schema::hasTable('list_items')) {
            Schema::create('list_items', function (Blueprint $table) {
                $table->foreignId('list_id')->constrained('lists')->onDelete('cascade');
                $table->string('item_type', 20); // 'movie' | 'external'
                $table->unsignedBigInteger('item_id');
                $table->timestamp('added_at')->useCurrent();

                $table->primary(['list_id', 'item_type', 'item_id']);
                $table->index(['item_type', 'item_id'], 'idx_list_item');
            });
        }

        // 1) Alle in_collection=0-Filme nach external_movies kopieren (id-Mapping merken).
        $map = [];
        $externalSource = DB::table('movies')->where('in_collection', false)->get();
        foreach ($externalSource as $m) {
            $newId = DB::table('external_movies')->insertGetId([
                'user_id'         => $m->user_id ?? null,
                'title'           => $m->title,
                'year'            => $m->year ?? null,
                'genre'           => $m->genre ?? null,
                'director'        => $m->director ?? null,
                'runtime'         => $m->runtime ?? null,
                'rating'          => $m->rating ?? null,
                'rating_age'      => $m->rating_age ?? null,
                'overview'        => $m->overview ?? null,
                'collection_type' => $m->collection_type ?? null,
                'cover_id'        => $m->cover_id ?? null,
                'backdrop_id'     => $m->backdrop_id ?? null,
                'trailer_url'     => $m->trailer_url ?? null,
                'tmdb_id'         => $m->tmdb_id ?? null,
                'tmdb_type'       => $m->tmdb_type ?? null,
                'created_at'      => $m->created_at ?? now(),
                'updated_at'      => $m->updated_at ?? now(),
            ]);
            $map[$m->id] = $newId;
        }

        // 2) Bestehende list_movies → list_items (movie bleibt movie, wishlist umgehängt).
        if (Schema::hasTable('list_movies')) {
            foreach (DB::table('list_movies')->get() as $r) {
                if (isset($map[$r->movie_id])) {
                    $type = 'external';
                    $itemId = $map[$r->movie_id];
                } else {
                    $type = 'movie';
                    $itemId = $r->movie_id;
                }
                DB::table('list_items')->insertOrIgnore([
                    'list_id'   => $r->list_id,
                    'item_type' => $type,
                    'item_id'   => $itemId,
                    'added_at'  => $r->added_at ?? now(),
                ]);
            }
        }

        // 3) Wunschfilme aus movies entfernen (FKs cascaden film_actor/seasons/… automatisch).
        DB::table('movies')->where('in_collection', false)->delete();

        // 4) Alten Pivot droppen.
        Schema::dropIfExists('list_movies');
    }

    public function down(): void
    {
        // Best-effort-Rückbau: nur movie-Items lassen sich nach list_movies zurückführen.
        if (! Schema::hasTable('list_movies')) {
            Schema::create('list_movies', function (Blueprint $table) {
                $table->foreignId('list_id')->constrained('lists')->onDelete('cascade');
                $table->foreignId('movie_id')->constrained('movies')->onDelete('cascade');
                $table->timestamp('added_at')->useCurrent();
                $table->primary(['list_id', 'movie_id']);
            });

            if (Schema::hasTable('list_items')) {
                foreach (DB::table('list_items')->where('item_type', 'movie')->get() as $r) {
                    DB::table('list_movies')->insertOrIgnore([
                        'list_id'  => $r->list_id,
                        'movie_id' => $r->item_id,
                        'added_at' => $r->added_at ?? now(),
                    ]);
                }
            }
        }

        Schema::dropIfExists('list_items');
    }
};
