<?php

use Domains\Core\Models\App;
use Domains\Ecommerce\Models\Brand;
use Domains\Ecommerce\Models\EcommerceProduct;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Domains\Shared\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->ecommerceApp = App::factory()->create(['code' => 'ecommerce']);
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    $this->company->apps()->attach($this->ecommerceApp->id, ['assigned_by' => $this->user->id, 'assigned_at' => now()]);

    foreach (['ecommerce:products:view', 'ecommerce:products:create', 'ecommerce:products:update', 'ecommerce:products:delete'] as $name) {
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

test('creating a product returns both basic and commerce fields in one response', function () {
    $payload = [
        'name' => 'Wireless Mouse',
        'slug' => 'wireless-mouse',
        'description' => 'An ergonomic wireless mouse',
        'is_active' => true,
        'sku' => 'SKU-0001',
        'price' => 19.99,
        'stock_quantity' => 50,
    ];

    $response = $this->postJson('/api/v1/ecommerce/products', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Wireless Mouse')
        ->assertJsonPath('data.slug', 'wireless-mouse')
        ->assertJsonPath('data.description', 'An ergonomic wireless mouse')
        ->assertJsonPath('data.is_active', true)
        ->assertJsonPath('data.sku', 'SKU-0001')
        ->assertJsonPath('data.price', '19.99')
        ->assertJsonPath('data.stock_quantity', 50);

    $productId = $response->json('data.product_id');

    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'company_id' => $this->company->id,
        'name' => 'Wireless Mouse',
        'slug' => 'wireless-mouse',
    ]);

    $this->assertDatabaseHas('ecommerce_products', [
        'id' => $response->json('data.id'),
        'company_id' => $this->company->id,
        'sku' => 'SKU-0001',
        'product_id' => $productId,
    ]);
});

test('getting a single product returns both basic and commerce fields', function () {
    $product = Product::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Desk Lamp',
        'slug' => 'desk-lamp',
    ]);
    $ecommerceProduct = EcommerceProduct::factory()->create([
        'company_id' => $this->company->id,
        'product_id' => $product->id,
        'sku' => 'SKU-LAMP',
        'price' => 45.50,
    ]);

    $this->getJson("/api/v1/ecommerce/products/{$ecommerceProduct->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Desk Lamp')
        ->assertJsonPath('data.slug', 'desk-lamp')
        ->assertJsonPath('data.sku', 'SKU-LAMP')
        ->assertJsonPath('data.price', '45.50');
});

