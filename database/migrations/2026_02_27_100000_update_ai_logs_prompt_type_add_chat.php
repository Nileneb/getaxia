<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add 'chat' to the prompt_type enum in ai_logs table.
     * Required for logging chat interactions via Langdock API.
     */
    public function up(): void
    {
        // For SQLite (used in dev/testing), we need to recreate the column
        // For PostgreSQL/MySQL, we can alter the enum directly
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't enforce enum constraints, but we update the column type
            // The column already accepts any string in SQLite
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE ai_logs DROP CONSTRAINT IF EXISTS ai_logs_prompt_type_check");
            DB::statement("ALTER TABLE ai_logs ADD CONSTRAINT ai_logs_prompt_type_check CHECK (prompt_type::text = ANY (ARRAY['todo_analysis'::text, 'company_extraction'::text, 'goals_extraction'::text, 'chat'::text]))");
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE ai_logs MODIFY prompt_type ENUM('todo_analysis', 'company_extraction', 'goals_extraction', 'chat')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE ai_logs DROP CONSTRAINT IF EXISTS ai_logs_prompt_type_check");
            DB::statement("ALTER TABLE ai_logs ADD CONSTRAINT ai_logs_prompt_type_check CHECK (prompt_type::text = ANY (ARRAY['todo_analysis'::text, 'company_extraction'::text, 'goals_extraction'::text]))");
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE ai_logs MODIFY prompt_type ENUM('todo_analysis', 'company_extraction', 'goals_extraction')");
        }
    }
};
