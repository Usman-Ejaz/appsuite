<?php

use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\Role;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a root user can create, list, view, update, and delete a role', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $permission = Permission::create(['name' => 'crm:contacts:view', 'code' => 'contacts_view', 'guard_name' => 'web']);

    $response = $this->postJson('/api/v1/roles', ['name' => 'Editor', 'permissions' => [$permission->id]]);
    $response->assertCreated()
        ->assertJsonPath('data.name', 'Editor')
        ->assertJsonPath('data.permissions.0.id', $permission->id);

    $id = $response->json('data.id');

    $this->getJson('/api/v1/roles')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/roles/{$id}")->assertOk()->assertJsonPath('data.id', $id);

    $this->putJson("/api/v1/roles/{$id}", ['name' => 'Senior Editor'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Senior Editor');

    $this->deleteJson("/api/v1/roles/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('roles', ['id' => $id]);
});

test('a root user creating a role without a company_id defaults to their own company', function () {
    $company = Company::factory()->create();
    $root = User::factory()->create(['is_root' => true, 'company_id' => $company->id]);
    Sanctum::actingAs($root);

    $response = $this->postJson('/api/v1/roles', ['name' => 'Editor']);

    $response->assertCreated()->assertJsonPath('data.company_id', $company->id);
});

test('a root user creating a role can target an explicit company_id', function () {
    $rootCompany = Company::factory()->create();
    $targetCompany = Company::factory()->create();
    $root = User::factory()->create(['is_root' => true, 'company_id' => $rootCompany->id]);
    Sanctum::actingAs($root);

    $response = $this->postJson('/api/v1/roles', ['name' => 'Editor', 'company_id' => $targetCompany->id]);

    $response->assertCreated()->assertJsonPath('data.company_id', $targetCompany->id);
});

test('an owner creating a role is always placed into the owner\'s own company, regardless of company_id', function () {
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $owner = User::factory()->create(['is_owner' => true, 'company_id' => $ownCompany->id]);
    Sanctum::actingAs($owner);

    $response = $this->postJson('/api/v1/roles', ['name' => 'Editor', 'company_id' => $otherCompany->id]);

    $response->assertCreated()->assertJsonPath('data.company_id', $ownCompany->id);
});

test('an owner can list, view, update, and delete roles only within their own company', function () {
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $owner = User::factory()->create(['is_owner' => true, 'company_id' => $ownCompany->id]);
    $ownRole = $ownCompany->roles()->create(['name' => 'Editor', 'guard_name' => 'web']);
    $otherRole = $otherCompany->roles()->create(['name' => 'Editor', 'guard_name' => 'web']);
    Sanctum::actingAs($owner);

    $this->getJson('/api/v1/roles')->assertOk()->assertJsonCount(1, 'data');

    $this->getJson("/api/v1/roles/{$ownRole->id}")->assertOk();
    $this->getJson("/api/v1/roles/{$otherRole->id}")->assertNotFound();

    $this->putJson("/api/v1/roles/{$ownRole->id}", ['name' => 'Updated'])->assertOk();
    $this->putJson("/api/v1/roles/{$otherRole->id}", ['name' => 'Updated'])->assertNotFound();

    $this->deleteJson("/api/v1/roles/{$otherRole->id}")->assertNotFound();
    $this->deleteJson("/api/v1/roles/{$ownRole->id}")->assertNoContent();
});

test('creating a role requires a name', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));

    $this->postJson('/api/v1/roles', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name'], responseKey: 'data');
});

test('a role name is unique per company but not globally', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $rootA = User::factory()->create(['is_root' => true, 'company_id' => $companyA->id]);
    $rootB = User::factory()->create(['is_root' => true, 'company_id' => $companyB->id]);

    Sanctum::actingAs($rootA);
    $this->postJson('/api/v1/roles', ['name' => 'Owner'])->assertCreated();
    $this->postJson('/api/v1/roles', ['name' => 'Owner'])->assertUnprocessable();

    Sanctum::actingAs($rootB);
    $this->postJson('/api/v1/roles', ['name' => 'Owner'])->assertCreated();
});

test('updating a role syncs its permissions', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $permA = Permission::create(['name' => 'perm.a', 'code' => 'perm_a', 'guard_name' => 'web']);
    $permB = Permission::create(['name' => 'perm.b', 'code' => 'perm_b', 'guard_name' => 'web']);
    $permC = Permission::create(['name' => 'perm.c', 'code' => 'perm_c', 'guard_name' => 'web']);

    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
    $role->syncPermissions([$permA, $permB]);

    $response = $this->putJson("/api/v1/roles/{$role->id}", ['permissions' => [$permB->id, $permC->id]]);

    $response->assertOk();
    $permIds = collect($response->json('data.permissions'))->pluck('id');
    expect($permIds)->toHaveCount(2)->and($permIds)->toContain($permB->id, $permC->id)->not->toContain($permA->id);
});

test('updating a role without a permissions field leaves its permissions untouched', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $permission = Permission::create(['name' => 'perm.a', 'code' => 'perm_a', 'guard_name' => 'web']);
    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
    $role->syncPermissions([$permission]);

    $this->putJson("/api/v1/roles/{$role->id}", ['name' => 'Renamed'])->assertOk();

    expect($role->fresh()->permissions->pluck('id'))->toContain($permission->id);
});

test('a non-owner user is forbidden from every role endpoint', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $role = $company->roles()->create(['name' => 'Editor', 'guard_name' => 'web']);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/roles')->assertForbidden();
    $this->getJson("/api/v1/roles/{$role->id}")->assertForbidden();
    $this->postJson('/api/v1/roles', ['name' => 'New Role'])->assertForbidden();
    $this->putJson("/api/v1/roles/{$role->id}", ['name' => 'Renamed'])->assertForbidden();
    $this->deleteJson("/api/v1/roles/{$role->id}")->assertForbidden();
});

test('unauthenticated requests to role endpoints are rejected', function () {
    $this->getJson('/api/v1/roles')->assertUnauthorized();
});

test('an owner cannot bypass company scoping on the roles list via a qubuilder filter', function () {
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $owner = User::factory()->create(['is_owner' => true, 'company_id' => $ownCompany->id]);
    $ownCompany->roles()->create(['name' => 'Editor', 'guard_name' => 'web']);
    $otherCompany->roles()->create(['name' => 'Manager', 'guard_name' => 'web']);
    Sanctum::actingAs($owner);

    $query = http_build_query([
        'filter' => [['field' => 'company_id', 'op' => '=', 'value' => $otherCompany->id]],
    ]);

    $this->getJson("/api/v1/roles?{$query}")->assertOk()->assertJsonCount(0, 'data');
});
