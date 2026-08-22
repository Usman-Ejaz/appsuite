<?php

use Domains\Core\Models\App;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Customer;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->ecommerceApp = App::factory()->create(['code' => 'ecommerce']);
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    $this->company->apps()->attach($this->ecommerceApp->id, ['assigned_by' => $this->user->id, 'assigned_at' => now()]);

    foreach (['ecommerce:customers:view', 'ecommerce:customers:create', 'ecommerce:customers:update', 'ecommerce:customers:delete'] as $name) {
        $permission = Permission::create([
            'app_id' => $this->ecommerceApp->id,
            'name' => $name,
            'code' => str($name)->afterLast(':').'_test',
            'guard_name' => 'web',
        ]);
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);
});

test('a customer can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/ecommerce/customers', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
    $response->assertCreated()->assertJsonPath('data.name', 'Jane Doe');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/ecommerce/customers')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/ecommerce/customers/{$id}", ['name' => 'Jane Updated'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Jane Updated');

    $this->deleteJson("/api/v1/ecommerce/customers/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('customers', ['id' => $id]);
});

test('creating a customer requires a name and email', function () {
    $this->postJson('/api/v1/ecommerce/customers', [])->assertUnprocessable();
});

test('a customer belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $customer = Customer::factory()->create(['company_id' => $otherCompany->id]);

    $this->getJson("/api/v1/ecommerce/customers/{$customer->id}")->assertNotFound();
    $this->putJson("/api/v1/ecommerce/customers/{$customer->id}", ['name' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/ecommerce/customers/{$customer->id}")->assertNotFound();
});

test('a user without permission cannot manage customers', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => false]);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/ecommerce/customers', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ])->assertForbidden();
});

test('customer email uniqueness is scoped per company', function () {
    $otherCompany = Company::factory()->create();
    Customer::factory()->create(['company_id' => $otherCompany->id, 'email' => 'shared@example.com']);

    $this->postJson('/api/v1/ecommerce/customers', [
        'name' => 'Shared Name',
        'email' => 'shared@example.com',
    ])->assertCreated();

    $this->postJson('/api/v1/ecommerce/customers', [
        'name' => 'Another Name',
        'email' => 'shared@example.com',
    ])->assertUnprocessable();
});

test('creating a customer with an invalid email fails validation', function () {
    $this->postJson('/api/v1/ecommerce/customers', [
        'name' => 'Jane Doe',
        'email' => 'not-an-email',
    ])->assertUnprocessable();
});
