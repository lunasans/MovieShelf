<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('oauth_clients', function (Blueprint $table) {
            $table->string('client_id', 80)->primary();
            $table->string('client_secret', 80)->nullable();
            $table->string('name', 255)->notNull();
            $table->string('redirect_uri', 1000)->notNull();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::connection('central')->create('oauth_auth_codes', function (Blueprint $table) {
            $table->string('code', 80)->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('client_id', 80);
            $table->string('redirect_uri', 1000);
            $table->string('code_challenge', 128)->nullable();
            $table->string('code_challenge_method', 10)->nullable();
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('client_id')->references('client_id')->on('oauth_clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('oauth_auth_codes');
        Schema::connection('central')->dropIfExists('oauth_clients');
    }
};
