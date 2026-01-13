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
        Schema::create('todo_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('run_id')->constrained('runs')->onDelete('cascade');
            $table->foreignUuid('todo_id')->constrained('todos')->onDelete('cascade');
            $table->enum('color', ['green', 'yellow', 'orange']);
            $table->integer('score');
            $table->text('reasoning');
            $table->enum('priority_recommendation', ['high', 'low', 'none'])->nullable();
            $table->enum('action_recommendation', ['keep', 'delegate', 'drop'])->nullable();
            $table->string('delegation_target_role')->nullable();
            $table->foreignUuid('primary_goal_id')->nullable()->constrained('goals')->onDelete('set null');
            $table->foreignUuid('primary_kpi_id')->nullable()->constrained('goal_kpis')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todo_evaluations');
    }
};


