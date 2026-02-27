<?php

use App\Http\Controllers\Billing\CheckoutController;
use App\Http\Controllers\Billing\PortalController;
use App\Http\Controllers\Billing\StripeWebhookController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Stripe Webhook (no auth, no CSRF)
|--------------------------------------------------------------------------
*/
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');

/*
|--------------------------------------------------------------------------
| Authenticated App Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - Main entry point showing latest analysis
    Volt::route('dashboard', 'dashboard')->name('dashboard');

    // Analysis Flow (Main App)
    Route::prefix('app')->name('app.')->group(function () {
        Volt::route('/company', 'onboarding.company')->name('company');
        Volt::route('/goals', 'onboarding.goals')->name('goals');
        Volt::route('/todos', 'onboarding.todos')->name('todos');
        Volt::route('/analysis/{run?}', 'onboarding.analysis')->name('analysis');
    });

    // Analyses History
    Volt::route('analyses', 'analyses.index')->name('analyses.index');

    // Billing
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::post('/checkout', [CheckoutController::class, 'create'])->name('checkout');
        Route::get('/portal', [PortalController::class, 'redirect'])->name('portal');
        Volt::route('/', 'billing.index')->name('index');
        Volt::route('/success', 'billing.success')->name('success');
        Volt::route('/cancel', 'billing.cancel')->name('cancel');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::redirect('/', '/settings/profile');
        Volt::route('/profile', 'settings.profile')->name('profile');
        Volt::route('/password', 'settings.password')->name('password');
        Volt::route('/appearance', 'settings.appearance')->name('appearance');

        Volt::route('/two-factor', 'settings.two-factor')
            ->middleware(
                when(
                    Features::canManageTwoFactorAuthentication()
                        && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                    ['password.confirm'],
                    [],
                ),
            )
            ->name('two-factor');
    });
});
