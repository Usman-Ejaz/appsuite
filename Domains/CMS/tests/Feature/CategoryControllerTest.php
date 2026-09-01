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
    $this->cmsApp = App::factory()->create(['code' => 'cms']);
    $this->company = Company::factory()->create();
    $this->company->apps()->attach($this->cmsApp->id);
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);

    foreach (['cms:categories:view', 'cms:categories:manage'] as $name) {
        $permission = Permission::create([
            'app_id' => $this->cmsApp->id,
            'name' => $name,
            'code' => str($name)->afterLast(':').'_test',
            'guard_name' => 'web',
        ]);
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);
});

test('a category can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/cms/categories', ['name' => 'News', 'slug' => 'news']);
    $response->assertCreated()
        ->assertJsonPath('data.name', 'News')
        ->assertJsonPath('data.app_code', 'cms');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/cms/categories')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/cms/categories/{$id}", ['name' => 'Updates'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updates');

    $this->deleteJson("/api/v1/cms/categories/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('categories', ['id' => $id]);
});

test('slug uniqueness is scoped per company', function () {
    Category::factory()->create(['company_id' => $this->company->id, 'app_code' => 'cms', 'slug' => 'news']);

    $this->postJson('/api/v1/cms/categories', ['name' => 'News Again', 'slug' => 'news'])
        ->assertUnprocessable();
});

test('a category belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $category = Category::factory()->create(['company_id' => $otherCompany->id, 'app_code' => 'cms']);

    $this->getJson("/api/v1/cms/categories/{$category->id}")->assertNotFound();
    $this->putJson("/api/v1/cms/categories/{$category->id}", ['name' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/cms/categories/{$category->id}")->assertNotFound();
});

test('a category belonging to a different app is not found', function () {
    $category = Category::factory()->create(['company_id' => $this->company->id, 'app_code' => 'ecommerce']);

    $this->getJson("/api/v1/cms/categories/{$category->id}")->assertNotFound();
});

test('a user without permission cannot manage categories', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/cms/categories', ['name' => 'News', 'slug' => 'news'])->assertForbidden();
    $this->getJson('/api/v1/cms/categories')->assertForbidden();
});