test('listing products returns multiple results', function () {
    EcommerceProduct::factory()->count(3)->create(['company_id' => $this->company->id]);

    $this->getJson('/api/v1/ecommerce/products')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('updating a product updates both basic and commerce fields', function () {
    $product = Product::factory()->create(['company_id' => $this->company->id, 'name' => 'Old Name']);
    $ecommerceProduct = EcommerceProduct::factory()->create([
        'company_id' => $this->company->id,
        'product_id' => $product->id,
        'price' => 10.00,
    ]);

    $this->putJson("/api/v1/ecommerce/products/{$ecommerceProduct->id}", [
        'name' => 'New Name',
        'price' => 25.00,
    ])->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.price', '25.00');

    $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New Name']);
    $this->assertDatabaseHas('ecommerce_products', ['id' => $ecommerceProduct->id, 'price' => 25.00]);

    $this->getJson("/api/v1/ecommerce/products/{$ecommerceProduct->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.price', '25.00');
});

test('deleting a product removes both the parent and child rows', function () {
    $product = Product::factory()->create(['company_id' => $this->company->id]);
    $ecommerceProduct = EcommerceProduct::factory()->create([
        'company_id' => $this->company->id,
        'product_id' => $product->id,
    ]);

    $this->deleteJson("/api/v1/ecommerce/products/{$ecommerceProduct->id}")->assertNoContent();

    $this->assertDatabaseMissing('ecommerce_products', ['id' => $ecommerceProduct->id]);
    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('creating a product without a sku or price is rejected', function () {
    $this->postJson('/api/v1/ecommerce/products', [
        'name' => 'Wireless Mouse',
        'slug' => 'wireless-mouse',
    ])->assertUnprocessable();
});

test('creating a product without a name or slug is rejected', function () {
    $this->postJson('/api/v1/ecommerce/products', [
        'sku' => 'SKU-0001',
        'price' => 19.99,
    ])->assertUnprocessable();
});

test('sku uniqueness is scoped per company', function () {
    EcommerceProduct::factory()->create(['company_id' => $this->company->id, 'sku' => 'DUPLICATE-SKU']);

    $this->postJson('/api/v1/ecommerce/products', [
        'name' => 'Another Product',
        'slug' => 'another-product',
        'sku' => 'DUPLICATE-SKU',
        'price' => 9.99,
    ])->assertUnprocessable();
});

test('the same sku can be used across different companies', function () {
    $otherCompany = Company::factory()->create();
    EcommerceProduct::factory()->create(['company_id' => $otherCompany->id, 'sku' => 'SHARED-SKU']);

    $this->postJson('/api/v1/ecommerce/products', [
        'name' => 'My Product',
        'slug' => 'my-product',
        'sku' => 'SHARED-SKU',
        'price' => 9.99,
    ])->assertCreated();
});

test('slug uniqueness is scoped per company', function () {
    Product::factory()->create(['company_id' => $this->company->id, 'slug' => 'duplicate-slug']);

    $this->postJson('/api/v1/ecommerce/products', [
        'name' => 'Another Product',
        'slug' => 'duplicate-slug',
        'sku' => 'SKU-UNIQUE-1',
        'price' => 9.99,
    ])->assertUnprocessable();
});

test('the same slug can be used across different companies', function () {
    $otherCompany = Company::factory()->create();
    Product::factory()->create(['company_id' => $otherCompany->id, 'slug' => 'shared-slug']);

    $this->postJson('/api/v1/ecommerce/products', [
        'name' => 'My Product',
        'slug' => 'shared-slug',
        'sku' => 'SKU-UNIQUE-2',
        'price' => 9.99,
    ])->assertCreated();
});

test('a product belonging to a different company cannot be viewed', function () {
    $otherCompany = Company::factory()->create();
    $otherProduct = Product::factory()->create(['company_id' => $otherCompany->id]);
    $ecommerceProduct = EcommerceProduct::factory()->create([
        'company_id' => $otherCompany->id,
        'product_id' => $otherProduct->id,
    ]);

    $this->getJson("/api/v1/ecommerce/products/{$ecommerceProduct->id}")->assertNotFound();
});

test('a product belonging to a different company cannot be updated', function () {
    $otherCompany = Company::factory()->create();
    $otherProduct = Product::factory()->create(['company_id' => $otherCompany->id]);
    $ecommerceProduct = EcommerceProduct::factory()->create([
        'company_id' => $otherCompany->id,
        'product_id' => $otherProduct->id,
    ]);

    $this->putJson("/api/v1/ecommerce/products/{$ecommerceProduct->id}", ['name' => 'Hijacked'])->assertNotFound();
});

test('a product belonging to a different company cannot be deleted', function () {
    $otherCompany = Company::factory()->create();
    $otherProduct = Product::factory()->create(['company_id' => $otherCompany->id]);
    $ecommerceProduct = EcommerceProduct::factory()->create([
        'company_id' => $otherCompany->id,
        'product_id' => $otherProduct->id,
    ]);

    $this->deleteJson("/api/v1/ecommerce/products/{$ecommerceProduct->id}")->assertNotFound();

    $this->assertDatabaseHas('ecommerce_products', ['id' => $ecommerceProduct->id]);
    $this->assertDatabaseHas('products', ['id' => $otherProduct->id]);
});

test('a user without permission cannot manage products', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id]);
    $unprivilegedUser->apps()->attach($this->ecommerceApp->id);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/ecommerce/products', [
        'name' => 'Wireless Mouse',
        'slug' => 'wireless-mouse',
        'sku' => 'SKU-0001',
        'price' => 19.99,
    ])->assertForbidden();
});

test('a brand_id belonging to a different company is rejected', function () {
    $otherCompany = Company::factory()->create();
    $invalidBrand = Brand::factory()->create(['company_id' => $otherCompany->id]);

    $this->postJson('/api/v1/ecommerce/products', [
        'name' => 'Wireless Mouse',
        'slug' => 'wireless-mouse',
        'sku' => 'SKU-0001',
        'price' => 19.99,
        'brand_id' => $invalidBrand->id,
    ])->assertUnprocessable();
});

test('a valid brand_id succeeds and is nested in the get response', function () {
    $brand = Brand::factory()->create(['company_id' => $this->company->id]);

    $response = $this->postJson('/api/v1/ecommerce/products', [
        'name' => 'Wireless Mouse',
        'slug' => 'wireless-mouse',
        'sku' => 'SKU-0001',
        'price' => 19.99,
        'brand_id' => $brand->id,
    ]);

    $response->assertCreated()->assertJsonPath('data.brand_id', $brand->id);

    $id = $response->json('data.id');

    $this->getJson("/api/v1/ecommerce/products/{$id}")
        ->assertOk()
        ->assertJsonPath('data.brand.id', $brand->id);
});
