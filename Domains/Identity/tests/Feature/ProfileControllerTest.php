<?php

use Domains\Core\Models\App;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a root user sees only the system app', function () {
    $user = User::factory()->create(['is_root' => true]);
    App::factory()->count(3)->sequence(
        ['name' => 'System', 'slug' => 'system', 'code' => 'system'],
        ['name' => 'App Two', 'slug' => 'app-two', 'code' => 'crm'],
        ['name' => 'App Three', 'slug' => 'app-three', 'code' => 'cms'],
    )->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/me')->assertOk();

    expect($response->json('data.apps'))->toHaveCount(1)
        ->and($response->json('data.apps.0.code'))->toBe('system');
});

test('a company owner sees the company\'s subscribed apps', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_owner' => true]);
    $subscribedApp = App::factory()->create(['name' => 'Subscribed App', 'slug' => 'subscribed-app', 'code' => 'crm']);
    App::factory()->create(['name' => 'Unsubscribed App', 'slug' => 'unsubscribed-app', 'code' => 'hr']);

    $company->apps()->attach($subscribedApp->id, ['assigned_by' => $user->id, 'assigned_at' => now()]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/me')->assertOk();

    expect($response->json('data.apps'))->toHaveCount(1)
        ->and($response->json('data.apps.0.code'))->toBe($subscribedApp->code->value);
});

test('a regular user only sees apps individually granted to them', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $grantedApp = App::factory()->create(['name' => 'Granted App', 'slug' => 'granted-app', 'code' => 'crm']);
    $companyOnlyApp = App::factory()->create(['name' => 'Company Only App', 'slug' => 'company-only-app', 'code' => 'hr']);

    $company->apps()->attach(
        [$grantedApp->id, $companyOnlyApp->id],
        ['assigned_by' => $user->id, 'assigned_at' => now()]
    );
    $user->apps()->attach($grantedApp->id);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/me')->assertOk();

    expect($response->json('data.apps'))->toHaveCount(1)
        ->and($response->json('data.apps.0.code'))->toBe($grantedApp->code->value);
});

test('recent_app prefers last_app when it is in the resolved apps list', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_owner' => true, 'last_app' => 'crm']);
    $appA = App::factory()->create(['name' => 'Alpha', 'slug' => 'alpha', 'code' => 'cms']);
    $appB = App::factory()->create(['name' => 'Beta', 'slug' => 'beta', 'code' => 'crm']);

    $company->apps()->attach([$appA->id, $appB->id], ['assigned_by' => $user->id, 'assigned_at' => now()]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me')->assertOk()->assertJson(['data' => ['recent_app' => 'crm']]);
});

test('recent_app falls back to alphabetically-first app when last_app is not in the list', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_owner' => true, 'last_app' => 'nonexistent']);
    $appA = App::factory()->create(['name' => 'Alpha', 'slug' => 'alpha', 'code' => 'cms']);
    $appB = App::factory()->create(['name' => 'Beta', 'slug' => 'beta', 'code' => 'crm']);

    $company->apps()->attach([$appA->id, $appB->id], ['assigned_by' => $user->id, 'assigned_at' => now()]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me')->assertOk()->assertJson(['data' => ['recent_app' => 'cms']]);
});

test('permissions are scoped to the current app, with global permissions always included', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_owner' => true, 'last_app' => 'crm']);
    $currentApp = App::factory()->create(['name' => 'CRM App', 'slug' => 'crm-app', 'code' => 'crm']);
    $otherApp = App::factory()->create(['name' => 'CMS App', 'slug' => 'cms-app', 'code' => 'cms']);

    $company->apps()->attach([$currentApp->id, $otherApp->id], ['assigned_by' => $user->id, 'assigned_at' => now()]);

    $currentAppPermission = Permission::create(['app_id' => $currentApp->id, 'name' => 'crm:contacts:view', 'guard_name' => 'web']);
    $otherAppPermission = Permission::create(['app_id' => $otherApp->id, 'name' => 'cms:pages:view', 'guard_name' => 'web']);
    $globalPermission = Permission::create(['app_id' => null, 'name' => 'platform:settings:view', 'guard_name' => 'web']);

    $user->givePermissionTo([$currentAppPermission, $otherAppPermission, $globalPermission]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/me')->assertOk();

    expect($response->json('data.permissions'))
        ->toContain('crm:contacts:view')
        ->toContain('platform:settings:view')
        ->not->toContain('cms:pages:view');
});

test('company sub-object includes name, status, and a null plan placeholder', function () {
    $company = Company::factory()->create(['name' => 'Acme Inc']);
    $user = User::factory()->create(['company_id' => $company->id]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJson(['data' => ['company' => ['name' => 'Acme Inc', 'plan' => null]]]);
});

test('unauthenticated requests to me are rejected', function () {
    $this->getJson('/api/v1/me')->assertUnauthorized();
});
