<?php

use Domains\Core\Models\App;
use Domains\Ecommerce\Models\Collection;
use Domains\Ecommerce\Models\EcommerceProduct;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->ecommerceApp = App::factory()->create(['code' => 'ecommerce']);
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    $this->company->apps()->attach($this->ecommerceApp->id, ['assigned_by' => $this->user->id, 'assigned_at' => now()]);

    foreach (['ecommerce:collections:view', 'ecommerce:collections:create', 'ecommerce:collections:update', 'ecommerce:collections:delete'] as $name) {
        $permission = Permission::create([
            'app_id' => $this->ecommerceApp->id,
            'name' => $name,
            'code' => str($name)->afterLast(':').'_test',
            'guard_name' => 'web',
        ]);
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);

    // Defensive belt-and-suspenders: Domains\Core\app\Traits\HasCompany's
    // CompanyScope is bound once per model class per process. At the time
    // these tests were written it was captured from `request()->user()`
    // at boot time, which is null when the model's first touch in a test
    // is a direct factory call rather than an actual HTTP dispatch - that
    // silently disables company scoping for the rest of the test. Wiring
    // the resolver here keeps it correct regardless of what touches the
    // model first, and regardless of which resolution strategy the scope
    // itself uses. See bug note in the final report.
    request()->setUserResolver(fn () => Auth::user());
});

/**
 * Create a record that belongs to a company other than the currently
 * acting one. Acting as a user from the target company while creating the
 * fixture guarantees the correct company_id regardless of exactly how
 * Domains\Core\app\Traits\HasCompany derives it on creation - see bug
 * note in the final report for the specific issue this guarded against
 * at the time these tests were written.
 */
function createCollectionForCompany(Company $company, array $attributes = []): Collection
{
    $owner = User::factory()->create(['company_id' => $company->id]);
    Sanctum::actingAs($owner);

    $collection = Collection::factory()->create(array_merge(['company_id' => $company->id], $attributes));

    return $collection;
}

function createEcommerceProductForCompany(Company $company, array $attributes = []): EcommerceProduct
{
    $owner = User::factory()->create(['company_id' => $company->id]);
    Sanctum::actingAs($owner);

    $product = EcommerceProduct::factory()->create(array_merge(['company_id' => $company->id], $attributes));

    return $product;
}

test('a collection can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/ecommerce/collections', [
        'name' => 'Summer Sale',
        'slug' => 'summer-sale',
    ]);
    $response->assertCreated()->assertJsonPath('data.name', 'Summer Sale');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/ecommerce/collections')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/ecommerce/collections/{$id}", ['name' => 'Winter Sale'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Winter Sale');

    $this->deleteJson("/api/v1/ecommerce/collections/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('collections', ['id' => $id]);
});

test('creating a collection requires a name and slug', function () {
    $this->postJson('/api/v1/ecommerce/collections', [])->assertUnprocessable();
});

test('a collection belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $collection = createCollectionForCompany($otherCompany);
    Sanctum::actingAs($this->user);

    $this->getJson("/api/v1/ecommerce/collections/{$collection->id}")->assertNotFound();
    $this->putJson("/api/v1/ecommerce/collections/{$collection->id}", ['name' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/ecommerce/collections/{$collection->id}")->assertNotFound();
});

test('a user without permission cannot manage collections', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id]);
    $unprivilegedUser->apps()->attach($this->ecommerceApp->id);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/ecommerce/collections', ['name' => 'Summer Sale', 'slug' => 'summer-sale'])
        ->assertForbidden();
});

test('slug uniqueness is scoped per company', function () {
    Collection::factory()->create(['company_id' => $this->company->id, 'slug' => 'summer-sale']);

    $this->postJson('/api/v1/ecommerce/collections', ['name' => 'Another', 'slug' => 'summer-sale'])
        ->assertUnprocessable();
});

test('syncing products attaches them and returns them nested on the collection', function () {
    $collection = Collection::factory()->create(['company_id' => $this->company->id]);
    $productA = EcommerceProduct::factory()->create(['company_id' => $this->company->id]);
    $productB = EcommerceProduct::factory()->create(['company_id' => $this->company->id]);

    $response = $this->putJson("/api/v1/ecommerce/collections/{$collection->id}/products", [
        'products' => [
            ['id' => $productA->id, 'sort_order' => 1],
            ['id' => $productB->id, 'sort_order' => 0],
        ],
    ]);

    $response->assertOk();

    $ids = collect($response->json('data.products'))->pluck('id')->all();
    expect($ids)->toContain($productA->id)->toContain($productB->id);
});

test('syncing a product that does not belong to the company is rejected', function () {
    $collection = Collection::factory()->create(['company_id' => $this->company->id]);

    $otherCompany = Company::factory()->create();
    $foreignProduct = createEcommerceProductForCompany($otherCompany);
    Sanctum::actingAs($this->user);

    $this->putJson("/api/v1/ecommerce/collections/{$collection->id}/products", [
        'products' => [
            ['id' => $foreignProduct->id],
        ],
    ])->assertUnprocessable();
});

test('syncing products replaces the previous set instead of appending to it', function () {
    $collection = Collection::factory()->create(['company_id' => $this->company->id]);
    $productA = EcommerceProduct::factory()->create(['company_id' => $this->company->id]);
    $productB = EcommerceProduct::factory()->create(['company_id' => $this->company->id]);

    $this->putJson("/api/v1/ecommerce/collections/{$collection->id}/products", [
        'products' => [['id' => $productA->id]],
    ])->assertOk();

    $response = $this->putJson("/api/v1/ecommerce/collections/{$collection->id}/products", [
        'products' => [['id' => $productB->id]],
    ])->assertOk();

    $ids = collect($response->json('data.products'))->pluck('id')->all();
    expect($ids)->toContain($productB->id)->not->toContain($productA->id);
});
