<?php

namespace Database\Factories;

use App\Models\AgentSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AgentSessionFactory extends Factory
{
    protected $model = AgentSession::class;

    public function definition(): array
    {
        return [
            'session_id' => Str::uuid(),
            'user_id' => User::factory(),
            'mode' => fake()->randomElement(['chat', 'workflow', 'analysis']),
            'workflow_key' => fake()->optional()->randomElement(['onboarding', 'goal_setting', 'todo_analysis']),
            'meta' => fake()->optional()->randomElement([
                null,
                ['step' => fake()->numberBetween(1, 5)],
                ['context' => fake()->word()],
            ]),
            'expires_at' => Carbon::now()->addHours(fake()->numberBetween(1, 24)),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function chatMode(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => 'chat',
            'workflow_key' => null,
        ]);
    }

    public function workflowMode(string $workflowKey): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => 'workflow',
            'workflow_key' => $workflowKey,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => Carbon::now()->subHours(1),
        ]);
    }
}
