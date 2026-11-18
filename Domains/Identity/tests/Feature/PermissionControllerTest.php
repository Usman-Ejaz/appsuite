<?php

use Domains\Core\Models\App;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a root user can list and view permissions', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $permission = Permission::create(['name' => 'crm:contacts:view', 'label' => 'View Contacts', 'guard_name' => 'web']);

    $this->getJson('/api/v1/permissions')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/permissions/{$permission->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'crm:contacts:view')
        ->assertJsonPath('data.label', 'View Contacts');
});

test('an owner can list and view permissions', function () {
    Sanctum::actingAs(User::factory()->create(['is_owner' => true]));
    Permission::create(['name' => 'crm:contacts:view', 'guard_name' => 'web']);

    $this->getJson('/api/v1/permissions')->assertOk()->assertJsonCount(1, 'data');
});

test('permissions can be filtered by app_id', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $crm = App::factory()->create(['code' => 'crm']);
    $hr = App::factory()->create(['code' => 'hr']);
    Permission::create(['app_id' => $crm->id, 'name' => 'crm:contacts:view', 'guard_name' => 'web']);
    Permission::create(['app_id' => $hr->id, 'name' => 'hr:employees:view', 'guard_name' => 'web']);

    $filter = json_encode(['field' => 'app_id', 'op' => '=', 'value' => $crm->id]);

    $response = $this->getJson('/api/v1/permissions?'.http_build_query(['filter' => $filter]));

    $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.app_id', $crm->id);
});

test('a non-owner user is forbidden from the permissions endpoints', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => 'crm:contacts:view', 'guard_name' => 'web']);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/permissions')->assertForbidden();
    $this->getJson("/api/v1/permissions/{$permission->id}")->assertForbidden();
});

test('unauthenticated requests to permission endpoints are rejected', function () {
    $this->getJson('/api/v1/permissions')->assertUnauthorized();
});
