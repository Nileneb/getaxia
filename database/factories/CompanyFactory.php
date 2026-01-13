<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'owner_user_id' => User::factory(),
            'name' => fake()->company(),
            'business_model' => fake()->randomElement(['b2b_saas', 'b2c', 'marketplace', 'agency', 'other']),
            'team_cofounders' => fake()->numberBetween(1, 5),
            'team_employees' => fake()->numberBetween(0, 50),
            'user_position' => fake()->randomElement(['CEO', 'CTO', 'COO', 'Product Manager', 'Founder']),
            'customer_profile' => fake()->paragraph(),
            'market_insights' => fake()->paragraph(),
            'website' => fake()->optional()->url(),
            'original_smart_text' => fake()->optional()->paragraphs(3, true),
            'extracted_from_text' => fake()->boolean(30),
            'additional_information' => fake()->optional()->sentence(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_user_id' => $user->id,
        ]);
    }

    public function b2bSaas(): static
    {
        return $this->state(fn (array $attributes) => [
            'business_model' => 'b2b_saas',
        ]);
    }

    public function withSmartText(): static
    {
        return $this->state(fn (array $attributes) => [
            'original_smart_text' => fake()->paragraphs(3, true),
            'extracted_from_text' => true,
        ]);
    }
}
