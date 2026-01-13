<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoalFactory extends Factory
{
    protected $model = Goal::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['high', 'medium', 'low']),
            'time_frame' => fake()->randomElement(['Q1 2025', 'Q2 2025', 'H1 2025', '2025', '6 months', '12 months']),
            'is_active' => fake()->boolean(80),
            'original_smart_text' => fake()->optional()->paragraphs(2, true),
            'extracted_from_text' => fake()->boolean(30),
            'additional_information' => fake()->optional()->sentence(),
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
            'is_active' => true,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
