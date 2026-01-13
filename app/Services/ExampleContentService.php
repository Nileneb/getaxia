<?php

namespace App\Services;

class ExampleContentService
{
    /**
     * Get example company information texts
     */
    public static function getCompanyExamples(): array
    {
        return [
            [
                'label' => 'B2B SaaS Startup',
                'keywords' => 'SaaS, B2B, Enterprise',
                'content' => 'We are a B2B SaaS company building a project management tool for remote teams. Founded in 2024, we currently have 2 co-founders and 8 employees. Our target customers are mid-size tech companies (50-500 employees) who struggle with team coordination. The market is competitive but growing rapidly, with remote work becoming the norm. We differentiate through AI-powered task prioritization and seamless integrations.',
            ],
            [
                'label' => 'E-Commerce Marketplace',
                'keywords' => 'Marketplace, E-Commerce, Consumer',
                'content' => 'We operate an online marketplace connecting local artisans with customers. Launched in 2023, we have 3 co-founders and 15 employees. Our customers are creative individuals and small businesses looking for unique, handmade products. The market is fragmented but has strong growth potential as consumers seek more personalized products. Our competitive advantage is our curated selection and strong community focus.',
            ],
            [
                'label' => 'Agency / Service Business',
                'keywords' => 'Agency, Services, Consulting',
                'content' => 'We are a digital marketing agency specializing in growth marketing for early-stage startups. Founded in 2022, we have 1 founder and 12 employees. Our clients are seed to Series A startups who need help with customer acquisition and retention. The market is crowded but we focus on data-driven results and transparent reporting. Our unique value is our founder-to-founder approach and proven track record.',
            ],
        ];
    }

    /**
     * Get example goals and KPIs texts
     */
    public static function getGoalsExamples(): array
    {
        return [
            [
                'label' => 'Revenue Focus',
                'keywords' => 'Revenue, MRR, Growth',
                'content' => 'Our primary goal is to reach 50k MRR by Q2 2024. Currently at 15k MRR, we need to close 7 more enterprise deals. Secondary goals include improving customer retention from 85% to 95% and reducing churn rate from 5% to 2%. We also want to hire 3 senior engineers to increase development velocity by 50%.',
            ],
            [
                'label' => 'User Growth',
                'keywords' => 'Users, Activation, PMF',
                'content' => 'Our main objective is to reach product-market fit with 10,000 active users by end of Q1. Currently at 2,500 users, we need to improve activation rate from 30% to 60% and increase weekly active users from 40% to 70%. We also aim to reduce customer acquisition cost from 50€ to 25€ and increase referral rate from 5% to 20%.',
            ],
            [
                'label' => 'Team & Operations',
                'keywords' => 'Hiring, Efficiency, Scale',
                'content' => 'We need to build a strong team foundation. Goal 1: Hire 2 senior engineers and 1 product manager by March. Goal 2: Improve team velocity by implementing better processes - target 2x output with same team size. Goal 3: Reduce time-to-market for new features from 6 weeks to 3 weeks. Goal 4: Achieve 90% customer satisfaction score.',
            ],
        ];
    }

    /**
     * Get example todo lists
     */
    public static function getTodoExamples(): array
    {
        return [
            [
                'label' => 'Sales & Growth',
                'keywords' => 'Sales, Customers, Revenue',
                'content' => "Close enterprise deal with TechCorp (50k ARR)
Follow up with 5 warm leads from last week
Prepare investor deck for Series A pitch
Hire senior sales rep
Update pricing page with new tiers
Review Q1 metrics with team
Schedule customer interviews (3 this week)",
            ],
            [
                'label' => 'Product & Development',
                'keywords' => 'Product, Features, Engineering',
                'content' => "Ship new onboarding flow (increases activation)
Fix critical bug in payment system
Design new dashboard UI
Write API documentation
Set up analytics dashboard
Code review for 3 PRs
Plan Q2 product roadmap
Hire backend engineer",
            ],
            [
                'label' => 'Operations & Team',
                'keywords' => 'Operations, Team, Admin',
                'content' => "Update internal wiki documentation
Set up new employee onboarding process
Review and approve expense reports
Plan team offsite event
Update company handbook
Schedule 1-on-1s with team (5 people)
Renew office lease
Attend industry networking event",
            ],
        ];
    }
}

