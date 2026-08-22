<?php

use Domains\Core\Models\App;
use Domains\Ecommerce\Models\Brand;
use Domains\Identity\Models\Company;
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

    foreach (['ecommerce:brands:view', 'ecommerce:brands:create', 'ecommerce:brands:update', 'ecommerce:brands:delete'] as $name) {
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

test('a brand can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/ecommerce/brands', ['name' => 'Acme Corp', 'slug' => 'acme-corp']);
    $response->assertCreated()->assertJsonPath('data.name', 'Acme Corp');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/ecommerce/brands')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/ecommerce/brands/{$id}", ['name' => 'Acme Corporation'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Acme Corporation');

    $this->deleteJson("/api/v1/ecommerce/brands/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('brands', ['id' => $id]);
});

test('creating a brand requires a name and slug', function () {
    $this->postJson('/api/v1/ecommerce/brands', [])->assertUnprocessable();
});

test('a brand belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $brand = Brand::factory()->create(['company_id' => $otherCompany->id]);

    $this->getJson("/api/v1/ecommerce/brands/{$brand->id}")->assertNotFound();
    $this->putJson("/api/v1/ecommerce/brands/{$brand->id}", ['name' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/ecommerce/brands/{$brand->id}")->assertNotFound();
});

test('a user without permission cannot manage brands', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id]);
    $unprivilegedUser->apps()->attach($this->ecommerceApp->id);

    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/ecommerce/brands', ['name' => 'Acme Corp', 'slug' => 'acme-corp'])
        ->assertForbidden();
});

test('slug uniqueness is scoped per company', function () {
    $otherCompany = Company::factory()->create();
    Brand::factory()->create(['company_id' => $otherCompany->id, 'slug' => 'acme-corp']);

    $this->postJson('/api/v1/ecommerce/brands', ['name' => 'Acme Corp', 'slug' => 'acme-corp'])
        ->assertCreated();

    $this->postJson('/api/v1/ecommerce/brands', ['name' => 'Acme Corp Again', 'slug' => 'acme-corp'])
        ->assertUnprocessable();
});

test('is_active is cast to boolean in the response', function () {
    $response = $this->postJson('/api/v1/ecommerce/brands', ['name' => 'Acme Corp', 'slug' => 'acme-corp']);
    $response->assertCreated();
    expect($response->json('data.is_active'))->toBeTrue();

    $id = $response->json('data.id');

    $this->putJson("/api/v1/ecommerce/brands/{$id}", ['is_active' => false])
        ->assertOk()
        ->assertJsonPath('data.is_active', false);
});
