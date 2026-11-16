<?php

use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

/*
 * Session (cookie) auth for the first-party SPA: Fortify's POST /login starts a session, and
 * `auth:sanctum` accepts it on /api routes for requests from a stateful origin. Each request
 * re-sends the session cookie from the previous response and starts from fresh guards, so a
 * pass proves the session itself authenticates — not a user cached on the guard in memory.
 */

beforeEach(function () {
    config(['sanctum.stateful' => ['localhost:5173']]);
});

function fromSpa(TestCase $test, ?TestResponse $previous = null): TestCase
{
    app('auth')->forgetGuards();

    $test->withHeaders(['Origin' => 'http://localhost:5173', 'Referer' => 'http://localhost:5173/']);

    if ($previous && ($cookie = $previous->getCookie(config('session.cookie')))) {
        $test->withCookie($cookie->getName(), $cookie->getValue());
    }

    return $test;
}

test('a user can log in with a session and call the api without a bearer token', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);

    $login = fromSpa($this)->postJson('/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $login->assertOk()->assertJson(['two_factor' => false]);

    fromSpa($this, $login)->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

test('a stateful origin without a session is unauthenticated', function () {
    fromSpa($this)->getJson('/api/v1/me')->assertUnauthorized();
});

test('session login with a wrong password is rejected', function () {
    $user = User::factory()->create();

    fromSpa($this)->postJson('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnprocessable();
});

test('logging out of a session ends it', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);

    $login = fromSpa($this)->postJson('/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ])->assertOk();

    fromSpa($this, $login)->postJson('/api/v1/logout')->assertNoContent();

    fromSpa($this, $login)->getJson('/api/v1/me')->assertUnauthorized();
});
