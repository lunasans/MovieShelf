<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('audit_log')) {
            Schema::create('audit_log', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('event', 100);
                $table->string('auditable_type', 100);
                $table->unsignedBigInteger('auditable_id');
                $table->text('old_values')->nullable();
                $table->text('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['auditable_type', 'auditable_id'], 'idx_auditable');
                $table->index('user_id', 'idx_audit_log_user');
                $table->index('event', 'idx_event');

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
