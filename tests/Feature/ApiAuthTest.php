<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Goal;

/**
 * API Authentication Tests
 * 
 * Diese Tests prüfen, dass alle API-Endpunkte durch Session-Auth geschützt sind.
 */

test('unauthenticated user cannot access /api/goals', function () {
    $response = $this->getJson('/api/goals');
    
    // API sollte 401 Unauthorized zurückgeben (nicht 302, da JSON-Request)
    $response->assertStatus(401);
});

test('unauthenticated user cannot access /api/user', function () {
    $response = $this->getJson('/api/user');
    
    $response->assertStatus(401);
});

test('unauthenticated user cannot access /api/runs', function () {
    $response = $this->getJson('/api/runs');
    
    $response->assertStatus(401);
});

test('unauthenticated user cannot post to /api/todos', function () {
    $response = $this->postJson('/api/todos', [
        'todos' => ['Test todo'],
    ]);
    
    $response->assertStatus(401);
});

test('authenticated user can access /api/goals', function () {
    $user = User::factory()->create();
    Company::create(['owner_user_id' => $user->id]);
    
    $response = $this->actingAs($user)->getJson('/api/goals');
    
    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('authenticated user can access /api/user', function () {
    $user = User::factory()->create([
        'first_name' => 'Test',
        'last_name' => 'User',
    ]);
    
    $response = $this->actingAs($user)->getJson('/api/user');
    
    $response->assertStatus(200)
        ->assertJsonFragment(['email' => $user->email]);
});

test('authenticated user can access /api/runs', function () {
    $user = User::factory()->create();
    Company::create(['owner_user_id' => $user->id]);
    
    $response = $this->actingAs($user)->getJson('/api/runs');
    
    $response->assertStatus(200);
});

test('authenticated user can create goals', function () {
    $user = User::factory()->create();
    $company = Company::create(['owner_user_id' => $user->id]);
    
    $response = $this->actingAs($user)->postJson('/api/goals', [
        'title' => 'Increase MRR',
        'description' => 'Grow revenue to 50k',
        'priority' => 'high',
    ]);
    
    $response->assertStatus(201);
    
    expect(Goal::where('title', 'Increase MRR')->exists())->toBeTrue();
});

test('user can only see their own company goals', function () {
    // Create two users with companies
    $user1 = User::factory()->create();
    $company1 = Company::create(['owner_user_id' => $user1->id]);
    
    $user2 = User::factory()->create();
    $company2 = Company::create(['owner_user_id' => $user2->id]);
    
    // Create goal for user1
    Goal::create([
        'company_id' => $company1->id,
        'title' => 'User1 Goal',
        'priority' => 'high',
    ]);
    
    // Create goal for user2
    Goal::create([
        'company_id' => $company2->id,
        'title' => 'User2 Goal',
        'priority' => 'high',
    ]);
    
    // User1 should only see their goal
    $response = $this->actingAs($user1)->getJson('/api/goals');
    
    $response->assertStatus(200);
    
    $goals = $response->json('data');
    expect(count($goals))->toBe(1)
        ->and($goals[0]['title'])->toBe('User1 Goal');
});
