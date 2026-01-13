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
        // Add fields to companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->text('original_smart_text')->nullable()->after('website');
            $table->boolean('extracted_from_text')->default(false)->after('original_smart_text');
            $table->text('additional_information')->nullable()->after('extracted_from_text');
        });

        // Add fields to goals table
        Schema::table('goals', function (Blueprint $table) {
            $table->text('original_smart_text')->nullable()->after('is_active');
            $table->boolean('extracted_from_text')->default(false)->after('original_smart_text');
            $table->text('additional_information')->nullable()->after('extracted_from_text');
        });

        // Add fields to goal_kpis table
        Schema::table('goal_kpis', function (Blueprint $table) {
            $table->text('original_smart_text')->nullable()->after('is_top_kpi');
            $table->boolean('extracted_from_text')->default(false)->after('original_smart_text');
            $table->text('additional_information')->nullable()->after('extracted_from_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['original_smart_text', 'extracted_from_text', 'additional_information']);
        });

        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn(['original_smart_text', 'extracted_from_text', 'additional_information']);
        });

        Schema::table('goal_kpis', function (Blueprint $table) {
            $table->dropColumn(['original_smart_text', 'extracted_from_text', 'additional_information']);
        });
    }
};
