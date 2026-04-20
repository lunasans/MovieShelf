<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('desktop_releases', function (Blueprint $table) {
            $table->string('download_url_linux_appimage')->nullable()->after('download_url');
            $table->string('download_url_linux_deb')->nullable()->after('download_url_linux_appimage');
        });
    }

    public function down(): void
    {
        Schema::table('desktop_releases', function (Blueprint $table) {
            $table->dropColumn(['download_url_linux_appimage', 'download_url_linux_deb']);
        });
    }
};
