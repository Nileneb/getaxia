<?php

namespace Database\Factories;

use App\Models\AiLog;
use App\Models\Run;
use App\Models\SystemPrompt;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiLogFactory extends Factory
{
    protected $model = AiLog::class;

    public function definition(): array
    {
        $success = fake()->boolean(90);

        return [
            'run_id' => fake()->optional()->randomElement([null, Run::factory()]),
            'prompt_type' => fake()->randomElement(['todo_analysis', 'company_extraction', 'goals_extraction']),
            'system_prompt_id' => fake()->optional()->randomElement([null, SystemPrompt::factory()]),
            'input_context' => [
                'user_input' => fake()->sentence(),
                'context' => fake()->paragraph(),
            ],
            'response' => $success ? [
                'result' => fake()->paragraph(),
                'confidence' => fake()->randomFloat(2, 0.5, 1.0),
            ] : null,
            'tokens_used' => $success ? fake()->numberBetween(100, 5000) : null,
            'duration_ms' => fake()->numberBetween(500, 15000),
            'success' => $success,
            'error_message' => $success ? null : fake()->sentence(),
        ];
    }

    public function forRun(Run $run): static
    {
        return $this->state(fn (array $attributes) => [
            'run_id' => $run->id,
        ]);
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'success' => true,
            'error_message' => null,
            'tokens_used' => fake()->numberBetween(100, 5000),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'success' => false,
            'error_message' => fake()->sentence(),
            'tokens_used' => null,
            'response' => null,
        ]);
    }

    public function todoAnalysis(): static
    {
        return $this->state(fn (array $attributes) => [
            'prompt_type' => 'todo_analysis',
        ]);
    }
}
