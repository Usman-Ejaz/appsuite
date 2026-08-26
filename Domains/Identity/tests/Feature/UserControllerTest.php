<?php

use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->payload = [
        'name' => 'Jane Doe',
        'email' => 'jane@acme-inc.com',
        'password' => 'Sup3r$ecurePass!',
        'password_confirmation' => 'Sup3r$ecurePass!',
    ];
});

test('a root user can create, list, view, update, and delete a user', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));

    $response = $this->postJson('/api/v1/users', $this->payload);
    $response->assertCreated()
        ->assertJsonPath('data.name', 'Jane Doe')
        ->assertJsonPath('data.email', 'jane@acme-inc.com');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/users')->assertOk()->assertJsonCount(2, 'data');
    $this->getJson("/api/v1/users/{$id}")->assertOk()->assertJsonPath('data.id', $id);

    $this->putJson("/api/v1/users/{$id}", ['name' => 'Jane Smith'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Jane Smith');

    $this->deleteJson("/api/v1/users/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('users', ['id' => $id]);
});

test('a root user creating a user without a company_id defaults to their own company', function () {
    $company = Company::factory()->create();
    $root = User::factory()->create(['is_root' => true, 'company_id' => $company->id]);
    Sanctum::actingAs($root);

    $response = $this->postJson('/api/v1/users', $this->payload);

    $response->assertCreated()->assertJsonPath('data.company_id', $company->id);
});

test('a root user creating a user can target an explicit company_id', function () {
    $rootCompany = Company::factory()->create();
    $targetCompany = Company::factory()->create();
    $root = User::factory()->create(['is_root' => true, 'company_id' => $rootCompany->id]);
    Sanctum::actingAs($root);

    $response = $this->postJson('/api/v1/users', [...$this->payload, 'company_id' => $targetCompany->id]);

    $response->assertCreated()->assertJsonPath('data.company_id', $targetCompany->id);
});

test('an owner creating a user is always placed into the owner\'s own company, regardless of company_id', function () {
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $owner = User::factory()->create(['is_owner' => true, 'company_id' => $ownCompany->id]);
    Sanctum::actingAs($owner);

    $response = $this->postJson('/api/v1/users', [...$this->payload, 'company_id' => $otherCompany->id]);

    $response->assertCreated()->assertJsonPath('data.company_id', $ownCompany->id);
});

test('an owner can list, view, update, and delete users only within their own company', function () {
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $owner = User::factory()->create(['is_owner' => true, 'company_id' => $ownCompany->id]);
    $teammate = User::factory()->create(['company_id' => $ownCompany->id]);
    $outsider = User::factory()->create(['company_id' => $otherCompany->id]);
    Sanctum::actingAs($owner);

    $this->getJson('/api/v1/users')->assertOk()->assertJsonCount(2, 'data');

    $this->getJson("/api/v1/users/{$teammate->id}")->assertOk();
    $this->getJson("/api/v1/users/{$outsider->id}")->assertNotFound();

    $this->putJson("/api/v1/users/{$teammate->id}", ['name' => 'Updated Name'])->assertOk();
    $this->putJson("/api/v1/users/{$outsider->id}", ['name' => 'Updated Name'])->assertNotFound();

    $this->deleteJson("/api/v1/users/{$outsider->id}")->assertNotFound();
    $this->deleteJson("/api/v1/users/{$teammate->id}")->assertNoContent();
});

test('creating a user requires a name, email, and password', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));

    $this->postJson('/api/v1/users', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password'], responseKey: 'data');
});

test('a user email must be unique across the platform', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    User::factory()->create(['email' => 'jane@acme-inc.com']);

    $this->postJson('/api/v1/users', $this->payload)->assertUnprocessable();
});

test('a non-owner user is forbidden from every user endpoint', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/users')->assertForbidden();
    $this->getJson("/api/v1/users/{$user->id}")->assertForbidden();
    $this->postJson('/api/v1/users', $this->payload)->assertForbidden();
    $this->putJson("/api/v1/users/{$user->id}", ['name' => 'Renamed'])->assertForbidden();
    $this->deleteJson("/api/v1/users/{$user->id}")->assertForbidden();
});

test('unauthenticated requests to user endpoints are rejected', function () {
    $this->getJson('/api/v1/users')->assertUnauthorized();
});

test('an owner cannot bypass company scoping on the users list via a qubuilder filter', function () {
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $owner = User::factory()->create(['is_owner' => true, 'company_id' => $ownCompany->id]);
    User::factory()->create(['company_id' => $otherCompany->id]);
    Sanctum::actingAs($owner);

    $query = http_build_query([
        'filter' => [['field' => 'company_id', 'op' => '=', 'value' => $otherCompany->id]],
    ]);

    // The tenant scope (company_id = ownCompany) and the client-supplied filter
    // (company_id = otherCompany) AND together into a contradiction — zero rows,
    // never a leak of the other company's data, and never a fallback to the
    // owner's own unfiltered list either.
    $this->getJson("/api/v1/users?{$query}")->assertOk()->assertJsonCount(0, 'data');
});
