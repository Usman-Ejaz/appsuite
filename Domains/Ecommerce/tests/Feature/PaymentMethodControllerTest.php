<?php

use Domains\Core\Models\App;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Domains\Shared\Enums\PaymentMethodType;
use Domains\Shared\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->ecommerceApp = App::factory()->create(['code' => 'ecommerce']);
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    $this->company->apps()->attach($this->ecommerceApp->id, ['assigned_by' => $this->user->id, 'assigned_at' => now()]);

    foreach ([
        'ecommerce:payment-methods:view', 'ecommerce:payment-methods:create',
        'ecommerce:payment-methods:update', 'ecommerce:payment-methods:delete',
    ] as $name) {
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

test('a payment method can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/ecommerce/payment-methods', [
        'name' => 'Cash Register',
        'type' => PaymentMethodType::BANK_TRANSFER->value,
    ]);
    $response->assertCreated()
        ->assertJsonPath('data.name', 'Cash Register')
        ->assertJsonPath('data.type', 'Bank Transfer')
        ->assertJsonPath('data.is_active', true)
        ->assertJsonPath('data.is_default', false);

    $id = $response->json('data.id');

    $this->getJson('/api/v1/ecommerce/payment-methods')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/ecommerce/payment-methods/{$id}", ['name' => 'Updated Cash Register'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Cash Register');

    $this->deleteJson("/api/v1/ecommerce/payment-methods/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('payment_methods', ['id' => $id]);
});

test('creating a payment method requires a name and type', function () {
    $this->postJson('/api/v1/ecommerce/payment-methods', [])->assertUnprocessable();
});

test('payment method name uniqueness is scoped per company', function () {
    PaymentMethod::factory()->create(['company_id' => $this->company->id, 'name' => 'Cash Register']);

    $this->postJson('/api/v1/ecommerce/payment-methods', [
        'name' => 'Cash Register',
        'type' => PaymentMethodType::CASH->value,
    ])->assertUnprocessable();
});

test('a payment method belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $paymentMethod = PaymentMethod::factory()->create(['company_id' => $otherCompany->id]);

    $this->getJson("/api/v1/ecommerce/payment-methods/{$paymentMethod->id}")->assertNotFound();
    $this->putJson("/api/v1/ecommerce/payment-methods/{$paymentMethod->id}", ['name' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/ecommerce/payment-methods/{$paymentMethod->id}")->assertNotFound();
});

test('a user without permission cannot manage payment methods', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => false]);
    $unprivilegedUser->apps()->attach($this->ecommerceApp->id);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/ecommerce/payment-methods', [
        'name' => 'Cash Register',
        'type' => PaymentMethodType::CASH->value,
    ])->assertForbidden();
});
