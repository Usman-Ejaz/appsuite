<?php

use Domains\Core\Models\App;
use Domains\Core\Repositories\AppRepository;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a root user can create, list, view, update, and delete an app', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));

    $response = $this->postJson('/api/v1/apps', ['name' => 'CRM', 'slug' => 'crm', 'code' => 'crm']);
    $response->assertCreated()
        ->assertJsonPath('data.name', 'CRM')
        ->assertJsonPath('data.code', 'crm');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/apps')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/apps/{$id}")->assertOk()->assertJsonPath('data.id', $id);

    $this->putJson("/api/v1/apps/{$id}", ['name' => 'Customer Relationship Management'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Customer Relationship Management');

    $this->deleteJson("/api/v1/apps/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('apps', ['id' => $id]);
});

test('creating an app requires a name, slug, and code', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));

    $this->postJson('/api/v1/apps', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'slug', 'code'], responseKey: 'data');
});

test('an app slug and code must each be unique across the platform', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));

    App::factory()->create(['slug' => 'crm', 'code' => 'crm']);

    $this->postJson('/api/v1/apps', ['name' => 'CRM Again', 'slug' => 'crm', 'code' => 'crm-2'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['slug'], responseKey: 'data');

    $this->postJson('/api/v1/apps', ['name' => 'CRM Again', 'slug' => 'crm-2', 'code' => 'crm'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code'], responseKey: 'data');
});

test('updating an app can keep its own slug and code', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $app = App::factory()->create(['slug' => 'crm', 'code' => 'crm']);

    $this->putJson("/api/v1/apps/{$app->id}", ['slug' => 'crm', 'code' => 'crm', 'name' => 'CRM'])
        ->assertOk()
        ->assertJsonPath('data.slug', 'crm');
});

test('a non-root user is forbidden from every app endpoint', function () {
    $app = App::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/apps')->assertForbidden();
    $this->getJson("/api/v1/apps/{$app->id}")->assertForbidden();
    $this->postJson('/api/v1/apps', ['name' => 'CRM', 'slug' => 'crm', 'code' => 'crm'])->assertForbidden();
    $this->putJson("/api/v1/apps/{$app->id}", ['name' => 'Renamed'])->assertForbidden();
    $this->deleteJson("/api/v1/apps/{$app->id}")->assertForbidden();
});

test('unauthenticated requests to app endpoints are rejected', function () {
    $this->getJson('/api/v1/apps')->assertUnauthorized();
});

test('the apps list can be filtered by a qubuilder filter', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    App::factory()->create(['name' => 'CRM', 'slug' => 'crm', 'code' => 'crm', 'is_active' => true]);
    App::factory()->create(['name' => 'HR', 'slug' => 'hr', 'code' => 'hr', 'is_active' => false]);

    $query = http_build_query([
        'filter' => [['field' => 'is_active', 'op' => '=', 'value' => 1]],
    ]);

    $this->getJson("/api/v1/apps?{$query}")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'CRM');
});

test('the apps list can be sorted by a qubuilder sort param', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    App::factory()->create(['name' => 'Zeta', 'slug' => 'zeta', 'code' => 'crm']);
    App::factory()->create(['name' => 'Alpha', 'slug' => 'alpha', 'code' => 'hr']);

    $query = http_build_query(['sort' => ['name' => 'asc']]);

    $this->getJson("/api/v1/apps?{$query}")
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Alpha');
});

test('the apps list rejects a limit above the configured max', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));

    $this->getJson('/api/v1/apps?limit=9999')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['limit'], responseKey: 'data');
});

test('the base repository clamps an out-of-range limit for callers that bypass GetCollectionRequest validation', function () {
    $paginator = app(AppRepository::class)->list(['limit' => -5]);

    expect($paginator->perPage())->toBe(config('qubuilder.limit.max'));
});

test('a single app can be fetched with a restricted select', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $app = App::factory()->create(['name' => 'CRM', 'slug' => 'crm', 'code' => 'crm']);

    $query = http_build_query(['select' => ['id', 'name']]);

    // AppResource always renders every key (it doesn't guard fields with whenHas()) — a
    // restricted select narrows what qubuilder actually fetches from the DB, so unselected
    // columns still appear in the JSON, just as null rather than their real stored value.
    $this->getJson("/api/v1/apps/{$app->id}?{$query}")
        ->assertOk()
        ->assertJsonPath('data.id', $app->id)
        ->assertJsonPath('data.name', 'CRM')
        ->assertJsonPath('data.slug', null);
});

test('the apps list can eager-load permissions via a qubuilder include', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $app = App::factory()->create(['name' => 'CRM', 'slug' => 'crm', 'code' => 'crm']);
    Permission::create(['app_id' => $app->id, 'name' => 'crm:contacts:view', 'code' => 'contacts_view', 'guard_name' => 'web']);

    $query = http_build_query(['include' => [['name' => 'permissions']]]);

    $this->getJson("/api/v1/apps?{$query}")
        ->assertOk()
        ->assertJsonCount(1, 'data.0.permissions')
        ->assertJsonPath('data.0.permissions.0.name', 'crm:contacts:view');
});
