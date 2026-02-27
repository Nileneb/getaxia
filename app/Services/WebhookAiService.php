<?php

namespace App\Services;

use App\Models\AiLog;
use App\Models\Company;
use App\Models\GoalKpi;
use App\Models\Run;
use App\Models\SystemPrompt;
use App\Services\AiResponseValidator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WebhookAiService - Routes all AI requests through Langdock API
 * Uses OpenAI-compatible endpoint: POST /openai/{region}/v1/chat/completions
 * See: https://docs.langdock.com/api-endpoints/completion/openai
 */
class WebhookAiService
{
    protected ?string $apiKey;
    protected ?string $baseUrl;
    protected ?string $region;
    protected ?string $model;
    protected ?\App\Models\User $user;

    /**
     * Maximum retries for rate-limited (429) requests
     */
    protected int $maxRetries = 3;

    public function __construct(?\App\Models\User $user = null)
    {
        /** @var \App\Models\User|null */
        $this->user = $user ?? Auth::user();

        // Get Langdock configuration from config/services.php
        $this->apiKey = config('services.langdock.api_key');
        $this->baseUrl = rtrim(config('services.langdock.base_url'), '/');
        $this->region = config('services.langdock.region', 'eu');
        $this->model = config('services.langdock.model');

        if (empty($this->apiKey) || empty($this->baseUrl)) {
            throw new \Exception('Langdock API credentials not configured');
        }
    }

    /**
     * Set maximum retry count (useful for testing)
     */
    public function setMaxRetries(int $retries): self
    {
        $this->maxRetries = $retries;
        return $this;
    }

    /**
     * Build the full Langdock OpenAI-compatible API URL
     * Format: {baseUrl}/openai/{region}/v1/chat/completions
     */
    protected function getApiUrl(): string
    {
        return "{$this->baseUrl}/openai/{$this->region}/v1/chat/completions";
    }

    /**
     * Analyze todos against goals and KPIs via webhook
     */
    public function analyzeTodos(Run $run, Collection $todos, ?Company $company = null): array
    {
        $startTime = microtime(true);

        $systemPrompt = SystemPrompt::getActiveForType('todo_analysis');

        if (!$systemPrompt) {
            throw new \Exception('No active system prompt found for todo_analysis');
        }

        $goals = $company ? $company->goals()->with('kpis')->get() : collect();
        $topKpi = $company ? $company->top_kpi : null;

        $contextVariables = $this->buildContextVariables($company, $goals, $topKpi, $todos);
        $userPrompt = $this->buildPromptFromTemplate($systemPrompt->user_prompt_template, $contextVariables);

        // Call webhook with analysis task
        $response = $this->callWebhook([
            'task' => 'todo_analysis',
            'system_message' => $systemPrompt->system_message,
            'user_prompt' => $userPrompt,
            'temperature' => (float) $systemPrompt->temperature,
            'run_id' => $run->id,
            'company_id' => $company?->id,
        ]);

        $duration = (microtime(true) - $startTime) * 1000;

        if (!$response['success']) {
            AiLog::create([
                'run_id' => $run->id,
                'prompt_type' => 'todo_analysis',
                'system_prompt_id' => $systemPrompt->id,
                'input_context' => ['todos' => $todos->pluck('normalized_title')],
                'response' => ['error' => $response['error']],
                'duration_ms' => (int) round($duration),
                'success' => false,
                'error_message' => $response['error'],
            ]);

            throw new \Exception('Webhook AI request failed: ' . $response['error']);
        }

        $result = $response['data'];

        // Check if response is plain text (fallback when n8n doesn't return JSON)
        if (isset($result['analysis']) && is_string($result['analysis'])) {
            Log::warning('WebhookAiService: Received plain text analysis, skipping validation', [
                'run_id' => $run->id,
            ]);

            // Return a minimal valid structure for plain text responses
            $result = [
                'overall_score' => 50,
                'evaluations' => [],
                'missing_tasks' => [],
                'strategic_notes' => $result['analysis'],
            ];
        } else {
            // Validate and enhance quality for structured JSON responses
            $validator = new AiResponseValidator();
            $validator->validateTodoAnalysis($result);
            $result = $validator->enhanceQuality($result);
        }

        // Log success
        AiLog::create([
            'run_id' => $run->id,
            'prompt_type' => 'todo_analysis',
            'system_prompt_id' => $systemPrompt->id,
            'input_context' => [
                'company' => $company?->name,
                'top_kpi' => $topKpi?->name,
                'todos_count' => $todos->count(),
            ],
            'response' => $result,
            'tokens_used' => $response['tokens_used'] ?? null,
            'duration_ms' => (int) round($duration),
            'success' => true,
        ]);

        return $result;
    }

