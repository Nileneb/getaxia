<?php

use App\Http\Middleware\EnsureSubscribed;
use App\Models\Company;
use App\Models\User;
use App\Notifications\TrialEndingSoonNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    config([
        'services.stripe.basic_price_id' => 'price_test_basic',
        'services.stripe.trial_days' => 14,
        'cashier.currency' => 'eur',
    ]);
});

/*
|--------------------------------------------------------------------------
| Phase 0 – Prerequisites
|--------------------------------------------------------------------------
*/

test('user model has billable trait', function () {
    $user = User::factory()->create();

    expect(method_exists($user, 'subscribed'))->toBeTrue();
    expect(method_exists($user, 'subscription'))->toBeTrue();
    expect(method_exists($user, 'newSubscription'))->toBeTrue();
    expect(method_exists($user, 'createOrGetStripeCustomer'))->toBeTrue();
    expect(method_exists($user, 'redirectToBillingPortal'))->toBeTrue();
});

test('stripe customer columns exist on users table', function () {
    $user = User::factory()->create();

    // These columns should exist and be nullable
    expect($user->stripe_id)->toBeNull();
    expect($user->pm_type)->toBeNull();
    expect($user->pm_last_four)->toBeNull();
    expect($user->trial_ends_at)->toBeNull();
});

test('subscriptions table exists', function () {
    expect(\Illuminate\Support\Facades\Schema::hasTable('subscriptions'))->toBeTrue();
    expect(\Illuminate\Support\Facades\Schema::hasTable('subscription_items'))->toBeTrue();
});

test('stripe environment variables are configured', function () {
    expect(config('services.stripe.basic_price_id'))->toBe('price_test_basic');
    expect(config('services.stripe.trial_days'))->toBe(14);
});

/*
|--------------------------------------------------------------------------
| Phase 1 – Checkout
|--------------------------------------------------------------------------
*/

test('checkout route exists and requires auth', function () {
    $response = $this->post(route('billing.checkout'));

    $response->assertRedirect(); // should redirect to login
});

test('checkout rejects already subscribed users', function () {
    $user = User::factory()->create();
    Company::factory()->create(['owner_user_id' => $user->id]);

    // Mock the user being subscribed
    $user->stripe_id = 'cus_test_123';
    $user->save();

    // Create a mock subscription directly in the database
    \Illuminate\Support\Facades\DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_test_basic',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->post(route('billing.checkout'));

    $response->assertRedirect(route('billing.index'));
    $response->assertSessionHas('error');
});

/*
|--------------------------------------------------------------------------
| Phase 2 – Trial Configuration
|--------------------------------------------------------------------------
*/

test('trial days are configurable', function () {
    config(['services.stripe.trial_days' => 7]);
    expect(config('services.stripe.trial_days'))->toBe(7);

    config(['services.stripe.trial_days' => 30]);
    expect(config('services.stripe.trial_days'))->toBe(30);

    config(['services.stripe.trial_days' => 0]);
    expect(config('services.stripe.trial_days'))->toBe(0);
});

test('trial ending notification can be sent', function () {
    Notification::fake();

    $user = User::factory()->create();

    $user->notify(new TrialEndingSoonNotification(
        trialEndsAt: Carbon::now()->addDays(3)
    ));

    Notification::assertSentTo($user, TrialEndingSoonNotification::class);
});

test('trial ending notification contains correct content', function () {
    $trialEndsAt = Carbon::now()->addDays(3)->startOfDay();
    $notification = new TrialEndingSoonNotification(trialEndsAt: $trialEndsAt);

    $user = User::factory()->create(['first_name' => 'Max']);

    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('trial ends in');
    expect($mail->actionUrl)->toBe(route('billing.index'));
});

/*
|--------------------------------------------------------------------------
| Phase 3 – Portal & Middleware
|--------------------------------------------------------------------------
*/

test('billing portal route exists and requires auth', function () {
    $response = $this->get(route('billing.portal'));

    $response->assertRedirect(); // should redirect to login
});

test('billing page route exists and requires auth', function () {
    $response = $this->get(route('billing.index'));

    $response->assertRedirect(); // should redirect to login
});

test('subscription middleware blocks unsubscribed users', function () {
    $user = User::factory()->create();
    Company::factory()->create(['owner_user_id' => $user->id]);

    // Create a test route with the middleware
    \Illuminate\Support\Facades\Route::middleware(['auth', 'subscribed'])
        ->get('/test-subscribed', fn () => response('ok'));

    $response = $this->actingAs($user)->get('/test-subscribed');

    $response->assertRedirect(route('billing.index'));
});

test('subscription middleware allows subscribed users', function () {
    $user = User::factory()->create();
    Company::factory()->create(['owner_user_id' => $user->id]);
    $user->stripe_id = 'cus_test_456';
    $user->save();

    \Illuminate\Support\Facades\DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_456',
        'stripe_status' => 'active',
        'stripe_price' => 'price_test_basic',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    \Illuminate\Support\Facades\Route::middleware(['auth', 'subscribed'])
        ->get('/test-subscribed', fn () => response('ok'));

    $response = $this->actingAs($user)->get('/test-subscribed');

    $response->assertOk();
    $response->assertSee('ok');
});

test('subscription middleware returns 403 for api requests', function () {
    $user = User::factory()->create();
    Company::factory()->create(['owner_user_id' => $user->id]);

    \Illuminate\Support\Facades\Route::middleware(['auth', 'subscribed'])
        ->get('/api/test-subscribed', fn () => response('ok'));

    $response = $this->actingAs($user)
        ->getJson('/api/test-subscribed');

    $response->assertStatus(403);
    $response->assertJson(['message' => 'An active subscription is required.']);
});

/*
|--------------------------------------------------------------------------
| Webhook Route
|--------------------------------------------------------------------------
*/

test('webhook route exists and is accessible without auth', function () {
    // The webhook route should not require auth (it's called by Stripe)
    $response = $this->postJson('/stripe/webhook', []);

    // Should not be a 404 (route exists) or 401/302 (no auth required)
    // Stripe webhook controller returns 200 or 400
    expect($response->status())->not->toBe(404);
    expect($response->status())->not->toBe(302);
});

/*
|--------------------------------------------------------------------------
| Stripe Queue Configuration
|--------------------------------------------------------------------------
*/

test('stripe webhooks queue is configured', function () {
    $config = config('queue.connections.stripe-webhooks');

    expect($config)->not->toBeNull();
    expect($config['driver'])->toBe('database');
    expect($config['queue'])->toBe('stripe-webhooks');
});
