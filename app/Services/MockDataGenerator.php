<?php

namespace App\Services;

class MockDataGenerator
{
    /**
     * Generate mock company data for testing
     */
    public function generateMockCompany(): array
    {
        return [
            'name' => 'Acme SaaS Inc',
            'business_model' => 'b2b_saas',
            'team_cofounders' => 2,
            'team_employees' => 8,
            'user_position' => 'CEO',
            'top_kpi' => [
                'name' => 'Monthly Recurring Revenue',
                'current' => 5000,
                'target' => 50000,
                'unit' => '€',
            ],
            'goals' => [
                [
                    'title' => 'Reach product-market fit',
                    'priority' => 'high',
                    'time_frame' => 'Q2 2024',
                    'kpis' => [
                        ['name' => 'Monthly Active Users', 'current' => 100, 'target' => 1000, 'unit' => 'users'],
                        ['name' => 'Retention Rate', 'current' => 40, 'target' => 70, 'unit' => '%'],
                    ],
                ],
                [
                    'title' => 'Build strong team',
                    'priority' => 'high',
                    'time_frame' => 'Q1 2024',
                    'kpis' => [
                        ['name' => 'Senior Engineers', 'current' => 1, 'target' => 3, 'unit' => 'people'],
                    ],
                ],
                [
                    'title' => 'Secure funding',
                    'priority' => 'medium',
                    'time_frame' => 'Q3 2024',
                    'kpis' => [
                        ['name' => 'Seed Round', 'current' => 0, 'target' => 500000, 'unit' => '€'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Generate mock todos for testing
     */
    public function generateMockTodos(): array
    {
        return [
            // High impact
            'Close deal with Enterprise customer worth 10k MRR',
            'Ship new onboarding flow that increases activation by 30%',
            'Hire senior backend engineer',
            
            // Medium impact
            'Prepare investor update for Q1',
            'Set up analytics dashboard for team',
            'Review and improve sales process',
            
            // Low impact / Busywork
            'Update internal wiki documentation',
            'Attend industry networking event',
            'Post company update on LinkedIn',
            'Fix minor UI bug in admin panel',
            'Organize team lunch',
            'Reply to recruiter emails',
        ];
    }

    /**
     * Generate mock context object
     */
    public function generateMockContext(): object
    {
        $data = $this->generateMockCompany();
        
        return (object) [
            'company' => (object) $data,
            'top_kpi' => (object) $data['top_kpi'],
            'goals' => collect($data['goals'])->map(fn($g) => (object) $g),
            'todos' => $this->generateMockTodos(),
        ];
    }
}

