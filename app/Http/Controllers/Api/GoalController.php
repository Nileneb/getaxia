<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\GoalKpi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoalController extends Controller
{
    /**
     * Get all goals for the authenticated user's company
     */
    public function index(Request $request)
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'message' => 'No company found for this user',
            ], 404);
        }

        $goals = $company->goals()->with('kpis')->get();

        return response()->json([
            'data' => $goals,
        ]);
    }

    /**
     * Create a new goal
     */
    public function store(Request $request)
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'message' => 'No company found for this user',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'time_frame' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $goal = Goal::create([
            'company_id' => $company->id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'time_frame' => $request->time_frame,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Goal created successfully',
            'data' => $goal->load('kpis'),
        ], 201);
    }

    /**
     * Get a specific goal
     */
    public function show(Request $request, Goal $goal)
    {
        // Ensure goal belongs to user's company
        if ($goal->company_id !== $request->user()->company?->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'data' => $goal->load('kpis'),
        ]);
    }

    /**
     * Update a goal
     */
    public function update(Request $request, Goal $goal)
    {
        // Ensure goal belongs to user's company
        if ($goal->company_id !== $request->user()->company?->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'time_frame' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $goal->update($request->only([
            'title', 'description', 'priority', 'time_frame', 'is_active'
        ]));

        return response()->json([
            'message' => 'Goal updated successfully',
            'data' => $goal->load('kpis'),
        ]);
    }

    /**
     * Delete a goal
     */
    public function destroy(Request $request, Goal $goal)
    {
        // Ensure goal belongs to user's company
        if ($goal->company_id !== $request->user()->company?->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $goal->delete();

        return response()->json([
            'message' => 'Goal deleted successfully',
        ]);
    }

    /**
     * Get KPIs for a goal
     */
    public function kpis(Request $request, Goal $goal)
    {
        // Ensure goal belongs to user's company
        if ($goal->company_id !== $request->user()->company?->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'data' => $goal->kpis,
        ]);
    }

    /**
     * Create a KPI for a goal
     */
    public function storeKpi(Request $request, Goal $goal)
    {
        // Ensure goal belongs to user's company
        if ($goal->company_id !== $request->user()->company?->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'current_value' => 'required|numeric',
            'target_value' => 'required|numeric',
            'tracking_frequency' => 'nullable|in:daily,weekly,monthly,quarterly',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kpi = GoalKpi::create([
            'goal_id' => $goal->id,
            'name' => $request->name,
            'unit' => $request->unit,
            'current_value' => $request->current_value,
            'target_value' => $request->target_value,
            'tracking_frequency' => $request->tracking_frequency ?? 'weekly',
        ]);

        return response()->json([
            'message' => 'KPI created successfully',
            'data' => $kpi,
        ], 201);
    }
}
