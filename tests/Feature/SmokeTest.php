<?php

use App\Models\User;
use App\Models\Company;

/**
 * Smoke Tests - App bootet und Kernrouten funktionieren
 */

test('homepage loads successfully', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('login page loads successfully', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('register page loads successfully', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('onboarding page requires authentication', function () {
    $response = $this->get('/app/company');

    $response->assertRedirect('/login');
});

test('onboarding page loads for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/app/company');

    $response->assertStatus(200);
});

test('dashboard requires authentication', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('dashboard loads for authenticated user', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

/**
 * Database Integrity - Migrations funktionieren
 */

test('migrations run without errors', function () {
    // This test uses RefreshDatabase trait, so if we get here, migrations work
    expect(true)->toBeTrue();
});

test('user can be created with factory', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->id)->not->toBeNull()
        ->and($user->email)->not->toBeNull();
});

test('company can be created for user', function () {
    $user = User::factory()->create();

    $company = Company::create([
        'owner_user_id' => $user->id,
        'name' => 'Test Company',
    ]);

    expect($company)->toBeInstanceOf(Company::class)
        ->and($company->owner_user_id)->toBe($user->id);
});

test('user company relationship works', function () {
    $user = User::factory()->create();

    $company = Company::create([
        'owner_user_id' => $user->id,
        'name' => 'Test Company',
    ]);

    $user->refresh();

    expect($user->company)->not->toBeNull()
        ->and($user->company->id)->toBe($company->id);
});
