<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Run;
use App\Models\Todo;
use App\Services\WebhookAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    /**
     * Create todos and analyze them (creates a new run)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'todos' => 'required|array|min:1',
            'todos.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company = $request->user()->company;

        try {
            DB::beginTransaction();

            $topKpi = $company?->topKpi();

            // Create run
            $run = Run::create([
                'company_id' => $company?->id,
                'user_id' => $request->user()->id,
                'snapshot_top_kpi_id' => $topKpi?->id,
            ]);

            // Create todos
            $todos = collect();
            foreach ($request->todos as $index => $todoText) {
                $todo = Todo::create([
                    'run_id' => $run->id,
                    'raw_input' => $todoText,
                    'normalized_title' => $todoText,
                    'source' => 'api',
                    'position' => $index,
                ]);
                $todos->push($todo);
            }

            // Analyze with AI via webhook
            $analysisService = new WebhookAiService();
            $analysisResults = $analysisService->analyzeTodos($run, $todos, $company);

            // Store results (same logic as Home.php)
            $run->update([
                'overall_score' => $analysisResults['overall_score'] ?? null,
                'summary_text' => $analysisResults['summary_text'] ?? null,
            ]);

            // Store evaluations
            if (isset($analysisResults['evaluations'])) {
                foreach ($analysisResults['evaluations'] as $evaluation) {
                    $todo = $todos[$evaluation['task_index']] ?? null;
                    if (!$todo) continue;

                    $goal = null;
                    $kpi = null;
                    
                    if ($company && isset($evaluation['goal_title'])) {
                        $goal = $company->goals()->where('title', $evaluation['goal_title'])->first();
                    }
                    
                    if ($goal && isset($evaluation['kpi_name'])) {
                        $kpi = $goal->kpis()->where('name', $evaluation['kpi_name'])->first();
                    }

                    \App\Models\TodoEvaluation::create([
                        'run_id' => $run->id,
                        'todo_id' => $todo->id,
                        'color' => $evaluation['color'],
                        'score' => $evaluation['score'],
                        'reasoning' => $evaluation['reasoning'],
                        'priority_recommendation' => $evaluation['priority_recommendation'] ?? null,
                        'action_recommendation' => $evaluation['action_recommendation'] ?? null,
                        'delegation_target_role' => $evaluation['delegation_target_role'] ?? null,
                        'primary_goal_id' => $goal?->id,
                        'primary_kpi_id' => $kpi?->id,
                    ]);
                }
            }

            // Store missing todos
            if (isset($analysisResults['missing_todos'])) {
                foreach ($analysisResults['missing_todos'] as $missing) {
                    $goal = null;
                    $kpi = null;
                    
                    if ($company && isset($missing['goal_title'])) {
                        $goal = $company->goals()->where('title', $missing['goal_title'])->first();
                    }
                    
                    if ($goal && isset($missing['kpi_name'])) {
                        $kpi = $goal->kpis()->where('name', $missing['kpi_name'])->first();
                    }

                    \App\Models\MissingTodo::create([
                        'run_id' => $run->id,
                        'goal_id' => $goal?->id,
                        'kpi_id' => $kpi?->id,
                        'title' => $missing['title'],
                        'description' => $missing['description'] ?? null,
                        'category' => $missing['category'] ?? null,
                        'impact_score' => $missing['impact_score'] ?? null,
                        'suggested_owner_role' => $missing['suggested_owner_role'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Todos analyzed successfully',
                'data' => $run->load([
                    'todos.evaluation',
                    'missingTodos',
                ]),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to analyze todos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch create todos without analysis (for n8n automation)
     */
    public function storeBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'run_id' => 'required|exists:runs,id',
            'todos' => 'required|array|min:1',
            'todos.*.title' => 'required|string',
            'todos.*.source' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $run = Run::findOrFail($request->run_id);

        // Ensure run belongs to user
        if ($run->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $createdTodos = [];
        $position = $run->todos()->count();

        foreach ($request->todos as $todoData) {
            $todo = Todo::create([
                'run_id' => $run->id,
                'raw_input' => $todoData['title'],
                'normalized_title' => $todoData['title'],
                'source' => $todoData['source'] ?? 'api_batch',
                'position' => $position++,
            ]);

            $createdTodos[] = $todo;
        }

        return response()->json([
            'message' => 'Todos created successfully',
            'data' => $createdTodos,
        ], 201);
    }
}
