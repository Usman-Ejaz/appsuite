<?php

use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a user can login with valid credentials and receives a token', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);

    $response = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['token', 'token_type', 'scopes', 'expires_at']);
});

test('login fails with invalid credentials', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnprocessable();
});

test('login is rate limited after repeated failures', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
    }

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

test('a logged in user can logout and revoke their token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/logout')
        ->assertNoContent();

    expect($user->tokens()->count())->toBe(0);
});
