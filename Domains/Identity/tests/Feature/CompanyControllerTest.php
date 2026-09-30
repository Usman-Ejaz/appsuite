<?php

use Domains\Core\Models\App;
use Domains\Identity\Enums\CompanyStatus;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function ownerPayload(string $email = 'aria@acme-inc.com'): array
{
    return ['name' => 'Aria Steinberg', 'email' => $email, 'password' => 'correct-horse-battery-staple'];
}

test('a root user can create, list, view, update, and delete a company', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $app = App::factory()->create();

    $response = $this->postJson('/api/v1/companies', [
        'name' => 'Acme Inc',
        'slug' => 'acme-inc',
        'apps' => [$app->id],
        'owner' => ownerPayload(),
    ]);
    $response->assertCreated()
        ->assertJsonPath('data.name', 'Acme Inc')
        ->assertJsonPath('data.status', CompanyStatus::ACTIVE->value)
        ->assertJsonPath('data.apps.0.id', $app->id);

    $id = $response->json('data.id');

    $this->getJson('/api/v1/companies')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/companies/{$id}")->assertOk()->assertJsonPath('data.id', $id);

    $this->putJson("/api/v1/companies/{$id}", ['name' => 'Acme Corporation', 'status' => CompanyStatus::BLOCKED->value])
        ->assertOk()
        ->assertJsonPath('data.name', 'Acme Corporation')
        ->assertJsonPath('data.status', CompanyStatus::BLOCKED->value);

    $this->deleteJson("/api/v1/companies/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('companies', ['id' => $id]);
});

test('creating a company requires a name, slug, at least one app, and an owner', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));

    $this->postJson('/api/v1/companies', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'slug', 'apps', 'owner'], responseKey: 'data');
});

test("creating a company's owner requires a name, email, and password", function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $app = App::factory()->create();

    $this->postJson('/api/v1/companies', ['name' => 'Acme Inc', 'slug' => 'acme-inc', 'apps' => [$app->id], 'owner' => []])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['owner.name', 'owner.email', 'owner.password'], responseKey: 'data');
});

test('creating a company rejects an app id that does not exist', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));

    $this->postJson('/api/v1/companies', ['name' => 'Acme Inc', 'slug' => 'acme-inc', 'apps' => [999], 'owner' => ownerPayload()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['apps.0'], responseKey: 'data');
});

test('a company slug must be unique across the platform', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $app = App::factory()->create();

    Company::factory()->create(['slug' => 'acme-inc']);

    $this->postJson('/api/v1/companies', ['name' => 'Acme Inc Again', 'slug' => 'acme-inc', 'apps' => [$app->id], 'owner' => ownerPayload()])
        ->assertUnprocessable();
});

test('updating a company syncs its app subscriptions', function () {
    $root = User::factory()->create(['is_root' => true]);
    Sanctum::actingAs($root);
    [$appA, $appB, $appC] = App::factory()->sequence(
        ['name' => 'App A', 'slug' => 'app-a', 'code' => 'crm'],
        ['name' => 'App B', 'slug' => 'app-b', 'code' => 'hr'],
        ['name' => 'App C', 'slug' => 'app-c', 'code' => 'inventory'],
    )->count(3)->create();

    $company = Company::factory()->create();
    $company->apps()->attach([$appA->id, $appB->id], ['assigned_by' => $root->id, 'assigned_at' => now()->subDay()]);

    $response = $this->putJson("/api/v1/companies/{$company->id}", ['apps' => [$appB->id, $appC->id]]);

    $response->assertOk();
    $appIds = collect($response->json('data.apps'))->pluck('id');
    expect($appIds)->toHaveCount(2)->and($appIds)->toContain($appB->id, $appC->id)->not->toContain($appA->id);

    $this->assertDatabaseMissing('company_apps', ['company_id' => $company->id, 'app_id' => $appA->id]);
    $this->assertDatabaseHas('company_apps', ['company_id' => $company->id, 'app_id' => $appC->id]);
});

test('updating a company without an apps field leaves its app subscriptions untouched', function () {
    $root = User::factory()->create(['is_root' => true]);
    Sanctum::actingAs($root);
    $app = App::factory()->create();

    $company = Company::factory()->create();
    $company->apps()->attach($app->id, ['assigned_by' => $root->id, 'assigned_at' => now()]);

    $this->putJson("/api/v1/companies/{$company->id}", ['name' => 'Renamed Co'])->assertOk();

    $this->assertDatabaseHas('company_apps', ['company_id' => $company->id, 'app_id' => $app->id]);
});

test('updating a company cannot reduce its apps to zero', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $company = Company::factory()->create();

    $this->putJson("/api/v1/companies/{$company->id}", ['apps' => []])->assertUnprocessable();
});

test('creating a company with an owner provisions a primary owner user', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $app = App::factory()->create();

    $response = $this->postJson('/api/v1/companies', [
        'name' => 'Acme Inc',
        'slug' => 'acme-inc',
        'apps' => [$app->id],
        'owner' => [...ownerPayload(), 'phone' => '+1 555-0100', 'whatsapp' => '+1 555-0100'],
    ]);

    $response->assertCreated();
    $companyId = $response->json('data.id');

    $this->assertDatabaseHas('users', [
        'company_id' => $companyId,
        'email' => 'aria@acme-inc.com',
        'is_owner' => true,
        'is_root' => false,
    ]);

    $owner = User::where('email', 'aria@acme-inc.com')->firstOrFail();
    expect(Hash::check('correct-horse-battery-staple', $owner->password))->toBeTrue();

    $this->getJson("/api/v1/companies/{$companyId}?include=".urlencode(json_encode([['name' => 'owner']])))
        ->assertOk()
        ->assertJsonPath('data.owner.email', 'aria@acme-inc.com');
});

test('creating a company rejects a duplicate owner email and rolls back the whole company', function () {
    Sanctum::actingAs(User::factory()->create(['is_root' => true]));
    $app = App::factory()->create();
    User::factory()->create(['email' => 'taken@acme-inc.com']);

    $this->postJson('/api/v1/companies', [
        'name' => 'Acme Inc',
        'slug' => 'acme-inc',
        'apps' => [$app->id],
        'owner' => ownerPayload('taken@acme-inc.com'),
    ])->assertUnprocessable()->assertJsonValidationErrors(['owner.email'], responseKey: 'data');

    $this->assertDatabaseMissing('companies', ['slug' => 'acme-inc']);
});

test('a company owner is forbidden from every company endpoint', function () {
    $company = Company::factory()->create();
    $owner = User::factory()->create(['company_id' => $company->id, 'is_owner' => true]);
    Sanctum::actingAs($owner);

    $this->getJson('/api/v1/companies')->assertForbidden();
    $this->getJson("/api/v1/companies/{$company->id}")->assertForbidden();
    $this->postJson('/api/v1/companies', ['name' => 'New Co', 'slug' => 'new-co'])->assertForbidden();
    $this->putJson("/api/v1/companies/{$company->id}", ['name' => 'Renamed'])->assertForbidden();
    $this->deleteJson("/api/v1/companies/{$company->id}")->assertForbidden();
});

test('a non-owner user is forbidden from every company endpoint', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/companies')->assertForbidden();
    $this->getJson("/api/v1/companies/{$company->id}")->assertForbidden();
});

test('unauthenticated requests to company endpoints are rejected', function () {
    $this->getJson('/api/v1/companies')->assertUnauthorized();
});
