<?php

use Domains\Core\Models\App;
use Domains\Ecommerce\Enums\CouponType;
use Domains\Ecommerce\Enums\OrderStatus;
use Domains\Ecommerce\Models\Coupon;
use Domains\Ecommerce\Models\Order;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Customer;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Domains\Shared\Models\PaymentMethod;
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

    foreach ([
        'ecommerce:orders:view', 'ecommerce:orders:create', 'ecommerce:orders:update',
        'ecommerce:orders:delete', 'ecommerce:orders:cancel', 'ecommerce:orders:apply-coupon',
    ] as $name) {
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
});

test('an order can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/ecommerce/orders', ['customer_id' => $this->customer->id]);
    $response->assertCreated()
        ->assertJsonPath('data.customer_id', $this->customer->id)
        ->assertJsonPath('data.status', 'Pending')
        ->assertJsonPath('data.total', '0.00');

    expect($response->json('data.order_number'))->toStartWith('ORD-');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/ecommerce/orders')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/ecommerce/orders/{$id}", ['notes' => 'Please gift wrap'])
        ->assertOk()
        ->assertJsonPath('data.notes', 'Please gift wrap');

    $this->deleteJson("/api/v1/ecommerce/orders/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('orders', ['id' => $id]);
});

test('creating an order requires a valid customer', function () {
    $this->postJson('/api/v1/ecommerce/orders', [])->assertUnprocessable();

    $otherCompany = Company::factory()->create();
    $otherCustomer = Customer::factory()->create(['company_id' => $otherCompany->id]);

    $this->postJson('/api/v1/ecommerce/orders', ['customer_id' => $otherCustomer->id])->assertUnprocessable();
});

test('order_number is unique per company and auto-generated', function () {
    $first = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);
    $second = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    expect($first->order_number)->not->toBe($second->order_number);
});

test('status cannot be set to Cancelled via a plain update', function () {
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $this->putJson("/api/v1/ecommerce/orders/{$order->id}", ['status' => 'Cancelled'])->assertUnprocessable();
});

test('status can be updated to a non-cancelled value via plain update', function () {
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $this->putJson("/api/v1/ecommerce/orders/{$order->id}", ['status' => 'Processing'])
        ->assertOk()
        ->assertJsonPath('data.status', 'Processing');
});

test('an order belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $otherCustomer = Customer::factory()->create(['company_id' => $otherCompany->id]);
    $order = Order::factory()->create(['company_id' => $otherCompany->id, 'customer_id' => $otherCustomer->id]);

    $this->getJson("/api/v1/ecommerce/orders/{$order->id}")->assertNotFound();
    $this->putJson("/api/v1/ecommerce/orders/{$order->id}", ['notes' => 'x'])->assertNotFound();
    $this->deleteJson("/api/v1/ecommerce/orders/{$order->id}")->assertNotFound();
});

test('a user without permission cannot manage orders', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => false]);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/ecommerce/orders', ['customer_id' => $this->customer->id])->assertForbidden();
});

test('an order with an added item can be cancelled, which restocks the product and rolls back coupon usage', function () {
    $product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id, 'stock_quantity' => 10, 'price' => 20]);
    $coupon = Coupon::factory()->create(['company_id' => $this->company->id, 'type' => CouponType::FIXED, 'value' => 5]);
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/items", ['product_id' => $product->id, 'quantity' => 3])
        ->assertCreated();

    expect($product->fresh()->stock_quantity)->toBe(7);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/apply-coupon", ['code' => $coupon->code])
        ->assertOk()
        ->assertJsonPath('data.discount_total', '5.00');

    expect($coupon->fresh()->usage_count)->toBe(1);

    $response = $this->postJson("/api/v1/ecommerce/orders/{$order->id}/cancel");
    $response->assertOk()->assertJsonPath('data.status', 'Cancelled');

    expect($response->json('data.cancelled_at'))->not->toBeNull();
    expect($product->fresh()->stock_quantity)->toBe(10);
    expect($coupon->fresh()->usage_count)->toBe(0);
});

