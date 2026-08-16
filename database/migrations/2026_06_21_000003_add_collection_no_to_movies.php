<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('movies', 'collection_no')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->unsignedInteger('collection_no')->nullable()->after('id');
                $table->unique('collection_no', 'uniq_collection_no');
            });
        }

        // Lückenlos 1..n nach id-Reihenfolge. Wunschfilme sind bereits in wishlist_movies
        // ausgelagert, daher zieht nur noch die Sammlung Nummern.
        $no = 0;
        DB::table('movies')->orderBy('id')->select('id')->each(function ($m) use (&$no) {
            $no++;
            DB::table('movies')->where('id', $m->id)->update(['collection_no' => $no]);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('movies', 'collection_no')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->dropUnique('uniq_collection_no');
                $table->dropColumn('collection_no');
            });
        }
    }
};
