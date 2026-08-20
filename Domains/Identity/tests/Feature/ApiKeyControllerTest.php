<?php

use Domains\Identity\Models\ApiKey;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a logged-in user can create an api key and sees the token only once', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/api-keys', ['name' => 'CI Key']);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'name', 'api_key'], 'api_secret']);

    expect($response->json('data'))->not->toHaveKey('api_secret');
});

test('a user without a company cannot create an api key', function () {
    $user = User::factory()->create(['company_id' => null]);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/api-keys', ['name' => 'CI Key'])->assertForbidden();
});

test('a logged-in user can list only their own company keys', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $userB = User::factory()->create(['company_id' => $companyB->id]);

    ApiKey::factory()->count(2)->create(['company_id' => $companyA->id]);
    ApiKey::factory()->count(3)->create(['company_id' => $companyB->id]);

    Sanctum::actingAs($userA);

    $this->getJson('/api/v1/api-keys')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('a user cannot revoke another company\'s api key', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $keyB = ApiKey::factory()->create(['company_id' => $companyB->id]);

    Sanctum::actingAs($userA);

    $this->deleteJson("/api/v1/api-keys/{$keyB->id}")->assertNotFound();

    $this->assertDatabaseHas('api_keys', ['id' => $keyB->id]);
});

test('a user can revoke their own company\'s api key', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $key = ApiKey::factory()->create(['company_id' => $company->id]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/api-keys/{$key->id}")->assertNoContent();

    $this->assertDatabaseMissing('api_keys', ['id' => $key->id]);
});

test('unauthenticated requests to api key endpoints are rejected', function () {
    $this->getJson('/api/v1/api-keys')->assertUnauthorized();
});
