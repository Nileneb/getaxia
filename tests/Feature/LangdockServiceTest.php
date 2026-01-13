<?php

use App\Models\Company;
use App\Models\Goal;
use App\Models\Run;
use App\Models\SystemPrompt;
use App\Models\Todo;
use App\Models\User;
use App\Services\WebhookAiService;
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

test('langdock service sends correct request headers', function () {
    Http::fake([
        'api.langdock.test/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'overall_score' => 75,
                            'evaluations' => [],
                            'missing_todos' => [],
                        ]),
                    ],
                ],
            ],
            'usage' => [
                'total_tokens' => 150,
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id, 'name' => 'Test Co']);
    $run = Run::create(['company_id' => $company->id, 'user_id' => $user->id]);
    $todo = Todo::create(['run_id' => $run->id, 'raw_input' => 'Test todo', 'normalized_title' => 'Test todo']);

    $service = new WebhookAiService($user);
    $result = $service->analyzeTodos($run, collect([$todo]), $company);

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer test-api-key')
            && $request->hasHeader('Content-Type', 'application/json')
            && str_contains($request->url(), 'api.langdock.test');
    });
});

test('langdock service sends correct request body', function () {
    Http::fake([
        'api.langdock.test/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode(['overall_score' => 80, 'evaluations' => []]),
                    ],
                ],
            ],
            'usage' => ['total_tokens' => 100],
        ], 200),
    ]);

    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id, 'name' => 'Test Co']);
    $run = Run::create(['company_id' => $company->id, 'user_id' => $user->id]);
    $todo = Todo::create(['run_id' => $run->id, 'raw_input' => 'Test todo', 'normalized_title' => 'Test todo']);

    $service = new WebhookAiService($user);
    $result = $service->analyzeTodos($run, collect([$todo]), $company);

    Http::assertSent(function ($request) {
        $body = $request->data();
        return isset($body['model'])
            && $body['model'] === 'gpt-4o'
            && isset($body['messages'])
            && count($body['messages']) === 2
            && $body['messages'][0]['role'] === 'system'
            && $body['messages'][1]['role'] === 'user';
    });
});

test('langdock service parses json response correctly', function () {
    $mockResponse = [
        'overall_score' => 85,
        'evaluations' => [
            [
                'task_index' => 0,
                'color' => 'green',
                'score' => 85,
                'reasoning' => 'High impact task',
                'priority_recommendation' => 'keep',
            ],
        ],
        'missing_todos' => [],
    ];

    Http::fake([
        'api.langdock.test/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode($mockResponse),
                    ],
                ],
            ],
            'usage' => ['total_tokens' => 200],
        ], 200),
    ]);

    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id, 'name' => 'Test Co']);
    $run = Run::create(['company_id' => $company->id, 'user_id' => $user->id]);
    $todo = Todo::create(['run_id' => $run->id, 'raw_input' => 'Important task', 'normalized_title' => 'Important task']);

    $service = new WebhookAiService($user);
    $result = $service->analyzeTodos($run, collect([$todo]), $company);

    expect($result)->toBeArray()
        ->and($result['overall_score'])->toBe(85)
        ->and($result['evaluations'])->toBeArray()
        ->and($result['evaluations'][0]['color'])->toBe('green');
});

test('langdock service handles plain text response', function () {
    Http::fake([
        'api.langdock.test/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'This is a plain text analysis response.',
                    ],
                ],
            ],
            'usage' => ['total_tokens' => 50],
        ], 200),
    ]);

    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id, 'name' => 'Test Co']);
    $run = Run::create(['company_id' => $company->id, 'user_id' => $user->id]);
    $todo = Todo::create(['run_id' => $run->id, 'raw_input' => 'Test', 'normalized_title' => 'Test']);

    $service = new WebhookAiService($user);
    $result = $service->analyzeTodos($run, collect([$todo]), $company);

    // Should return minimal valid structure for plain text
    expect($result)->toBeArray()
        ->and($result['overall_score'])->toBe(50)
        ->and($result['evaluations'])->toBe([]);
});

test('langdock service throws exception on api error', function () {
    Http::fake([
        'api.langdock.test/*' => Http::response([
            'error' => [
                'message' => 'Rate limit exceeded',
            ],
        ], 429),
    ]);

    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id, 'name' => 'Test Co']);
    $run = Run::create(['company_id' => $company->id, 'user_id' => $user->id]);
    $todo = Todo::create(['run_id' => $run->id, 'raw_input' => 'Test', 'normalized_title' => 'Test']);

    $service = new WebhookAiService($user);

    expect(fn () => $service->analyzeTodos($run, collect([$todo]), $company))
        ->toThrow(\Exception::class);
});

test('langdock service throws exception on 5xx error', function () {
    Http::fake([
        'api.langdock.test/*' => Http::response('Internal Server Error', 500),
    ]);

    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id, 'name' => 'Test Co']);
    $run = Run::create(['company_id' => $company->id, 'user_id' => $user->id]);
    $todo = Todo::create(['run_id' => $run->id, 'raw_input' => 'Test', 'normalized_title' => 'Test']);

    $service = new WebhookAiService($user);

    expect(fn () => $service->analyzeTodos($run, collect([$todo]), $company))
        ->toThrow(\Exception::class);
});

test('langdock service handles invalid response format', function () {
    Http::fake([
        'api.langdock.test/*' => Http::response([
            'unexpected' => 'format',
        ], 200),
    ]);

    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id, 'name' => 'Test Co']);
    $run = Run::create(['company_id' => $company->id, 'user_id' => $user->id]);
    $todo = Todo::create(['run_id' => $run->id, 'raw_input' => 'Test', 'normalized_title' => 'Test']);

    $service = new WebhookAiService($user);

    expect(fn () => $service->analyzeTodos($run, collect([$todo]), $company))
        ->toThrow(\Exception::class);
});

test('langdock service requires api key configuration', function () {
    config(['services.langdock.api_key' => null]);

    $user = User::factory()->create();

    expect(fn () => new WebhookAiService($user))
        ->toThrow(\Exception::class, 'Langdock API credentials not configured');
});
