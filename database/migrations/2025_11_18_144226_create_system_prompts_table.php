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
        Schema::create('system_prompts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['todo_analysis', 'company_extraction', 'goals_extraction']);
            $table->text('system_message');
            $table->text('user_prompt_template');
            $table->decimal('temperature', 2, 1)->default(0.7);
            $table->boolean('is_active')->default(false);
            $table->string('version')->default('v1.0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_prompts');
    }
};
