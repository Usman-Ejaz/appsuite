<?php

use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Domains\Shared\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);

    Sanctum::actingAs($this->user);
});

test('a site can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/sites', ['name' => 'Acme Blog', 'url' => 'https://blog.acme.com']);
    $response->assertCreated()->assertJsonPath('data.name', 'Acme Blog');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/sites')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/sites/{$id}", ['name' => 'Acme Blog Updated'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Acme Blog Updated');

    $this->deleteJson("/api/v1/sites/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('sites', ['id' => $id]);
});

test('creating a site requires a name and url', function () {
    $this->postJson('/api/v1/sites', [])->assertUnprocessable();
});

test('a site belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $site = Site::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Other Site', 'url' => 'https://other.example.com']);

    $this->getJson("/api/v1/sites/{$site->id}")->assertNotFound();
    $this->putJson("/api/v1/sites/{$site->id}", ['name' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/sites/{$site->id}")->assertNotFound();
});

test('status defaults to Draft and is cast correctly in the response', function () {
    $response = $this->postJson('/api/v1/sites', ['name' => 'Acme Blog', 'url' => 'https://blog.acme.com']);
    $response->assertCreated();
    expect($response->json('data.status'))->toBe('Draft');

    $id = $response->json('data.id');

    $this->putJson("/api/v1/sites/{$id}", ['status' => 'Active'])
        ->assertOk()
        ->assertJsonPath('data.status', 'Active');
});
