<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\RunController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CompanyProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All API routes require authentication via Sanctum/session.
| Routes are organized by resource for clarity.
|
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | User Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::get('/company', [UserController::class, 'company']);
    });

    /*
    |--------------------------------------------------------------------------
    | Company & Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('companies/{company}')->group(function () {
        Route::get('/profiles', [CompanyProfileController::class, 'index']);
        Route::post('/profiles', [CompanyProfileController::class, 'store']);
        Route::get('/profile-data', [CompanyProfileController::class, 'prioritized']);
    });

    /*
    |--------------------------------------------------------------------------
    | Goals & KPIs Routes
    |--------------------------------------------------------------------------
    */
    Route::apiResource('goals', GoalController::class);
    Route::prefix('goals/{goal}')->group(function () {
        Route::get('/kpis', [GoalController::class, 'kpis']);
        Route::post('/kpis', [GoalController::class, 'storeKpi']);
    });

    /*
    |--------------------------------------------------------------------------
    | Runs & Analysis Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('runs')->group(function () {
        Route::get('/', [RunController::class, 'index']);
        Route::get('/{run}', [RunController::class, 'show']);
        Route::get('/{run}/todos', [RunController::class, 'todos']);
        Route::get('/{run}/evaluations', [RunController::class, 'evaluations']);
        Route::get('/{run}/missing-todos', [RunController::class, 'missingTodos']);
    });

    /*
    |--------------------------------------------------------------------------
    | Todos Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('todos')->group(function () {
        Route::post('/', [TodoController::class, 'store']);
        Route::post('/batch', [TodoController::class, 'storeBatch']);
    });

    /*
    |--------------------------------------------------------------------------
    | Chat Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('chat')->group(function () {
        Route::post('/start', [ChatController::class, 'start']);
        Route::post('/message', [ChatController::class, 'message']);
        Route::get('/session/{sessionId}', [ChatController::class, 'show']);
    });
});
