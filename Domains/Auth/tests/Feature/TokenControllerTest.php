<?php

use Domains\Identity\Actions\IssueApiKey;
use Domains\Identity\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a valid api_key/api_secret pair can be exchanged for a bearer token', function () {
    $company = Company::factory()->create();

    ['apiKey' => $apiKey, 'plainSecret' => $plainSecret] = app(IssueApiKey::class)->handle($company, ['name' => 'CI Key']);

    $response = $this->postJson('/api/v1/token', [
        'api_key' => $apiKey->api_key,
        'api_secret' => $plainSecret,
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['token', 'token_type', 'scopes', 'expires_at']]);
});

test('an invalid api_secret is rejected', function () {
    $company = Company::factory()->create();

    ['apiKey' => $apiKey] = app(IssueApiKey::class)->handle($company, ['name' => 'CI Key']);

    $this->postJson('/api/v1/token', [
        'api_key' => $apiKey->api_key,
        'api_secret' => 'wrong-secret',
    ])->assertUnprocessable();
});

test('an unknown api_key is rejected', function () {
    $this->postJson('/api/v1/token', [
        'api_key' => 'unknown-key',
        'api_secret' => 'whatever',
    ])->assertUnprocessable();
});
