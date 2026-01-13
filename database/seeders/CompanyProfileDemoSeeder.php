<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;

class CompanyProfileDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Create a demo company
        $company = Company::factory()->create([
            'name' => 'Demo Company Inc',
            'website' => 'https://democompany.com',
            'user_description' => 'User-provided company description',
        ]);

        // AI from user input (higher priority than domain)
        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'customer_profile',
            'source_type' => 'ai_from_user_input',
            'raw_text' => 'AI-generated customer profile based on user input',
            'ai_extracted_json' => [
                'target_segments' => ['SMBs', 'Enterprises'],
                'pain_points' => ['Efficiency', 'Cost reduction'],
            ],
        ]);

        // AI from domain (lower priority)
        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'customer_profile',
            'source_type' => 'ai_from_domain',
            'raw_text' => 'AI-extracted customer profile from domain analysis',
            'ai_extracted_json' => [
                'target_segments' => ['Tech companies'],
            ],
        ]);

        // Market insights
        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'market_insights',
            'source_type' => 'ai_from_domain',
            'raw_text' => 'Market analysis shows growing demand in SaaS sector',
            'ai_extracted_json' => [
                'market_size' => '$500M',
                'growth_rate' => '15% YoY',
            ],
        ]);

        // Positioning
        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'positioning',
            'source_type' => 'ai_mixed',
            'raw_text' => 'Position as premium solution for enterprise clients',
            'ai_extracted_json' => [
                'value_prop' => 'Enterprise-grade efficiency',
                'differentiators' => ['Security', 'Scalability'],
            ],
        ]);

        $this->command->info("Demo company created with ID: {$company->id}");
        $this->command->info('Company has 4 profile entries demonstrating prioritization');
    }
}
