<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->enum('profile_type', ['customer_profile', 'market_insights', 'positioning', 'domain_extract', 'competitive_analysis']);
            $table->enum('source_type', ['ai_from_user_input', 'ai_from_domain', 'ai_mixed']);
            $table->longText('raw_text')->nullable();
            $table->json('ai_extracted_json')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'profile_type']);
            $table->index(['company_id', 'profile_type', 'source_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
