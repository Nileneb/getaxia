<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            // Status: todo, in_progress, done, blocked, canceled
            if (!Schema::hasColumn('todos', 'status')) {
                $table->string('status', 32)->default('todo')->index();
            }
            // Hierarchy: parent-child relationships
            if (!Schema::hasColumn('todos', 'parent_id')) {
                $table->uuid('parent_id')->nullable()->index();
            }
            // Position may already exist; add index if column exists and index not present
            if (Schema::hasColumn('todos', 'position')) {
                $table->index('position');
            }
        });
    }

    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            if (Schema::hasColumn('todos', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('todos', 'parent_id')) {
                $table->dropColumn('parent_id');
            }
            // Do not drop position; it may be part of existing schema
        });
    }
};