test('cancelling an already-cancelled order is rejected', function () {
    $order = Order::factory()->create([
        'company_id' => $this->company->id,
        'customer_id' => $this->customer->id,
        'status' => OrderStatus::CANCELLED,
        'cancelled_at' => now(),
    ]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/cancel")->assertUnprocessable();
});

test('a user without the cancel permission cannot cancel an order', function () {
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => false]);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/cancel")->assertForbidden();
});

test('applying a percentage coupon computes the discount from the order subtotal', function () {
    $product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id, 'stock_quantity' => 10, 'price' => 50]);
    $coupon = Coupon::factory()->create(['company_id' => $this->company->id, 'type' => CouponType::PERCENTAGE, 'value' => 10]);
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/items", ['product_id' => $product->id, 'quantity' => 2]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/apply-coupon", ['code' => $coupon->code])
        ->assertOk()
        ->assertJsonPath('data.subtotal', '100.00')
        ->assertJsonPath('data.discount_total', '10.00')
        ->assertJsonPath('data.total', '90.00');
});

test('a percentage coupon discount is capped by max_discount_amount', function () {
    $product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id, 'stock_quantity' => 10, 'price' => 100]);
    $coupon = Coupon::factory()->create([
        'company_id' => $this->company->id,
        'type' => CouponType::PERCENTAGE,
        'value' => 50,
        'max_discount_amount' => 20,
    ]);
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/items", ['product_id' => $product->id, 'quantity' => 1]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/apply-coupon", ['code' => $coupon->code])
        ->assertOk()
        ->assertJsonPath('data.discount_total', '20.00');
});

test('an expired coupon cannot be applied', function () {
    $coupon = Coupon::factory()->create(['company_id' => $this->company->id, 'expires_at' => now()->subDay()]);
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/apply-coupon", ['code' => $coupon->code])->assertUnprocessable();
});

test('a coupon below its minimum order amount cannot be applied', function () {
    $product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id, 'price' => 10]);
    $coupon = Coupon::factory()->create(['company_id' => $this->company->id, 'min_order_amount' => 100]);
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/items", ['product_id' => $product->id, 'quantity' => 1]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/apply-coupon", ['code' => $coupon->code])->assertUnprocessable();
});

test('a coupon that has reached its usage limit cannot be applied', function () {
    $coupon = Coupon::factory()->create(['company_id' => $this->company->id, 'usage_limit' => 1, 'usage_count' => 1]);
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/apply-coupon", ['code' => $coupon->code])->assertUnprocessable();
});

test('an unknown coupon code returns not found', function () {
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/apply-coupon", ['code' => 'DOES-NOT-EXIST'])->assertNotFound();
});

test('removing a coupon zeroes the discount and rolls back usage', function () {
    $coupon = Coupon::factory()->create(['company_id' => $this->company->id, 'type' => CouponType::FIXED, 'value' => 5]);
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/apply-coupon", ['code' => $coupon->code])->assertOk();
    expect($coupon->fresh()->usage_count)->toBe(1);

    $this->postJson("/api/v1/ecommerce/orders/{$order->id}/remove-coupon")
        ->assertOk()
        ->assertJsonPath('data.discount_total', '0.00')
        ->assertJsonPath('data.coupon_id', null);

    expect($coupon->fresh()->usage_count)->toBe(0);
});

test('a payment method can be attached to an order and is nested in the response', function () {
    $paymentMethod = PaymentMethod::factory()->create(['company_id' => $this->company->id]);
    $order = Order::factory()->create(['company_id' => $this->company->id, 'customer_id' => $this->customer->id]);

    $this->putJson("/api/v1/ecommerce/orders/{$order->id}", ['payment_method_id' => $paymentMethod->id])
        ->assertOk();

    $this->getJson("/api/v1/ecommerce/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.payment_method.id', $paymentMethod->id);
});