    /**
     * Extract company information from freeform text via webhook
     */
    public function extractCompanyInfo(string $text): array
    {
        $systemPrompt = SystemPrompt::getActiveForType('company_extraction');

        if (!$systemPrompt) {
            throw new \Exception('No active system prompt found for company_extraction');
        }

        $userPrompt = $this->buildPromptFromTemplate(
            $systemPrompt->user_prompt_template,
            ['text' => $text]
        );

        $response = $this->callWebhook([
            'task' => 'company_extraction',
            'system_message' => $systemPrompt->system_message,
            'user_prompt' => $userPrompt,
            'temperature' => (float) $systemPrompt->temperature,
        ]);

        if (!$response['success']) {
            throw new \Exception('Webhook AI request failed: ' . $response['error']);
        }

        return $response['data'];
    }

    /**
     * Extract goals and KPIs from freeform text via webhook
     */
    public function extractGoalsAndKpis(string $text): array
    {
        $systemPrompt = SystemPrompt::getActiveForType('goals_extraction');

        if (!$systemPrompt) {
            throw new \Exception('No active system prompt found for goals_extraction');
        }

        $userPrompt = $this->buildPromptFromTemplate(
            $systemPrompt->user_prompt_template,
            ['text' => $text]
        );

        $response = $this->callWebhook([
            'task' => 'goals_extraction',
            'system_message' => $systemPrompt->system_message,
            'user_prompt' => $userPrompt,
            'temperature' => (float) $systemPrompt->temperature,
        ]);

        if (!$response['success']) {
            throw new \Exception('Webhook AI request failed: ' . $response['error']);
        }

        return $response['data'];
    }

