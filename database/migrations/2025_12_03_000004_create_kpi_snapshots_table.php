<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kpi_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('run_id');
            $table->uuid('goal_kpi_id');
            $table->decimal('current_value', 20, 6)->nullable();
            $table->decimal('target_value', 20, 6)->nullable();
            $table->string('unit', 64)->nullable();
            $table->boolean('is_top_kpi')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['run_id']);
            $table->index(['goal_kpi_id']);
            $table->index(['run_id', 'goal_kpi_id']);
            $table->index(['is_top_kpi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_snapshots');
    }
};
