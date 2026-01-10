<?php

use App\Http\Controllers\AxiaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Axia Routes
|--------------------------------------------------------------------------
|
| Add these routes to your routes/web.php file
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Main dashboard
    Route::get('/axia', [AxiaController::class, 'index'])->name('axia.dashboard');
    
    // Save data
    Route::post('/axia/save', [AxiaController::class, 'save'])->name('axia.save');
    
    // Run analysis
    Route::post('/axia/analyze', [AxiaController::class, 'analyze'])->name('axia.analyze');
    
    // Chat endpoint
    Route::post('/axia/chat', [AxiaController::class, 'chat'])->name('axia.chat');
});
