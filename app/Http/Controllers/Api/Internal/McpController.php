<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\Todo;
use App\Models\User;
use App\Services\UserContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class McpController extends Controller
{
    public function __construct(
        private UserContextService $userContext
    ) {}

    /**
     * Get full user context.
     * 
     * POST /api/internal/mcp/context
     * Body: { "user_id": "uuid" }
     */
    public function getContext(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        Log::channel('stack')->info('MCP: Get user context', [
            'user_id' => $user->id,
            'requested_by' => 'mcp_server',
        ]);

        $context = $this->userContext->getFullContext($user);

        return response()->json([
            'success' => true,
            'data' => $context,
        ]);
    }

    /**
     * Get IMAP emails for user.
     * 
     * POST /api/internal/mcp/emails
     * Body: { "user_id": "uuid", "folder": "INBOX", "limit": 50, "unread_only": false }
     */
    public function getEmails(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'folder' => 'string',
            'limit' => 'integer|min:1|max:100',
            'unread_only' => 'boolean',
        ]);

        $user = User::findOrFail($validated['user_id']);

        Log::channel('stack')->info('MCP: Get IMAP emails', [
            'user_id' => $user->id,
            'folder' => $validated['folder'] ?? 'INBOX',
            'limit' => $validated['limit'] ?? 50,
        ]);

        $emails = $this->userContext->getImapMails($user, [
            'folder' => $validated['folder'] ?? 'INBOX',
            'limit' => $validated['limit'] ?? 50,
            'unread_only' => $validated['unread_only'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'data' => $emails,
        ]);
    }

    /**
     * Create todos for user.
     * 
     * POST /api/internal/mcp/todos/create
     * Body: { "user_id": "uuid", "run_id": "uuid", "todos": [...] }
     */
    public function createTodos(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'run_id' => 'required|uuid|exists:runs,id',
            'todos' => 'required|array',
            'todos.*.title' => 'required|string',
            'todos.*.description' => 'nullable|string',
            'todos.*.due_date' => 'nullable|date',
            'todos.*.priority' => 'nullable|string|in:low,medium,high',
        ]);

        $user = User::findOrFail($validated['user_id']);

        Log::channel('stack')->info('MCP: Create todos', [
            'user_id' => $user->id,
            'run_id' => $validated['run_id'],
            'count' => count($validated['todos']),
        ]);

        $createdTodos = [];

        foreach ($validated['todos'] as $todoData) {
            $todo = Todo::create([
                'run_id' => $validated['run_id'],
                'title' => $todoData['title'],
                'description' => $todoData['description'] ?? null,
                'due_date' => $todoData['due_date'] ?? null,
                'priority' => $todoData['priority'] ?? 'medium',
                'status' => 'pending',
            ]);

            $createdTodos[] = $todo;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'created_count' => count($createdTodos),
                'todos' => $createdTodos,
            ],
        ]);
    }

    /**
     * Update goals for user.
     * 
     * POST /api/internal/mcp/goals/update
     * Body: { "user_id": "uuid", "goal_id": "uuid", "updates": {...} }
     */
    public function updateGoal(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'goal_id' => 'required|uuid|exists:goals,id',
            'updates' => 'required|array',
            'updates.title' => 'nullable|string',
            'updates.description' => 'nullable|string',
            'updates.status' => 'nullable|string|in:not_started,in_progress,completed,on_hold',
            'updates.target_date' => 'nullable|date',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $goal = Goal::findOrFail($validated['goal_id']);

        // Verify goal belongs to user
        if ($goal->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Goal does not belong to user',
            ], 403);
        }

        Log::channel('stack')->info('MCP: Update goal', [
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'updates' => array_keys($validated['updates']),
        ]);

        $goal->update($validated['updates']);

        // Clear cache
        $this->userContext->clearCache($user);

        return response()->json([
            'success' => true,
            'data' => $goal,
        ]);
    }

    /**
     * Health check endpoint.
     */
    public function health()
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'mcp_internal_api',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
