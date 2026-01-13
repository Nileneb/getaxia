<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WebhookPreset;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebhookPresetFactory extends Factory
{
    protected $model = WebhookPreset::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'webhook_url' => 'https://n8n.example.com/webhook/' . fake()->uuid(),
            'description' => fake()->optional()->sentence(),
            'is_active' => false,
            'is_default' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
