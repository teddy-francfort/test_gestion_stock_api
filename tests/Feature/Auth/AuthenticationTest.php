<?php

declare(strict_types=1);

use App\Models\User;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertNoContent();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can not authenticate if already logged', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertForbidden();
    $response->assertJson(['code' => 'ALREADY_LOGGED_IN']);
});

test('check if user is logged', function () {
    $user = User::factory()->create();

    $response = $this->getJson('/api/is-auth');
    $response->assertUnauthorized();

    $response = $this->actingAs($user)->getJson('/api/is-auth');
    $response->assertSuccessful();
});
