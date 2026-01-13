<?php

namespace Database\Factories;

use App\Models\SystemPrompt;
use Illuminate\Database\Eloquent\Factories\Factory;

class SystemPromptFactory extends Factory
{
    protected $model = SystemPrompt::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['todo_analysis', 'company_extraction', 'goals_extraction']),
            'system_message' => fake()->paragraph(),
            'user_prompt_template' => fake()->paragraph(),
            'temperature' => fake()->randomFloat(1, 0.0, 2.0),
            'is_active' => fake()->boolean(70),
            'version' => 'v' . fake()->numberBetween(1, 5) . '.' . fake()->numberBetween(0, 9),
        ];
    }

    public function todoAnalysis(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'todo_analysis',
            'system_message' => 'You are an AI assistant analyzing todo items...',
            'user_prompt_template' => 'Analyze these todos: {todos}',
        ]);
    }

    public function companyExtraction(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'company_extraction',
            'system_message' => 'You are an AI assistant extracting company information...',
            'user_prompt_template' => 'Extract company info from: {text}',
        ]);
    }

    public function goalsExtraction(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'goals_extraction',
            'system_message' => 'You are an AI assistant extracting goals and KPIs...',
            'user_prompt_template' => 'Extract goals from: {text}',
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
