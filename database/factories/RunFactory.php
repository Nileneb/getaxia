<?php

namespace Database\Factories;

use App\Models\Run;
use App\Models\Company;
use App\Models\User;
use App\Models\GoalKpi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class RunFactory extends Factory
{
    protected $model = Run::class;

    public function definition(): array
    {
        $periodStart = Carbon::now()->subDays(fake()->numberBetween(7, 30));
        $periodEnd = (clone $periodStart)->addDays(7);

        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'snapshot_top_kpi_id' => null,
            'overall_score' => fake()->numberBetween(0, 100),
            'summary_text' => fake()->paragraph(),
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function withTopKpi(): static
    {
        return $this->state(fn (array $attributes) => [
            'snapshot_top_kpi_id' => GoalKpi::factory()->topKpi(),
        ]);
    }

    public function thisWeek(): static
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        return $this->state(fn (array $attributes) => [
            'period_start' => $start->format('Y-m-d'),
            'period_end' => $end->format('Y-m-d'),
        ]);
    }
}
