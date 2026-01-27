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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('stage')->nullable()->after('business_model');
            $table->string('team_size')->nullable()->after('stage');
            $table->string('timeframe')->nullable()->after('team_size');
            $table->text('additional_info')->nullable()->after('timeframe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['stage', 'team_size', 'timeframe', 'additional_info']);
        });
    }
};
