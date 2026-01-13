<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Run;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle run completed webhook (triggered by Axia, received by n8n)
     */
    public function runCompleted(Request $request)
    {
        $run = Run::with(['user', 'company', 'todos.evaluation', 'missingTodos'])
            ->findOrFail($request->run_id);

        // Ensure run belongs to authenticated user
        if ($run->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get user's n8n webhook URL from config or user settings
        $n8nWebhookUrl = config('services.n8n.webhook_url');

        if (!$n8nWebhookUrl) {
            return response()->json([
                'message' => 'n8n webhook URL not configured',
            ], 500);
        }

        try {
            Http::post($n8nWebhookUrl . '/webhook/axia/run-completed', [
                'user_id' => $run->user_id,
                'run_id' => $run->id,
                'overall_score' => $run->overall_score,
                'summary_text' => $run->summary_text,
                'top_priority_todos' => $run->todos()
                    ->with('evaluation')
                    ->whereHas('evaluation', function ($query) {
                        $query->where('color', 'green')
                              ->orWhere('color', 'yellow');
                    })
                    ->limit(5)
                    ->get(),
                'missing_todos' => $run->missingTodos()->limit(3)->get(),
                'timestamp' => now()->toIso8601String(),
            ]);

            Log::info('n8n webhook triggered for run completed', [
                'run_id' => $run->id,
                'user_id' => $run->user_id,
            ]);

            return response()->json([
                'message' => 'Webhook triggered successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to trigger n8n webhook', [
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to trigger webhook',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle goal achieved webhook
     */
    public function goalAchieved(Request $request)
    {
        $request->validate([
            'goal_id' => 'required|exists:goals,id',
            'kpi_id' => 'nullable|exists:goal_kpis,id',
        ]);

        $goal = \App\Models\Goal::with(['company', 'kpis'])
            ->findOrFail($request->goal_id);

        // Ensure goal belongs to authenticated user's company
        if ($goal->company->owner_user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $n8nWebhookUrl = config('services.n8n.webhook_url');

        if (!$n8nWebhookUrl) {
            return response()->json([
                'message' => 'n8n webhook URL not configured',
            ], 500);
        }

        try {
            Http::post($n8nWebhookUrl . '/webhook/axia/goal-achieved', [
                'user_id' => $request->user()->id,
                'goal_id' => $goal->id,
                'goal_title' => $goal->title,
                'kpi_id' => $request->kpi_id,
                'timestamp' => now()->toIso8601String(),
            ]);

            Log::info('n8n webhook triggered for goal achieved', [
                'goal_id' => $goal->id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Webhook triggered successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to trigger n8n webhook', [
                'goal_id' => $goal->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to trigger webhook',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Receive incoming webhooks from n8n
     * This endpoint should be secured with a webhook secret
     */
    public function incomingWebhook(Request $request)
    {
        // Verify webhook signature
        $secret = config('services.n8n.webhook_secret');
        $signature = $request->header('X-N8N-Signature');

        if (!$this->verifySignature($request->getContent(), $signature, $secret)) {
            return response()->json([
                'message' => 'Invalid signature',
            ], 401);
        }

        Log::info('Received n8n webhook', $request->all());

        // Handle different webhook types
        $action = $request->input('action');

        switch ($action) {
            case 'create_todo':
                return $this->handleCreateTodo($request);
            
            case 'update_kpi':
                return $this->handleUpdateKpi($request);
            
            default:
                return response()->json([
                    'message' => 'Unknown action',
                ], 400);
        }
    }

    /**
     * Handle create todo from n8n
     */
    protected function handleCreateTodo(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'todo_text' => 'required|string',
            'source' => 'nullable|string',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'message' => 'User has no company',
            ], 404);
        }

        // Create or find an active run for today
        $run = Run::firstOrCreate([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'created_at' => now()->startOfDay(),
        ]);

        $todo = \App\Models\Todo::create([
            'run_id' => $run->id,
            'raw_input' => $request->todo_text,
            'normalized_title' => $request->todo_text,
            'source' => $request->source ?? 'n8n',
            'position' => $run->todos()->count(),
        ]);

        return response()->json([
            'message' => 'Todo created successfully',
            'data' => $todo,
        ], 201);
    }

    /**
     * Handle update KPI from n8n
     */
    protected function handleUpdateKpi(Request $request)
    {
        $request->validate([
            'kpi_id' => 'required|exists:goal_kpis,id',
            'current_value' => 'required|numeric',
        ]);

        $kpi = \App\Models\GoalKpi::findOrFail($request->kpi_id);
        $kpi->update([
            'current_value' => $request->current_value,
        ]);

        return response()->json([
            'message' => 'KPI updated successfully',
            'data' => $kpi,
        ]);
    }

    /**
     * Verify webhook signature
     */
    protected function verifySignature($payload, $signature, $secret)
    {
        if (!$signature || !$secret) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
}
