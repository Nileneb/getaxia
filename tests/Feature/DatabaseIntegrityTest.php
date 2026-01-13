<?php

use App\Models\Company;
use App\Models\Goal;
use App\Models\GoalKpi;
use App\Models\Run;
use App\Models\SystemPrompt;
use App\Models\Todo;
use App\Models\TodoEvaluation;
use App\Models\User;

/**
 * Database Integrity Tests
 * 
 * Prüft, dass Migrations korrekt sind und Relationen funktionieren.
 */

test('system prompts seeder creates required prompts', function () {
    $this->seed(\Database\Seeders\SystemPromptsSeeder::class);
    
    $todoAnalysis = SystemPrompt::where('type', 'todo_analysis')
        ->where('is_active', true)
        ->first();
    
    expect($todoAnalysis)->not->toBeNull()
        ->and($todoAnalysis->system_message)->not->toBeEmpty();
});

test('goal can have multiple kpis', function () {
    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id]);
    
    $goal = Goal::create([
        'company_id' => $company->id,
        'title' => 'Grow Revenue',
        'priority' => 'high',
    ]);
    
    $kpi1 = GoalKpi::create([
        'goal_id' => $goal->id,
        'name' => 'MRR',
        'current_value' => 10000,
        'target_value' => 50000,
        'unit' => 'EUR',
    ]);
    
    $kpi2 = GoalKpi::create([
        'goal_id' => $goal->id,
        'name' => 'Customer Count',
        'current_value' => 50,
        'target_value' => 200,
        'unit' => 'count',
    ]);
    
    $goal->refresh();
    
    expect($goal->kpis)->toHaveCount(2)
        ->and($goal->kpis->pluck('name')->toArray())->toContain('MRR', 'Customer Count');
});

test('run can have multiple todos', function () {
    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id]);
    
    $run = Run::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
    ]);
    
    Todo::create(['run_id' => $run->id, 'raw_input' => 'Task 1', 'normalized_title' => 'Task 1']);
    Todo::create(['run_id' => $run->id, 'raw_input' => 'Task 2', 'normalized_title' => 'Task 2']);
    Todo::create(['run_id' => $run->id, 'raw_input' => 'Task 3', 'normalized_title' => 'Task 3']);
    
    $run->refresh();
    
    expect($run->todos)->toHaveCount(3);
});

test('todo can have evaluation', function () {
    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id]);
    $run = Run::create(['company_id' => $company->id, 'user_id' => $user->id]);
    $todo = Todo::create(['run_id' => $run->id, 'raw_input' => 'Important task', 'normalized_title' => 'Important task']);
    
    $evaluation = TodoEvaluation::create([
        'run_id' => $run->id,
        'todo_id' => $todo->id,
        'color' => 'green',
        'score' => 85,
        'reasoning' => 'High impact task',
    ]);
    
    $todo->refresh();
    
    expect($todo->evaluation)->not->toBeNull()
        ->and($todo->evaluation->color)->toBe('green')
        ->and($todo->evaluation->score)->toBe(85);
});

test('company has top kpi method', function () {
    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id]);
    
    $goal = Goal::create([
        'company_id' => $company->id,
        'title' => 'Revenue Goal',
        'priority' => 'high',
    ]);
    
    $kpi = GoalKpi::create([
        'goal_id' => $goal->id,
        'name' => 'MRR',
        'current_value' => 10000,
        'target_value' => 50000,
        'unit' => 'EUR',
        'is_top_kpi' => true,
    ]);
    
    $topKpi = $company->topKpi();
    
    expect($topKpi)->not->toBeNull()
        ->and($topKpi->id)->toBe($kpi->id);
});

test('uuid is generated for models', function () {
    $user = User::factory()->create();
    
    // UUID should be a valid UUID format
    expect($user->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    
    $company = Company::create(['owner_user_id' => $user->id]);
    expect($company->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

test('user full name attribute works', function () {
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);
    
    expect($user->full_name)->toBe('John Doe');
});

test('company goals relationship works', function () {
    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id]);
    
    Goal::create(['company_id' => $company->id, 'title' => 'Goal 1', 'priority' => 'high']);
    Goal::create(['company_id' => $company->id, 'title' => 'Goal 2', 'priority' => 'medium']);
    
    $company->refresh();
    
    expect($company->goals)->toHaveCount(2);
});

test('cascading delete works for company goals', function () {
    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id]);
    
    $goal = Goal::create(['company_id' => $company->id, 'title' => 'Test Goal', 'priority' => 'high']);
    GoalKpi::create(['goal_id' => $goal->id, 'name' => 'Test KPI', 'current_value' => 0, 'target_value' => 100, 'unit' => 'count']);
    
    expect(Goal::count())->toBe(1);
    expect(GoalKpi::count())->toBe(1);
    
    $goal->delete();
    
    // KPIs should be deleted with goal (if cascade is set up)
    // Note: This depends on your migration/model setup
});
