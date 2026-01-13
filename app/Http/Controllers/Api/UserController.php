<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get authenticated user
     */
    public function show(Request $request)
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }

    /**
     * Get user's company with goals and KPIs
     */
    public function company(Request $request)
    {
        $company = $request->user()->company()
            ->with(['goals.kpis', 'runs' => function ($query) {
                $query->latest()->limit(10);
            }])
            ->first();

        if (!$company) {
            return response()->json([
                'message' => 'No company found for this user',
            ], 404);
        }

        return response()->json([
            'data' => $company,
        ]);
    }
}
