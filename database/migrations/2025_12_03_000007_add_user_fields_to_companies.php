<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // User-provided fields for prioritization
            $table->text('user_description')->nullable()->after('additional_information');
            $table->text('user_target_customers')->nullable()->after('user_description');
            $table->text('user_market_info')->nullable()->after('user_target_customers');
            $table->text('user_positioning')->nullable()->after('user_market_info');
            $table->text('user_competitive_notes')->nullable()->after('user_positioning');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'user_description',
                'user_target_customers',
                'user_market_info',
                'user_positioning',
                'user_competitive_notes',
            ]);
        });
    }
};
