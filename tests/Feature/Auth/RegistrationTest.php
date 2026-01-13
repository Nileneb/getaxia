<?php

use App\Models\User;
use App\Models\Company;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register with first_name and last_name', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasNoErrors();

    // Fortify redirects to dashboard after registration
    $response->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('registration creates user with correct fields', function () {
    $this->post(route('register.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'jane@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->first_name)->toBe('Jane')
        ->and($user->last_name)->toBe('Smith')
        ->and($user->is_guest)->toBeFalse();
});

test('registration creates company for new user', function () {
    $this->post(route('register.store'), [
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'max@example.com')->first();

    expect($user->company)->not->toBeNull()
        ->and($user->company->owner_user_id)->toBe($user->id);
});

// Guest login test skipped - feature not yet implemented via Fortify routes
// TODO: Add guest login route via RegisterController if needed

test('password is hashed on registration', function () {
    $this->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'hash@example.com',
        'password' => 'mypassword123',
        'password_confirmation' => 'mypassword123',
    ]);

    $user = User::where('email', 'hash@example.com')->first();

    // Password should not be stored as plain text
    expect($user->password)->not->toBe('mypassword123')
        ->and(\Hash::check('mypassword123', $user->password))->toBeTrue();
});
