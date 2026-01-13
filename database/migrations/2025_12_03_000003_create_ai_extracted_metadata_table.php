<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_extracted_metadata', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Polymorphic relation to any entity
            $table->string('entity_type');
            $table->uuid('entity_id');
            // Original unprocessed text
            $table->longText('raw_text')->nullable();
            // JSON with extracted structured data
            $table->json('extracted_data_json')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_extracted_metadata');
    }
};
