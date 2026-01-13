<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_prompts', function (Blueprint $table) {
            $table->boolean('is_system_default')->default(false)->after('is_active');
            $table->index('is_system_default');
        });
    }

    public function down(): void
    {
        Schema::table('system_prompts', function (Blueprint $table) {
            $table->dropIndex(['is_system_default']);
            $table->dropColumn('is_system_default');
        });
    }
};
