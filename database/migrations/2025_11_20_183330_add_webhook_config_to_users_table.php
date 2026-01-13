<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('n8n_webhook_url')->nullable()->after('email');
            $table->json('webhook_config')->nullable()->after('n8n_webhook_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['n8n_webhook_url', 'webhook_config']);
        });
    }
};
