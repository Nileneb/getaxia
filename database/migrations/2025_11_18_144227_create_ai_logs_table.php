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
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('run_id')->nullable()->constrained('runs')->onDelete('cascade');
            $table->enum('prompt_type', ['todo_analysis', 'company_extraction', 'goals_extraction']);
            $table->foreignUuid('system_prompt_id')->nullable()->constrained('system_prompts')->onDelete('set null');
            $table->json('input_context');
            $table->json('response');
            $table->integer('tokens_used')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};
