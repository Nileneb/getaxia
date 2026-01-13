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
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_user_id')->constrained('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->enum('business_model', ['b2b_saas', 'b2c', 'marketplace', 'agency', 'other'])->nullable();
            $table->integer('team_cofounders')->nullable();
            $table->integer('team_employees')->nullable();
            $table->string('user_position')->nullable();
            $table->text('customer_profile')->nullable();
            $table->text('market_insights')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};


