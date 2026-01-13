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
        Schema::create('agent_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('session_id')->unique();
            $table->uuid('user_id');
            $table->string('mode')->default('chat'); // chat, workflow, etc.
            $table->string('workflow_key')->nullable(); // n8n workflow identifier
            $table->json('meta')->nullable(); // additional session metadata
            $table->timestamp('expires_at');
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('session_id');
            $table->index('expires_at');

            // Foreign key constraint
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_sessions');
    }
};
