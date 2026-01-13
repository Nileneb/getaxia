<?php

namespace Database\Factories;

use App\Models\MissingTodo;
use App\Models\Run;
use App\Models\Goal;
use App\Models\GoalKpi;
use Illuminate\Database\Eloquent\Factories\Factory;

class MissingTodoFactory extends Factory
{
    protected $model = MissingTodo::class;

    public function definition(): array
    {
        return [
            'run_id' => Run::factory(),
            'goal_id' => fake()->optional()->randomElement([null, Goal::factory()]),
            'kpi_id' => fake()->optional()->randomElement([null, GoalKpi::factory()]),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['hiring', 'prioritization', 'delegation', 'culture', 'other']),
            'impact_score' => fake()->optional()->numberBetween(1, 100),
            'suggested_owner_role' => fake()->optional()->jobTitle(),
        ];
    }

    public function forRun(Run $run): static
    {
        return $this->state(fn (array $attributes) => [
            'run_id' => $run->id,
        ]);
    }

    public function forGoal(Goal $goal): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_id' => $goal->id,
        ]);
    }

    public function forKpi(GoalKpi $kpi): static
    {
        return $this->state(fn (array $attributes) => [
            'kpi_id' => $kpi->id,
        ]);
    }

    public function hiring(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'hiring',
            'suggested_owner_role' => 'HR Manager',
        ]);
    }

    public function highImpact(): static
    {
        return $this->state(fn (array $attributes) => [
            'impact_score' => fake()->numberBetween(80, 100),
        ]);
    }
}
