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
        Schema::table('goals', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('goal_kpis', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('runs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('todos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('missing_todos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('webhook_presets', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('agent_sessions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('goal_kpis', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('runs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('todos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('missing_todos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('webhook_presets', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('agent_sessions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
