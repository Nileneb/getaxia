<?php

use App\Models\Company;
use App\Models\Goal;
use App\Models\MissingTodo;
use App\Models\Run;
use App\Models\SystemPrompt;
use App\Models\Todo;
use App\Models\TodoEvaluation;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Set Langdock config for tests
    config([
        'services.langdock.api_key' => 'test-api-key',
        'services.langdock.base_url' => 'https://api.langdock.test/v1',
        'services.langdock.model' => 'gpt-4o',
    ]);
    
    // Create required SystemPrompt
    SystemPrompt::create([
        'type' => 'todo_analysis',
        'version' => 'v1.0',
        'is_active' => true,
        'is_system_default' => true,
        'temperature' => 0.7,
        'system_message' => 'You are an AI assistant.',
        'user_prompt_template' => 'Analyze these todos: {{todos_list}}',
    ]);
});

test('todo creation triggers AI analysis', function () {
    // Mock Langdock API response
    Http::fake([
        'https://api.langdock.test/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'overall_score' => 75,
                            'evaluations' => [
                                [
                                    'task_index' => 0,
                                    'color' => 'green',
                                    'score' => 85,
                                    'reasoning' => 'High impact on MRR',
                                    'priority_recommendation' => 'high',
                                    'action_recommendation' => 'keep',
                                ],
                                [
                                    'task_index' => 1,
                                    'color' => 'yellow',
                                    'score' => 55,
                                    'reasoning' => 'Medium impact, could be delegated',
                                    'priority_recommendation' => 'low',
                                    'action_recommendation' => 'delegate',
                                ],
                            ],
                            'missing_todos' => [
                                [
                                    'title' => 'Follow up with leads',
                                    'description' => 'Important for revenue',
                                    'impact_score' => 80,
                                ],
                            ],
                        ]),
                    ],
                ],
            ],
            'usage' => ['total_tokens' => 500],
        ], 200),
    ]);

    $user = User::factory()->create();
    $company = Company::create([
        'owner_user_id' => $user->id,
        'name' => 'Test Company',
    ]);

    $response = $this->actingAs($user)->postJson('/api/todos', [
        'todos' => [
            'Close deal with customer X',
            'Update website footer',
        ],
    ]);

    $response->assertStatus(201);
    
    // Check that Run was created
    expect(Run::count())->toBe(1);
    $run = Run::first();
    expect($run->user_id)->toBe($user->id)
        ->and($run->overall_score)->toBe(75);
    
    // Check that Todos were created
    expect(Todo::count())->toBe(2);
    
    // Check that Evaluations were created
    expect(TodoEvaluation::count())->toBe(2);
    
    $greenEval = TodoEvaluation::where('color', 'green')->first();
    expect($greenEval)->not->toBeNull()
        ->and($greenEval->score)->toBe(85)
        ->and($greenEval->reasoning)->toBe('High impact on MRR');
    
    // Check that MissingTodos were created
    expect(MissingTodo::count())->toBe(1);
    $missing = MissingTodo::first();
    expect($missing->title)->toBe('Follow up with leads');
});

test('todo creation stores todos even if AI fails', function () {
    // Mock Langdock API error
    Http::fake([
        'https://api.langdock.test/*' => Http::response('Internal Server Error', 500),
    ]);

    $user = User::factory()->create();
    $company = Company::create([
        'owner_user_id' => $user->id,
        'name' => 'Test Company',
    ]);

    $response = $this->actingAs($user)->postJson('/api/todos', [
        'todos' => ['Important task'],
    ]);

    // Should return error
    $response->assertStatus(500);
    
    // Todos should NOT be persisted due to transaction rollback
    expect(Todo::count())->toBe(0);
});

test('todo creation validates input', function () {
    $user = User::factory()->create();
    Company::create(['owner_user_id' => $user->id]);

    // Empty todos array
    $response = $this->actingAs($user)->postJson('/api/todos', [
        'todos' => [],
    ]);
    $response->assertStatus(422);

    // Missing todos field
    $response = $this->actingAs($user)->postJson('/api/todos', []);
    $response->assertStatus(422);

    // Invalid todo type
    $response = $this->actingAs($user)->postJson('/api/todos', [
        'todos' => [123, 456],
    ]);
    $response->assertStatus(422);
});

test('todo creation links evaluations to matching goals', function () {
    Http::fake([
        'https://api.langdock.test/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'overall_score' => 80,
                            'evaluations' => [
                                [
                                    'task_index' => 0,
                                    'color' => 'green',
                                    'score' => 90,
                                    'reasoning' => 'Directly impacts MRR goal',
                                    'goal_title' => 'Increase MRR',
                                ],
                            ],
                            'missing_todos' => [],
                        ]),
                    ],
                ],
            ],
            'usage' => ['total_tokens' => 200],
        ], 200),
    ]);

    $user = User::factory()->create();
    $company = Company::create([
        'owner_user_id' => $user->id,
        'name' => 'Test Company',
    ]);
    
    $goal = Goal::create([
        'company_id' => $company->id,
        'title' => 'Increase MRR',
        'priority' => 'high',
    ]);

    $response = $this->actingAs($user)->postJson('/api/todos', [
        'todos' => ['Close enterprise deal'],
    ]);

    $response->assertStatus(201);
    
    $evaluation = TodoEvaluation::first();
    expect($evaluation->primary_goal_id)->toBe($goal->id);
});

test('batch todo creation works without AI analysis', function () {
    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id]);
    $run = Run::create(['company_id' => $company->id, 'user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/api/todos/batch', [
        'run_id' => $run->id,
        'todos' => [
            ['title' => 'Task 1'],
            ['title' => 'Task 2'],
            ['title' => 'Task 3'],
        ],
    ]);

    $response->assertStatus(201);
    expect(Todo::count())->toBe(3);
    
    // No AI analysis = no evaluations
    expect(TodoEvaluation::count())->toBe(0);
});

test('batch todo creation validates run ownership', function () {
    $user1 = User::factory()->create();
    $company1 = Company::create(['owner_user_id' => $user1->id]);
    $run1 = Run::create(['company_id' => $company1->id, 'user_id' => $user1->id]);

    $user2 = User::factory()->create();

    // User2 tries to add todos to User1's run
    $response = $this->actingAs($user2)->postJson('/api/todos/batch', [
        'run_id' => $run1->id,
        'todos' => [['title' => 'Sneaky task']],
    ]);

    $response->assertStatus(403);
});
