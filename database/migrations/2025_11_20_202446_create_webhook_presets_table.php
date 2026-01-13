<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_presets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('webhook_url');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Only one active webhook per user (partial unique index)
        DB::statement('CREATE UNIQUE INDEX unique_active_per_user ON webhook_presets (user_id) WHERE is_active = true');
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_presets');
    }
};
