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
        Schema::create('goal_kpis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('goal_id')->constrained('goals')->onDelete('cascade');
            $table->string('name');
            $table->decimal('current_value', 12, 2)->nullable();
            $table->decimal('target_value', 12, 2)->nullable();
            $table->string('unit')->nullable();
            $table->string('time_frame')->nullable();
            $table->boolean('is_top_kpi')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_kpis');
    }
};


