<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Run;
use Illuminate\Http\Request;

class RunController extends Controller
{
    /**
     * Get all runs for the authenticated user
     */
    public function index(Request $request)
    {
        $runs = $request->user()->runs()
            ->with(['company', 'snapshotTopKpi'])
            ->latest()
            ->paginate(20);

        return response()->json($runs);
    }

    /**
     * Get a specific run with all related data
     */
    public function show(Request $request, Run $run)
    {
        // Ensure run belongs to user
        if ($run->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'data' => $run->load([
                'company',
                'snapshotTopKpi',
                'todos.evaluation',
                'missingTodos',
            ]),
        ]);
    }

    /**
     * Get todos for a run
     */
    public function todos(Request $request, Run $run)
    {
        // Ensure run belongs to user
        if ($run->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $todos = $run->todos()->with('evaluation')->get();

        return response()->json([
            'data' => $todos,
        ]);
    }

    /**
     * Get evaluations for a run
     */
    public function evaluations(Request $request, Run $run)
    {
        // Ensure run belongs to user
        if ($run->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $evaluations = $run->evaluations()
            ->with(['todo', 'primaryGoal', 'primaryKpi'])
            ->orderBy('score', 'desc')
            ->get();

        return response()->json([
            'data' => $evaluations,
        ]);
    }

    /**
     * Get missing todos for a run
     */
    public function missingTodos(Request $request, Run $run)
    {
        // Ensure run belongs to user
        if ($run->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $missingTodos = $run->missingTodos()
            ->with(['goal', 'kpi'])
            ->orderBy('impact_score', 'desc')
            ->get();

        return response()->json([
            'data' => $missingTodos,
        ]);
    }
}
