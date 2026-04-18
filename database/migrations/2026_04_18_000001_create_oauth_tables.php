<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->string('client_id', 80)->primary();
            $table->string('client_secret', 80)->notNull();
            $table->string('name', 255)->notNull();
            $table->string('redirect_uri', 1000)->notNull();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->string('code', 80)->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('client_id', 80);
            $table->string('redirect_uri', 1000);
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('client_id')->references('client_id')->on('oauth_clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_auth_codes');
        Schema::dropIfExists('oauth_clients');
    }
};
