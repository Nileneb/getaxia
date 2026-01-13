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
        Schema::table('users', function (Blueprint $table) {
            // email is already unique, but adding explicit index for lookups
            // is_guest for filtering guest vs regular users
            $table->index('is_guest');
        });

        Schema::table('runs', function (Blueprint $table) {
            // user_id and company_id are already foreign keys with indexes
            // but adding explicit indexes for better query performance
            $table->index('user_id');
            $table->index('company_id');
            // Composite index for common query patterns (user's runs for a company)
            $table->index(['user_id', 'company_id']);
        });

        Schema::table('todos', function (Blueprint $table) {
            // run_id is already a foreign key, adding explicit index
            $table->index('run_id');
        });

        Schema::table('todo_evaluations', function (Blueprint $table) {
            // Both are foreign keys, adding explicit indexes
            $table->index('todo_id');
            $table->index('run_id');
            // Composite index for common query patterns
            $table->index(['run_id', 'todo_id']);
        });

        Schema::table('missing_todos', function (Blueprint $table) {
            // run_id is a foreign key, adding explicit index
            $table->index('run_id');
        });

        Schema::table('webhook_presets', function (Blueprint $table) {
            // user_id is a foreign key, adding explicit index
            $table->index('user_id');
        });

        Schema::table('goal_kpis', function (Blueprint $table) {
            // Both are foreign keys, adding explicit indexes
            $table->index('company_id');
            $table->index('goal_id');
            // Composite index for common query patterns
            $table->index(['company_id', 'goal_id']);
        });

        Schema::table('companies', function (Blueprint $table) {
            // owner_user_id is a foreign key, adding explicit index
            $table->index('owner_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_guest']);
        });

        Schema::table('runs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['company_id']);
            $table->dropIndex(['user_id', 'company_id']);
        });

        Schema::table('todos', function (Blueprint $table) {
            $table->dropIndex(['run_id']);
        });

        Schema::table('todo_evaluations', function (Blueprint $table) {
            $table->dropIndex(['todo_id']);
            $table->dropIndex(['run_id']);
            $table->dropIndex(['run_id', 'todo_id']);
        });

        Schema::table('missing_todos', function (Blueprint $table) {
            $table->dropIndex(['run_id']);
        });

        Schema::table('webhook_presets', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('goal_kpis', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
            $table->dropIndex(['goal_id']);
            $table->dropIndex(['company_id', 'goal_id']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['owner_user_id']);
        });
    }
};
