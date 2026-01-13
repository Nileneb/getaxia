<?php

namespace Database\Factories;

use App\Models\TodoEvaluation;
use App\Models\Run;
use App\Models\Todo;
use App\Models\Goal;
use App\Models\GoalKpi;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoEvaluationFactory extends Factory
{
    protected $model = TodoEvaluation::class;

    public function definition(): array
    {
        return [
            'run_id' => Run::factory(),
            'todo_id' => Todo::factory(),
            'color' => fake()->randomElement(['green', 'yellow', 'orange']),
            'score' => fake()->numberBetween(0, 100),
            'reasoning' => fake()->paragraph(),
            'priority_recommendation' => fake()->optional()->randomElement(['high', 'low', 'none']),
            'action_recommendation' => fake()->optional()->randomElement(['keep', 'delegate', 'drop']),
            'delegation_target_role' => fake()->optional()->jobTitle(),
            'primary_goal_id' => fake()->optional()->randomElement([null, Goal::factory()]),
            'primary_kpi_id' => fake()->optional()->randomElement([null, GoalKpi::factory()]),
        ];
    }

    public function forRun(Run $run): static
    {
        return $this->state(fn (array $attributes) => [
            'run_id' => $run->id,
        ]);
    }

    public function forTodo(Todo $todo): static
    {
        return $this->state(fn (array $attributes) => [
            'todo_id' => $todo->id,
        ]);
    }

    public function green(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => 'green',
            'score' => fake()->numberBetween(80, 100),
            'action_recommendation' => 'keep',
        ]);
    }

    public function yellow(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => 'yellow',
            'score' => fake()->numberBetween(50, 79),
        ]);
    }

    public function orange(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => 'orange',
            'score' => fake()->numberBetween(0, 49),
            'action_recommendation' => fake()->randomElement(['delegate', 'drop']),
        ]);
    }
}
