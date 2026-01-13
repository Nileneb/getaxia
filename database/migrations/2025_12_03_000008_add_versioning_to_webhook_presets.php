<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('webhook_presets', function (Blueprint $table) {
            // Add versioning fields
            $table->integer('version')->default(1)->after('is_default');
            $table->uuid('created_by_run_id')->nullable()->after('version');
            $table->uuid('rollback_to_version_id')->nullable()->after('created_by_run_id');

            // Add config_json if not already present (from comment in task)
            if (!Schema::hasColumn('webhook_presets', 'config_json')) {
                $table->json('config_json')->nullable()->after('description');
            }

            // Add indexes
            if (!Schema::hasIndex('webhook_presets', 'webhook_presets_user_id_version_index')) {
                $table->index(['user_id', 'version']);
            }
            if (!Schema::hasIndex('webhook_presets', 'webhook_presets_user_id_is_active_index')) {
                $table->index(['user_id', 'is_active']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('webhook_presets', function (Blueprint $table) {
            $table->dropColumn(['version', 'created_by_run_id', 'rollback_to_version_id']);

            if (Schema::hasColumn('webhook_presets', 'config_json')) {
                $table->dropColumn('config_json');
            }
        });
    }
};
