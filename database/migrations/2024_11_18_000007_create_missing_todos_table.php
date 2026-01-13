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
        Schema::create('missing_todos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('run_id')->constrained('runs')->onDelete('cascade');
            $table->foreignUuid('goal_id')->nullable()->constrained('goals')->onDelete('set null');
            $table->foreignUuid('kpi_id')->nullable()->constrained('goal_kpis')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['hiring', 'prioritization', 'delegation', 'culture', 'other'])->nullable();
            $table->integer('impact_score')->nullable();
            $table->string('suggested_owner_role')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missing_todos');
    }
};


