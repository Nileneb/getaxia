<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Create PostgreSQL ENUM types (with IF NOT EXISTS check)
        DB::statement("DO $$ BEGIN CREATE TYPE goal_priority AS ENUM ('high', 'medium', 'low'); EXCEPTION WHEN duplicate_object THEN null; END $$;");
        DB::statement("DO $$ BEGIN CREATE TYPE todo_source AS ENUM ('paste', 'csv'); EXCEPTION WHEN duplicate_object THEN null; END $$;");
        DB::statement("DO $$ BEGIN CREATE TYPE company_business_model AS ENUM ('b2b_saas', 'b2c', 'marketplace', 'agency', 'other'); EXCEPTION WHEN duplicate_object THEN null; END $$;");
        DB::statement("DO $$ BEGIN CREATE TYPE todo_status AS ENUM ('todo', 'in_progress', 'done', 'blocked', 'canceled'); EXCEPTION WHEN duplicate_object THEN null; END $$;");
        DB::statement("DO $$ BEGIN CREATE TYPE company_profile_type AS ENUM ('customer_profile', 'market_insights', 'positioning', 'domain_extract', 'competitive_analysis'); EXCEPTION WHEN duplicate_object THEN null; END $$;");
        DB::statement("DO $$ BEGIN CREATE TYPE company_profile_source AS ENUM ('ai_from_user_input', 'ai_from_domain', 'ai_mixed'); EXCEPTION WHEN duplicate_object THEN null; END $$;");

        // Convert goals.priority from VARCHAR to ENUM
        DB::statement("ALTER TABLE goals ALTER COLUMN priority DROP DEFAULT");
        DB::statement("ALTER TABLE goals ALTER COLUMN priority TYPE goal_priority USING priority::goal_priority");

        // Convert todos.source from VARCHAR to ENUM
        DB::statement("ALTER TABLE todos ALTER COLUMN source DROP DEFAULT");
        DB::statement("ALTER TABLE todos ALTER COLUMN source TYPE todo_source USING source::todo_source");
        DB::statement("ALTER TABLE todos ALTER COLUMN source SET DEFAULT 'paste'::todo_source");

        // Convert companies.business_model from VARCHAR to ENUM
        DB::statement("ALTER TABLE companies ALTER COLUMN business_model TYPE company_business_model USING business_model::company_business_model");

        // Convert todos.status from VARCHAR to ENUM (if exists)
        $statusExists = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'todos' AND column_name = 'status'");
        if (!empty($statusExists)) {
            DB::statement("ALTER TABLE todos ALTER COLUMN status DROP DEFAULT");
            DB::statement("ALTER TABLE todos ALTER COLUMN status TYPE todo_status USING status::todo_status");
            DB::statement("ALTER TABLE todos ALTER COLUMN status SET DEFAULT 'todo'::todo_status");
        }

        // Convert company_profiles enums
        DB::statement("ALTER TABLE company_profiles ALTER COLUMN profile_type TYPE company_profile_type USING profile_type::company_profile_type");
        DB::statement("ALTER TABLE company_profiles ALTER COLUMN source_type TYPE company_profile_source USING source_type::company_profile_source");
    }

    public function down(): void
    {
        // Revert columns to VARCHAR
        DB::statement("ALTER TABLE goals ALTER COLUMN priority TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE todos ALTER COLUMN source TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE companies ALTER COLUMN business_model TYPE VARCHAR(255)");

        $statusExists = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'todos' AND column_name = 'status'");
        if (!empty($statusExists)) {
            DB::statement("ALTER TABLE todos ALTER COLUMN status TYPE VARCHAR(32)");
        }

        DB::statement("ALTER TABLE company_profiles ALTER COLUMN profile_type TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE company_profiles ALTER COLUMN source_type TYPE VARCHAR(255)");

        // Drop ENUM types
        DB::statement("DROP TYPE IF EXISTS goal_priority");
        DB::statement("DROP TYPE IF EXISTS todo_source");
        DB::statement("DROP TYPE IF EXISTS company_business_model");
        DB::statement("DROP TYPE IF EXISTS todo_status");
        DB::statement("DROP TYPE IF EXISTS company_profile_type");
        DB::statement("DROP TYPE IF EXISTS company_profile_source");
    }
};
