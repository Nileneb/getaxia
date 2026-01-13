<?php

namespace Database\Factories;

use App\Models\Todo;
use App\Models\Run;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoFactory extends Factory
{
    protected $model = Todo::class;

    public function definition(): array
    {
        return [
            'run_id' => Run::factory(),
            'raw_input' => fake()->sentence(),
            'normalized_title' => fake()->sentence(),
            'owner' => fake()->optional()->name(),
            'due_date' => fake()->optional()->date(),
            'source' => fake()->randomElement(['paste', 'csv']),
            'position' => fake()->numberBetween(1, 50),
        ];
    }

    public function forRun(Run $run): static
    {
        return $this->state(fn (array $attributes) => [
            'run_id' => $run->id,
        ]);
    }

    public function withOwner(string $owner): static
    {
        return $this->state(fn (array $attributes) => [
            'owner' => $owner,
        ]);
    }

    public function withDueDate(?string $date = null): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $date ?? fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
        ]);
    }

    public function fromPaste(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'paste',
        ]);
    }

    public function fromCsv(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'csv',
        ]);
    }
}
