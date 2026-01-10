<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AxiaController extends Controller
{
    /**
     * Display the main Axia dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Load user's saved data (you can customize this based on your models)
        $companyData = $user->company ?? null;
        $goals = $user->goals ?? collect();
        $todos = $user->todos ?? collect();
        $analyses = $user->analyses ?? collect();
        
        return view('axia.dashboard', compact(
            'companyData',
            'goals', 
            'todos',
            'analyses'
        ));
    }

    /**
     * Save user data (company, goals, todos)
     */
    public function save(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'companyData' => 'nullable|array',
            'companyData.name' => 'nullable|string|max:255',
            'companyData.businessModel' => 'nullable|string|max:255',
            'companyData.stage' => 'nullable|string|max:50',
            'companyData.teamSize' => 'nullable|string|max:50',
            'companyData.timeframe' => 'nullable|string|max:50',
            'companyData.additionalInfo' => 'nullable|string|max:1000',
            'goals' => 'nullable|array',
            'goals.*.id' => 'required|string',
            'goals.*.title' => 'required|string|max:255',
            'goals.*.description' => 'nullable|string|max:1000',
            'goals.*.priority' => 'required|in:high,mid,low',
            'todos' => 'nullable|array',
            'todos.*.id' => 'required|string',
            'todos.*.text' => 'required|string|max:500',
        ]);

        // Save company data
        if (isset($validated['companyData'])) {
            $user->company()->updateOrCreate(
                ['user_id' => $user->id],
                $validated['companyData']
            );
        }

        // Save goals
        if (isset($validated['goals'])) {
            $user->goals()->delete();
            foreach ($validated['goals'] as $goal) {
                $user->goals()->create($goal);
            }
        }

        // Save todos
        if (isset($validated['todos'])) {
            $user->todos()->delete();
            foreach ($validated['todos'] as $todo) {
                $user->todos()->create($todo);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Run AI analysis on tasks
     */
    public function analyze(Request $request)
    {
        $user = Auth::user();
        
        // Get user's goals and todos
        $goals = $user->goals;
        $todos = $user->todos;
        
        // TODO: Integrate with AI service for real analysis
        // For now, return mock analysis
        
        $analysis = [
            'focusScore' => 55,
            'tasksAnalyzed' => $todos->count(),
            'goalsCount' => $goals->count(),
            'date' => now()->format('F j, Y'),
        ];

        // Save analysis to history
        $user->analyses()->create($analysis);

        return response()->json($analysis);
    }

    /**
     * Send chat message to AI
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // TODO: Integrate with AI service
        // For now, return mock response
        
        return response()->json([
            'id' => uniqid(),
            'reply' => 'I understand. Let me help you with that.',
        ]);
    }
}
