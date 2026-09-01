<?php

use Domains\Core\Models\App;
use Domains\Ecommerce\Models\Order;
use Domains\Ecommerce\Models\OrderItem;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Customer;
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

    foreach (['ecommerce:orders:view', 'ecommerce:orders:update'] as $name) {
        $permission = Permission::create([
            'app_id' => $this->ecommerceApp->id,
            'name' => $name,
            'code' => str($name)->afterLast(':').'_test',
            'guard_name' => 'web',
        ]);
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);

    $this->customer = Customer::factory()->create(['company_id' => $this->company->id]);
    $this->order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);
});

test('an item can be added, listed, updated, and removed, adjusting stock and order totals throughout', function () {
    $product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id, 'stock_quantity' => 20, 'price' => 25]);

    $response = $this->postJson("/api/v1/ecommerce/orders/{$this->order->id}/items", [
        'product_id' => $product->id,
        'quantity' => 4,
    ]);
    $response->assertCreated()
        ->assertJsonPath('data.product_name', $product->name)
        ->assertJsonPath('data.unit_price', '25.00')
        ->assertJsonPath('data.subtotal', '100.00');

    $itemId = $response->json('data.id');

    expect($product->fresh()->stock_quantity)->toBe(16);
    expect($this->order->fresh()->subtotal)->toBe('100.00');
    expect($this->order->fresh()->total)->toBe('100.00');

    $this->getJson("/api/v1/ecommerce/orders/{$this->order->id}/items")->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/ecommerce/orders/{$this->order->id}/items/{$itemId}")->assertOk();

    // Increasing quantity consumes more stock.
    $this->putJson("/api/v1/ecommerce/orders/{$this->order->id}/items/{$itemId}", ['quantity' => 6])
        ->assertOk()
        ->assertJsonPath('data.subtotal', '150.00');

    expect($product->fresh()->stock_quantity)->toBe(14);
    expect($this->order->fresh()->subtotal)->toBe('150.00');

    // Decreasing quantity restores stock.
    $this->putJson("/api/v1/ecommerce/orders/{$this->order->id}/items/{$itemId}", ['quantity' => 2])
        ->assertOk()
        ->assertJsonPath('data.subtotal', '50.00');

    expect($product->fresh()->stock_quantity)->toBe(18);

    $this->deleteJson("/api/v1/ecommerce/orders/{$this->order->id}/items/{$itemId}")->assertNoContent();

    expect($product->fresh()->stock_quantity)->toBe(20);
    expect($this->order->fresh()->subtotal)->toBe('0.00');
    $this->assertDatabaseMissing('order_items', ['id' => $itemId]);
});

test('adding more items than available stock is rejected', function () {
    $product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id, 'stock_quantity' => 2, 'track_inventory' => true]);

    $this->postJson("/api/v1/ecommerce/orders/{$this->order->id}/items", [
        'product_id' => $product->id,
        'quantity' => 5,
    ])->assertUnprocessable();

    expect($product->fresh()->stock_quantity)->toBe(2);
});

test('a product with inventory tracking disabled ignores stock limits', function () {
    $product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id, 'stock_quantity' => 1, 'track_inventory' => false]);

    $this->postJson("/api/v1/ecommerce/orders/{$this->order->id}/items", [
        'product_id' => $product->id,
        'quantity' => 50,
    ])->assertCreated();

    expect($product->fresh()->stock_quantity)->toBe(1);
});

test('increasing quantity beyond available stock is rejected and leaves the item unchanged', function () {
    $product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id, 'stock_quantity' => 5]);

    $response = $this->postJson("/api/v1/ecommerce/orders/{$this->order->id}/items", [
        'product_id' => $product->id,
        'quantity' => 3,
    ]);
    $itemId = $response->json('data.id');

    // 2 left in stock; asking to go to 10 needs 7 more than available.
    $this->putJson("/api/v1/ecommerce/orders/{$this->order->id}/items/{$itemId}", ['quantity' => 10])->assertUnprocessable();

    expect(OrderItem::find($itemId)->quantity)->toBe(3);
});

test('line items snapshot the product name, sku, and price at the time of purchase', function () {
    $product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id, 'sku' => 'ORIGINAL-SKU', 'price' => 30, 'stock_quantity' => 10]);

    $response = $this->postJson("/api/v1/ecommerce/orders/{$this->order->id}/items", [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
    $itemId = $response->json('data.id');

    $product->update(['sku' => 'CHANGED-SKU', 'price' => 999]);

    $this->getJson("/api/v1/ecommerce/orders/{$this->order->id}/items/{$itemId}")
        ->assertOk()
        ->assertJsonPath('data.product_sku', 'ORIGINAL-SKU')
        ->assertJsonPath('data.unit_price', '30.00');
});

test('an item belonging to a different order is not found under this order\'s nested route', function () {
    $otherOrder = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);
    $product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id, 'stock_quantity' => 10]);

    $response = $this->postJson("/api/v1/ecommerce/orders/{$otherOrder->id}/items", [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
    $itemId = $response->json('data.id');

    // Same company, correct item id, but the WRONG parent order in the URL.
    $this->getJson("/api/v1/ecommerce/orders/{$this->order->id}/items/{$itemId}")->assertNotFound();
    $this->putJson("/api/v1/ecommerce/orders/{$this->order->id}/items/{$itemId}", ['quantity' => 2])->assertNotFound();
    $this->deleteJson("/api/v1/ecommerce/orders/{$this->order->id}/items/{$itemId}")->assertNotFound();
});

test('items on an order belonging to a different company are not found', function () {
    $otherCompany = Company::factory()->create();
    $otherCustomer = Customer::factory()->create(['company_id' => $otherCompany->id]);
    $otherOrder = Order::factory()->create(['company_id' => $otherCompany->id, 'customer_id' => $otherCustomer->id]);

    $this->getJson("/api/v1/ecommerce/orders/{$otherOrder->id}/items")->assertNotFound();
});

test('a user without permission cannot add items to an order', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => false]);
    Sanctum::actingAs($unprivilegedUser);

    $product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id]);

    $this->postJson("/api/v1/ecommerce/orders/{$this->order->id}/items", [
        'product_id' => $product->id,
        'quantity' => 1,
    ])->assertForbidden();
});

test('adding an item requires a valid product belonging to the company', function () {
    $this->postJson("/api/v1/ecommerce/orders/{$this->order->id}/items", ['quantity' => 1])->assertUnprocessable();

    $otherCompany = Company::factory()->create();
    $otherProduct = Product::factory()->ecommerce()->create(['company_id' => $otherCompany->id]);

    $this->postJson("/api/v1/ecommerce/orders/{$this->order->id}/items", [
        'product_id' => $otherProduct->id,
        'quantity' => 1,
    ])->assertUnprocessable();
});
