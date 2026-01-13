<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Goal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'info@getaxia.de',
            'password' => Hash::make('password'), // Change in production!
            'email_verified_at' => now(),
        ]);

        // Create admin's company
        $adminCompany = Company::create([
            'owner_user_id' => $admin->id,
            'name' => 'Axia GmbH',
            'business_model' => 'b2b_saas',
            'team_cofounders' => 2,
            'team_employees' => 5,
            'user_position' => 'CEO',
            'customer_profile' => 'SMEs and startups looking for goal management solutions',
            'website' => 'https://www.getaxia.de',
        ]);

        // Create sample goal for admin
        Goal::create([
            'company_id' => $adminCompany->id,
            'title' => 'Increase MRR by 50%',
            'description' => 'Grow monthly recurring revenue from €10k to €15k',
            'priority' => 'high',
            'time_frame' => '6 months',
            'is_active' => true,
        ]);

        // Example User 1
        $user1 = User::create([
            'first_name' => 'Sarah',
            'last_name' => 'Mueller',
            'email' => 'sarah@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $company1 = Company::create([
            'owner_user_id' => $user1->id,
            'name' => 'TechStart Berlin',
            'business_model' => 'b2b_saas',
            'team_cofounders' => 2,
            'team_employees' => 3,
            'user_position' => 'CTO',
            'customer_profile' => 'Tech-forward companies seeking sustainable solutions',
            'website' => 'https://techstart-berlin.example.com',
        ]);

        Goal::create([
            'company_id' => $company1->id,
            'title' => 'Launch MVP',
            'description' => 'Complete and launch minimum viable product for beta testing',
            'priority' => 'high',
            'time_frame' => '3 months',
            'is_active' => true,
        ]);

        Goal::create([
            'company_id' => $company1->id,
            'title' => 'Reach 100 Beta Users',
            'description' => 'Acquire first 100 active beta testers for product validation',
            'priority' => 'medium',
            'time_frame' => '4 months',
            'is_active' => true,
        ]);

        // Example User 2
        $user2 = User::create([
            'first_name' => 'Michael',
            'last_name' => 'Schmidt',
            'email' => 'michael@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $company2 = Company::create([
            'owner_user_id' => $user2->id,
            'name' => 'Schmidt Consulting',
            'business_model' => 'agency',
            'team_cofounders' => 1,
            'team_employees' => 2,
            'user_position' => 'Founder',
            'customer_profile' => 'SME business owners seeking strategic guidance',
            'website' => 'https://schmidt-consulting.example.com',
        ]);

        Goal::create([
            'company_id' => $company2->id,
            'title' => 'Onboard 5 New Clients',
            'description' => 'Acquire 5 new consulting clients with €5k+ contracts',
            'priority' => 'high',
            'time_frame' => '2 months',
            'is_active' => true,
        ]);

        Goal::create([
            'company_id' => $company2->id,
            'title' => 'Develop Training Program',
            'description' => 'Create comprehensive leadership training program for clients',
            'priority' => 'medium',
            'time_frame' => '5 months',
            'is_active' => false,
        ]);

        // Seed System Prompts (protected)
        $this->call(SystemPromptsSeeder::class);
    }
}

