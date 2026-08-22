<?php

use Domains\Core\Models\App;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Domains\Shared\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->ecommerceApp = App::factory()->create(['code' => 'ecommerce']);
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    $this->company->apps()->attach($this->ecommerceApp->id, ['assigned_by' => $this->user->id, 'assigned_at' => now()]);

    foreach (['ecommerce:categories:view', 'ecommerce:categories:manage'] as $name) {
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

test('a category can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/ecommerce/categories', ['name' => 'Electronics', 'slug' => 'electronics']);
    $response->assertCreated()->assertJsonPath('data.name', 'Electronics');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/ecommerce/categories')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/ecommerce/categories/{$id}", ['name' => 'Updated Electronics'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Electronics');

    $this->deleteJson("/api/v1/ecommerce/categories/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('categories', ['id' => $id]);
});

test('creating a category requires a name and slug', function () {
    $this->postJson('/api/v1/ecommerce/categories', [])->assertUnprocessable();
});

test('a category belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $category = Category::factory()->create(['company_id' => $otherCompany->id]);

    $this->getJson("/api/v1/ecommerce/categories/{$category->id}")->assertNotFound();
    $this->putJson("/api/v1/ecommerce/categories/{$category->id}", ['name' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/ecommerce/categories/{$category->id}")->assertNotFound();
});

test('a user without permission cannot manage categories', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id]);
    $unprivilegedUser->apps()->attach($this->ecommerceApp->id);

    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/ecommerce/categories', ['name' => 'Electronics', 'slug' => 'electronics'])
        ->assertForbidden();
});

test('slug uniqueness is scoped per company', function () {
    $otherCompany = Company::factory()->create();
    Category::factory()->create(['company_id' => $otherCompany->id, 'slug' => 'electronics']);

    $this->postJson('/api/v1/ecommerce/categories', ['name' => 'Electronics', 'slug' => 'electronics'])
        ->assertCreated();

    $this->postJson('/api/v1/ecommerce/categories', ['name' => 'Electronics Again', 'slug' => 'electronics'])
        ->assertUnprocessable();
});