    /**
     * Call Langdock API (OpenAI-compatible) for AI processing
     * Endpoint: POST /openai/{region}/v1/chat/completions
     * Supports JSON mode via response_format for structured outputs
     * Implements retry logic for rate limiting (429)
     */
    protected function callWebhook(array $payload): array
    {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            $attempt++;

            try {
                Log::info('WebhookAiService: Calling Langdock API', [
                    'task' => $payload['task'] ?? 'unknown',
                    'model' => $this->model,
                    'region' => $this->region,
                    'attempt' => $attempt,
                ]);

                // Build OpenAI-compatible messages format
                $messages = [
                    [
                        'role' => 'system',
                        'content' => $payload['system_message'] ?? 'You are a helpful assistant.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $payload['user_prompt'] ?? '',
                    ],
                ];

                // Build request body
                $requestBody = [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => $payload['temperature'] ?? 0.7,
                    'max_tokens' => $payload['max_tokens'] ?? 4000,
                ];

                // Enable JSON mode for structured response tasks
                // This guarantees valid JSON output from the model
                $structuredTasks = ['todo_analysis', 'company_extraction', 'goals_extraction'];
                if (in_array($payload['task'] ?? '', $structuredTasks)) {
                    $requestBody['response_format'] = ['type' => 'json_object'];
                }

                // Call Langdock API with the correct OpenAI-compatible endpoint
                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::timeout(120)
                    ->withHeaders([
                        'Authorization' => "Bearer {$this->apiKey}",
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->getApiUrl(), $requestBody);

                // Handle rate limiting with exponential backoff
                if ($response->status() === 429) {
                    $retryAfter = (int) ($response->header('Retry-After') ?? (2 ** $attempt));
                    Log::warning('WebhookAiService: Rate limited (429), retrying', [
                        'attempt' => $attempt,
                        'retry_after' => $retryAfter,
                    ]);

                    if ($attempt < $this->maxRetries) {
                        sleep(min($retryAfter, 30));
                        continue;
                    }

                    return [
                        'success' => false,
                        'error' => 'Rate limited after ' . $this->maxRetries . ' retries',
                    ];
                }

                if ($response->failed()) {
                    Log::error('WebhookAiService: Langdock API call failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'url' => $this->getApiUrl(),
                    ]);

                    return [
                        'success' => false,
                        'error' => 'Langdock API returned status ' . $response->status() . ': ' . $response->body(),
                    ];
                }

                $data = $response->json();

                Log::info('WebhookAiService: Langdock API response received', [
                    'status' => $response->status(),
                    'usage' => $data['usage'] ?? null,
                ]);

                // Parse the content from the OpenAI-compatible response
                if (!isset($data['choices'][0]['message']['content'])) {
                    Log::error('WebhookAiService: Invalid Langdock response format', ['response' => $data]);

                    return [
                        'success' => false,
                        'error' => 'Invalid Langdock API response format',
                    ];
                }

            $content = $data['choices'][0]['message']['content'];
            $tokensUsed = $data['usage']['total_tokens'] ?? 0;

            // Try to parse content as JSON (expected for structured responses)
            $parsedContent = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($parsedContent)) {
                // Content is valid JSON, return as structured data
                Log::info('WebhookAiService: Successfully parsed response as JSON');
                return [
                    'success' => true,
                    'data' => $parsedContent,
                    'tokens_used' => $tokensUsed,
                ];
            }

            // Fallback: Return as text in analysis field
            Log::info('WebhookAiService: Returning response as text analysis');
            return [
                'success' => true,
                'data' => ['analysis' => $content],
                'tokens_used' => $tokensUsed,
            ];

            } catch (\Exception $e) {
                Log::error('WebhookAiService: Exception during Langdock API call', [
                    'message' => $e->getMessage(),
                    'attempt' => $attempt,
                ]);

                // Only return error on last attempt
                if ($attempt >= $this->maxRetries) {
                    return [
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];
                }

                // Wait before retrying on exception
                sleep(2 ** $attempt);
            }
        }

        return [
            'success' => false,
            'error' => 'Max retries exceeded',
        ];
    }

    /**
     * Build context variables for template replacement
     */
    protected function buildContextVariables(?Company $company, Collection $goals, ?GoalKpi $topKpi, ?Collection $todos = null): array
    {
        $variables = [
            'company_name' => $company?->name ?? 'Not set',
            'business_model' => $company?->business_model ? str_replace('_', ' ', $company->business_model) : 'Not set',
            'team_info' => $company ? "{$company->team_cofounders} co-founders, {$company->team_employees} employees" : 'Not set',
            'user_position' => $company?->user_position ?? 'Not set',
            'customer_profile' => $company?->customer_profile ?? 'Not specified',
            'market_insights' => $company?->market_insights ?? 'Not specified',
            'company_stage' => $this->detectCompanyStage($company, $topKpi),
        ];

        if ($topKpi) {
            $gap = $topKpi->target_value - $topKpi->current_value;
            $gapPercentage = $topKpi->target_value > 0
                ? round((($topKpi->target_value - $topKpi->current_value) / $topKpi->target_value) * 100, 1)
                : 0;

            $variables['top_kpi_name'] = $topKpi->name;
            $variables['top_kpi_current'] = number_format($topKpi->current_value, 0);
            $variables['top_kpi_target'] = number_format($topKpi->target_value, 0);
            $variables['top_kpi_unit'] = $topKpi->unit;
            $variables['top_kpi_gap'] = number_format($gap, 0);
            $variables['top_kpi_gap_percentage'] = $gapPercentage;
        } else {
            $variables['top_kpi_name'] = 'No top KPI set';
            $variables['top_kpi_current'] = '—';
            $variables['top_kpi_target'] = '—';
            $variables['top_kpi_unit'] = '';
            $variables['top_kpi_gap'] = '—';
            $variables['top_kpi_gap_percentage'] = '—';
        }

        $variables['goals_list'] = $this->buildGoalsHierarchy($goals);
        $variables['goals_hierarchy'] = $this->buildGoalsHierarchy($goals);
        $variables['standalone_kpis_list'] = $company ? $this->buildStandaloneKpisList($company) : 'None';

        if ($todos) {
            $todosList = '';
            foreach ($todos as $index => $todo) {
                $todosList .= ($index + 1) . ". {$todo->normalized_title}\n";
            }
            $variables['todos_list'] = $todosList;
        }

        return $variables;
    }

    /**
     * Replace template variables with actual values
     */
    protected function buildPromptFromTemplate(string $template, array $variables): string
    {
        $result = $template;

        foreach ($variables as $key => $value) {
            $result = str_replace("{{{$key}}}", $value, $result);
        }

        return $result;
    }

    /**
     * Detect company stage based on metrics
     */
    protected function detectCompanyStage(?Company $company, ?GoalKpi $topKpi): string
    {
        if (!$company || !$topKpi) {
            return 'Early Stage';
        }

        $teamSize = ($company->team_cofounders ?? 0) + ($company->team_employees ?? 0);
        $current = $topKpi->current_value ?? 0;

        $isRevenue = str_contains(strtolower($topKpi->name), 'revenue') ||
                     str_contains(strtolower($topKpi->name), 'mrr') ||
                     str_contains(strtolower($topKpi->unit ?? ''), '€') ||
                     str_contains(strtolower($topKpi->unit ?? ''), '$');

        if ($isRevenue) {
            if ($current < 5000) return 'Pre-Revenue / Building';
            if ($current < 50000) return 'Early Traction';
            if ($current < 100000) return 'Scaling';
            return 'Growth Stage';
        }

        $isUsers = str_contains(strtolower($topKpi->name), 'user') ||
                   str_contains(strtolower($topKpi->name), 'customer');

        if ($isUsers) {
            if ($current < 100) return 'Pre-PMF / Building';
            if ($current < 1000) return 'Early Traction';
            if ($current < 10000) return 'Product-Market Fit';
            return 'Scaling';
        }

        if ($teamSize < 5) return 'Early Stage';
        if ($teamSize < 20) return 'Growing';
        return 'Scaling';
    }

    /**
     * Build hierarchical goals list
     */
    protected function buildGoalsHierarchy(Collection $goals): string
    {
        if ($goals->isEmpty()) {
            return 'No goals defined yet';
        }

        $hierarchy = '';
        $byPriority = $goals->groupBy('priority');

        $priorityLabels = [
            'high' => '[HIGH PRIORITY - CRITICAL]',
            'medium' => '[MEDIUM PRIORITY]',
            'low' => '[LOW PRIORITY]',
        ];

        foreach (['high', 'medium', 'low'] as $priority) {
            if (!isset($byPriority[$priority]) || $byPriority[$priority]->isEmpty()) {
                continue;
            }

            $hierarchy .= $priorityLabels[$priority] . "\n";

            $counter = 1;
            foreach ($byPriority[$priority] as $goal) {
                $hierarchy .= "→ {$counter}. {$goal->title}";

                if ($goal->time_frame) {
                    $hierarchy .= " ({$goal->time_frame})";
                }

                if ($goal->description) {
                    $hierarchy .= "\n   Description: {$goal->description}";
                }

                $hierarchy .= "\n";

                if ($goal->kpis->isNotEmpty()) {
                    foreach ($goal->kpis as $kpi) {
                        $gap = $kpi->target_value - $kpi->current_value;
                        $gapPct = $kpi->target_value > 0
                            ? round(($gap / $kpi->target_value) * 100, 1)
                            : 0;

                        $hierarchy .= "   └─ KPI: {$kpi->name} ";
                        $hierarchy .= "({$kpi->current_value} → {$kpi->target_value} {$kpi->unit}) ";
                        $hierarchy .= "[Gap: " . number_format($gap, 0) . ", {$gapPct}% to go]";

                        if ($kpi->is_top_kpi) {
                            $hierarchy .= " ⭐ TOP KPI";
                        }

                        $hierarchy .= "\n";
                    }
                }

                $counter++;
            }

            $hierarchy .= "\n";
        }

        $noPriority = $goals->whereNull('priority');
        if ($noPriority->isNotEmpty()) {
            $hierarchy .= "[NO PRIORITY SET]\n";
            $counter = 1;
            foreach ($noPriority as $goal) {
                $hierarchy .= "→ {$counter}. {$goal->title}\n";
                if ($goal->kpis->isNotEmpty()) {
                    foreach ($goal->kpis as $kpi) {
                        $hierarchy .= "   └─ KPI: {$kpi->name} ({$kpi->current_value} → {$kpi->target_value} {$kpi->unit})\n";
                    }
                }
                $counter++;
            }
        }

        return trim($hierarchy);
    }

    /**
     * Build standalone KPIs list
     */
    protected function buildStandaloneKpisList(Company $company): string
    {
        $standaloneKpis = $company->kpis;

        if ($standaloneKpis->isEmpty()) {
            return 'None';
        }

        $list = '';

        foreach ($standaloneKpis as $kpi) {
            $gap = $kpi->target_value - $kpi->current_value;
            $gapPct = $kpi->target_value > 0
                ? round(($gap / $kpi->target_value) * 100, 1)
                : 0;

            $list .= "→ {$kpi->name}: ";
            $list .= "{$kpi->current_value} → {$kpi->target_value} {$kpi->unit} ";
            $list .= "[Gap: " . number_format($gap, 0) . ", {$gapPct}% to go]";

            if ($kpi->is_top_kpi) {
                $list .= " ⭐ TOP KPI";
            }

            $list .= "\n";
        }

        return trim($list);
    }

    /**
     * Send a chat message via Langdock API and get AI response
     * Uses: POST /openai/{region}/v1/chat/completions
     */
    public function sendChatMessage(string $message, ?Company $company = null): array
    {
        $startTime = microtime(true);

        // Build context from company data
        $systemPrompt = "You are a helpful AI assistant.";
        if ($company) {
            $systemPrompt = "You are a helpful AI assistant assisting with strategic planning for {$company->name}.";
            if ($company->business_model) {
                $systemPrompt .= " The company operates in the {$company->business_model} business model.";
            }
        }

        // Build user message with context
        $userMessage = $message;
        if ($company) {
            $goals = $company->goals()->where('is_active', true)->get();
            if ($goals->isNotEmpty()) {
                $userMessage .= "\n\nActive Goals:\n";
                foreach ($goals as $goal) {
                    $userMessage .= "- {$goal->title}\n";
                }
            }
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post($this->getApiUrl(), [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => $userMessage,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                ]);

            if (!$response->successful()) {
                throw new \Exception("Langdock API returned status {$response->status()}: {$response->body()}");
            }

            $data = $response->json();
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            // Extract message from OpenAI-compatible response
            $aiMessage = $data['choices'][0]['message']['content'] ?? 'No response from AI';
            $tokensUsed = $data['usage']['total_tokens'] ?? null;

            // Log the interaction using correct AiLog columns
            AiLog::create([
                'run_id' => null,
                'prompt_type' => 'chat',
                'system_prompt_id' => null,
                'input_context' => [
                    'message' => $message,
                    'company_id' => $company?->id,
                ],
                'response' => $data,
                'tokens_used' => $tokensUsed,
                'duration_ms' => (int) round($duration),
                'success' => true,
            ]);

            return [
                'message' => $aiMessage,
                'usage' => $data['usage'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Chat API call failed', [
                'error' => $e->getMessage(),
                'model' => $this->model,
                'url' => $this->getApiUrl(),
            ]);

            throw new \Exception('Failed to send chat message: ' . $e->getMessage());
        }
    }
}
