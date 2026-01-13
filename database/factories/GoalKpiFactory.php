<?php

namespace Database\Factories;

use App\Models\GoalKpi;
use App\Models\Goal;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoalKpiFactory extends Factory
{
    protected $model = GoalKpi::class;

    public function definition(): array
    {
        $currentValue = fake()->randomFloat(2, 0, 10000);
        $targetValue = $currentValue * fake()->randomFloat(2, 1.1, 2.5);

        return [
            'goal_id' => Goal::factory(),
            'company_id' => null,
            'name' => fake()->randomElement(['MRR', 'ARR', 'Churn Rate', 'CAC', 'LTV', 'Conversion Rate', 'Active Users']),
            'current_value' => $currentValue,
            'target_value' => $targetValue,
            'unit' => fake()->randomElement(['€', '%', 'users', 'deals', 'leads']),
            'time_frame' => fake()->randomElement(['Q1 2025', 'Q2 2025', 'monthly', 'yearly']),
            'is_top_kpi' => fake()->boolean(30),
            'original_smart_text' => fake()->optional()->sentence(),
            'extracted_from_text' => fake()->boolean(30),
            'additional_information' => fake()->optional()->sentence(),
        ];
    }

    public function forGoal(Goal $goal): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_id' => $goal->id,
            'company_id' => null,
        ]);
    }

    public function standalone(): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_id' => null,
            'company_id' => Company::factory(),
        ]);
    }

    public function topKpi(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_top_kpi' => true,
        ]);
    }
}
